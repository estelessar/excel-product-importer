<?php
/**
 * AJAX Handler class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EPI_Ajax_Handler {
    
    public function __construct() {
        // Import
        add_action('wp_ajax_epi_upload_file', array($this, 'upload_file'));
        add_action('wp_ajax_epi_parse_file', array($this, 'parse_file'));
        add_action('wp_ajax_epi_import_products', array($this, 'import_products'));
        add_action('wp_ajax_epi_get_categories', array($this, 'get_categories'));
        add_action('wp_ajax_epi_download_template', array($this, 'download_template'));
        
        // Export
        add_action('wp_ajax_epi_export_products', array($this, 'export_products'));
        
        // Stock Update
        add_action('wp_ajax_epi_update_stock', array($this, 'update_stock'));
        
        // Templates
        add_action('wp_ajax_epi_get_templates', array($this, 'get_templates'));
        add_action('wp_ajax_epi_save_template', array($this, 'save_template'));
        add_action('wp_ajax_epi_delete_template', array($this, 'delete_template'));
        add_action('wp_ajax_epi_load_template', array($this, 'load_template'));
        
        // History
        add_action('wp_ajax_epi_get_history', array($this, 'get_history'));
        add_action('wp_ajax_epi_rollback_import', array($this, 'rollback_import'));
        
        // Scheduler
        add_action('wp_ajax_epi_get_schedules', array($this, 'get_schedules'));
        add_action('wp_ajax_epi_save_schedule', array($this, 'save_schedule'));
        add_action('wp_ajax_epi_delete_schedule', array($this, 'delete_schedule'));
        add_action('wp_ajax_epi_toggle_schedule', array($this, 'toggle_schedule'));
        add_action('wp_ajax_epi_run_schedule', array($this, 'run_schedule'));
    }
    
    /**
     * Verify nonce
     */
    private function verify_nonce() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'epi_nonce')) {
            wp_send_json_error(array('message' => __('Güvenlik doğrulaması başarısız.', 'excel-product-importer')));
        }
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Bu işlem için yetkiniz yok.', 'excel-product-importer')));
        }
    }
    
    /**
     * Upload Excel file
     */
    public function upload_file() {
        $this->verify_nonce();
        
        if (empty($_FILES['file'])) {
            wp_send_json_error(array('message' => __('Dosya yüklenemedi.', 'excel-product-importer')));
        }
        
        $file = $_FILES['file'];
        $allowed_types = array(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv',
            'application/csv'
        );
        
        $allowed_extensions = array('xlsx', 'xls', 'csv');
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
            wp_send_json_error(array('message' => __('Geçersiz dosya formatı. Sadece .xlsx, .xls ve .csv dosyaları kabul edilir.', 'excel-product-importer')));
        }
        
        $upload_dir = wp_upload_dir();
        $epi_dir = $upload_dir['basedir'] . '/epi-imports';
        
        if (!file_exists($epi_dir)) {
            wp_mkdir_p($epi_dir);
        }
        
        $filename = 'import_' . time() . '_' . sanitize_file_name($file['name']);
        $filepath = $epi_dir . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            wp_send_json_error(array('message' => __('Dosya kaydedilemedi.', 'excel-product-importer')));
        }
        
        // Store file path in transient
        set_transient('epi_current_file_' . get_current_user_id(), $filepath, HOUR_IN_SECONDS);
        
        wp_send_json_success(array(
            'message' => __('Dosya başarıyla yüklendi.', 'excel-product-importer'),
            'filename' => $filename,
            'filepath' => $filepath
        ));
    }
    
    /**
     * Parse uploaded file
     */
    public function parse_file() {
        $this->verify_nonce();
        
        $filepath = get_transient('epi_current_file_' . get_current_user_id());
        
        if (!$filepath || !file_exists($filepath)) {
            wp_send_json_error(array('message' => __('Dosya bulunamadı. Lütfen tekrar yükleyin.', 'excel-product-importer')));
        }
        
        $parser = new EPI_Excel_Parser();
        $result = $parser->parse($filepath);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * Import products
     */
    public function import_products() {
        $this->verify_nonce();
        
        $mapping = isset($_POST['mapping']) ? json_decode(stripslashes($_POST['mapping']), true) : array();
        $options = isset($_POST['options']) ? json_decode(stripslashes($_POST['options']), true) : array();
        $batch = isset($_POST['batch']) ? intval($_POST['batch']) : 0;
        
        if (empty($mapping)) {
            wp_send_json_error(array('message' => __('Sütun eşleştirmesi yapılmadı.', 'excel-product-importer')));
        }
        
        $filepath = get_transient('epi_current_file_' . get_current_user_id());
        
        if (!$filepath || !file_exists($filepath)) {
            wp_send_json_error(array('message' => __('Dosya bulunamadı.', 'excel-product-importer')));
        }
        
        $parser = new EPI_Excel_Parser();
        $data = $parser->parse($filepath);
        
        if (is_wp_error($data)) {
            wp_send_json_error(array('message' => $data->get_error_message()));
        }
        
        $creator = new EPI_Product_Creator();
        $batch_size = 10;
        $start = $batch * $batch_size;
        $rows = array_slice($data['rows'], $start, $batch_size);
        
        $results = array(
            'success' => 0,
            'errors' => 0,
            'skipped' => 0,
            'messages' => array(),
            'product_ids' => array()
        );
        
        foreach ($rows as $index => $row) {
            $result = $creator->create_product($row, $mapping, $options);
            
            if (is_wp_error($result)) {
                $results['errors']++;
                $results['messages'][] = array(
                    'type' => 'error',
                    'row' => $start + $index + 2,
                    'message' => $result->get_error_message()
                );
            } elseif ($result === 'skipped') {
                $results['skipped']++;
                $results['messages'][] = array(
                    'type' => 'warning',
                    'row' => $start + $index + 2,
                    'message' => __('Ürün zaten mevcut, atlandı.', 'excel-product-importer')
                );
            } else {
                $results['success']++;
                if (is_array($result) && isset($result['id'])) {
                    $results['product_ids'][] = $result['id'];
                }
                $results['messages'][] = array(
                    'type' => 'success',
                    'row' => $start + $index + 2,
                    'message' => sprintf(__('Ürün oluşturuldu: %s', 'excel-product-importer'), is_array($result) ? $result['name'] : $result)
                );
            }
        }
        
        $total_rows = count($data['rows']);
        $processed = min(($batch + 1) * $batch_size, $total_rows);
        $has_more = $processed < $total_rows;
        
        // Log to history when complete
        if (!$has_more) {
            $history = new EPI_History();
            $all_results = get_transient('epi_import_results_' . get_current_user_id()) ?: array('success' => 0, 'errors' => 0, 'skipped' => 0, 'product_ids' => array());
            $all_results['success'] += $results['success'];
            $all_results['errors'] += $results['errors'];
            $all_results['skipped'] += $results['skipped'];
            $all_results['product_ids'] = array_merge($all_results['product_ids'], $results['product_ids']);
            
            $history->log_import(array(
                'filename' => basename($filepath),
                'file_size' => filesize($filepath),
                'total_rows' => $total_rows,
                'success_count' => $all_results['success'],
                'error_count' => $all_results['errors'],
                'skipped_count' => $all_results['skipped'],
                'product_ids' => $all_results['product_ids'],
                'mapping' => $mapping,
                'options' => $options,
                'status' => 'completed',
                'error_log' => array()
            ));
            
            delete_transient('epi_import_results_' . get_current_user_id());
        } else {
            // Store intermediate results
            $all_results = get_transient('epi_import_results_' . get_current_user_id()) ?: array('success' => 0, 'errors' => 0, 'skipped' => 0, 'product_ids' => array());
            $all_results['success'] += $results['success'];
            $all_results['errors'] += $results['errors'];
            $all_results['skipped'] += $results['skipped'];
            $all_results['product_ids'] = array_merge($all_results['product_ids'], $results['product_ids']);
            set_transient('epi_import_results_' . get_current_user_id(), $all_results, HOUR_IN_SECONDS);
        }
        
        wp_send_json_success(array(
            'results' => $results,
            'processed' => $processed,
            'total' => $total_rows,
            'has_more' => $has_more,
            'next_batch' => $batch + 1,
            'progress' => round(($processed / $total_rows) * 100)
        ));
    }
    
    /**
     * Get WooCommerce categories
     */
    public function get_categories() {
        $this->verify_nonce();
        
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false
        ));
        
        $result = array();
        foreach ($categories as $cat) {
            $result[] = array(
                'id' => $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'parent' => $cat->parent
            );
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * Download sample template
     */
    public function download_template() {
        check_ajax_referer('epi_nonce', 'nonce');
        
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'custom';
        
        // Generate template based on format
        $content = $this->generate_template_content($format);
        
        $filenames = array(
            'custom' => 'ornek-urun-sablonu.csv',
            'woocommerce_tr' => 'woocommerce-sablon-turkce.csv',
            'woocommerce_en' => 'woocommerce-template-english.csv',
            'stock_only' => 'stok-guncelleme-sablonu.csv'
        );
        
        $filename = isset($filenames[$format]) ? $filenames[$format] : 'ornek-urun-sablonu.csv';
        
        wp_send_json_success(array(
            'filename' => $filename,
            'content' => base64_encode($content),
            'type' => 'text/csv;charset=utf-8'
        ));
    }
    
    /**
     * Generate template content based on format
     */
    private function generate_template_content($format) {
        $output = fopen('php://temp', 'r+');
        
        // UTF-8 BOM for Turkish characters
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        switch ($format) {
            case 'woocommerce_tr':
                $this->write_woocommerce_tr_template($output);
                break;
            case 'woocommerce_en':
                $this->write_woocommerce_en_template($output);
                break;
            case 'stock_only':
                $this->write_stock_template($output);
                break;
            default:
                $this->write_custom_template($output);
                break;
        }
        
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        
        return $content;
    }
    
    /**
     * Write custom template (our format)
     */
    private function write_custom_template($output) {
        $headers = array(
            'product_name', 'sku', 'description', 'short_description', 
            'regular_price', 'sale_price', 'stock_quantity', 'category', 'tags',
            'image_url', 'gallery_urls', 'weight', 'length', 'width', 'height',
            'product_type', 'parent_sku', 
            'attribute_1_name', 'attribute_1_values', 
            'attribute_2_name', 'attribute_2_values'
        );
        
        fputcsv($output, $headers, ';');
        
        // Simple product example
        fputcsv($output, array(
            'Deri Cüzdan', 'WALLET-001', 'Hakiki deri erkek cüzdanı. El yapımı.',
            'Premium deri cüzdan', '299.90', '', '30', 'Aksesuar', 'cüzdan,deri,erkek',
            'https://example.com/images/wallet.jpg', '', '0.1', '12', '10', '2',
            'simple', '', '', '', '', ''
        ), ';');
        
        // Variable product example
        fputcsv($output, array(
            'Basic T-Shirt', 'TSHIRT-001', 'Yüksek kaliteli pamuklu basic t-shirt.',
            'Rahat ve şık basic t-shirt', '99.90', '79.90', '100', 'Giyim', 'tshirt,pamuk,basic',
            'https://example.com/images/tshirt.jpg', '', '0.2', '30', '25', '2',
            'variable', '', 'Renk', 'Kırmızı|Mavi|Siyah', 'Beden', 'S|M|L|XL'
        ), ';');
        
        // Variation examples with different prices and stock
        fputcsv($output, array(
            'Basic T-Shirt - Kırmızı S', 'TSHIRT-001-RED-S', '', '',
            '99.90', '79.90', '25', '', '', '', '', '', '', '', '',
            'variation', 'TSHIRT-001', 'Renk', 'Kırmızı', 'Beden', 'S'
        ), ';');
        
        fputcsv($output, array(
            'Basic T-Shirt - Kırmızı M', 'TSHIRT-001-RED-M', '', '',
            '109.90', '89.90', '30', '', '', '', '', '', '', '', '',
            'variation', 'TSHIRT-001', 'Renk', 'Kırmızı', 'Beden', 'M'
        ), ';');
        
        fputcsv($output, array(
            'Basic T-Shirt - Mavi S', 'TSHIRT-001-BLUE-S', '', '',
            '99.90', '79.90', '18', '', '', '', '', '', '', '', '',
            'variation', 'TSHIRT-001', 'Renk', 'Mavi', 'Beden', 'S'
        ), ';');
    }
    
    /**
     * Write WooCommerce Turkish format template
     */
    private function write_woocommerce_tr_template($output) {
        $headers = array(
            'Kimlik', 'Tür', 'Stok kodu (SKU)', 'İsim', 'Yayımlanmış', 'Öne çıkan?',
            'Katalogda görünürlük', 'Kısa açıklama', 'Açıklama',
            'İndirimli fiyatın başladığı tarih', 'İndirimli fiyatın bittiği tarih',
            'Vergi durumu', 'Vergi sınıfı', 'Stokta?', 'Stok', 'Düşük stok miktarı',
            'Yok satmaya izin?', 'Ayrı ayrı mı satılıyor?',
            'Ağırlık (kg)', 'Uzunluk (cm)', 'Genişlik (cm)', 'Yükseklik (cm)',
            'Müşteri değerlendirmelerine izin verilsin mi?', 'Satın alma notu',
            'İndirimli satış fiyatı', 'Normal fiyat', 'Kategoriler', 'Etiketler',
            'Gönderim sınıfı', 'Görseller', 'Ebeveyn',
            'Nitelik 1 ismi', 'Nitelik 1 değer(ler)i', 'Nitelik 1 görünür', 'Nitelik 1 genel',
            'Nitelik 2 ismi', 'Nitelik 2 değer(ler)i', 'Nitelik 2 görünür', 'Nitelik 2 genel'
        );
        
        fputcsv($output, $headers, ',');
        
        // Simple product
        fputcsv($output, array(
            '', 'simple', 'WALLET-001', 'Deri Cüzdan', '1', '0',
            'visible', 'Premium deri cüzdan', 'Hakiki deri erkek cüzdanı. El yapımı.',
            '', '', 'taxable', '', '1', '30', '',
            '0', '0', '0.1', '12', '10', '2', '1', '',
            '', '299.90', 'Aksesuar', 'cüzdan, deri',
            '', 'https://example.com/wallet.jpg', '',
            '', '', '', '', '', '', '', ''
        ), ',');
        
        // Variable product
        fputcsv($output, array(
            '', 'variable', 'TSHIRT-001', 'Basic T-Shirt', '1', '0',
            'visible', 'Rahat ve şık basic t-shirt', 'Yüksek kaliteli pamuklu basic t-shirt.',
            '', '', 'taxable', '', '1', '', '',
            '0', '0', '0.2', '30', '25', '2', '1', '',
            '', '', 'Giyim', 'tshirt, pamuk',
            '', 'https://example.com/tshirt.jpg', '',
            'Renk', 'Kırmızı, Mavi, Siyah', '1', '0',
            'Beden', 'S, M, L, XL', '1', '0'
        ), ',');
        
        // Variation
        fputcsv($output, array(
            '', 'variation', 'TSHIRT-001-RED-S', 'Basic T-Shirt - Kırmızı S', '1', '0',
            'visible', '', '', '', '', 'taxable', '', '1', '25', '',
            '0', '0', '', '', '', '', '1', '',
            '79.90', '99.90', '', '', '', '', 'TSHIRT-001',
            'Renk', 'Kırmızı', '1', '0', 'Beden', 'S', '1', '0'
        ), ',');
    }
    
    /**
     * Write WooCommerce English format template
     */
    private function write_woocommerce_en_template($output) {
        $headers = array(
            'ID', 'Type', 'SKU', 'Name', 'Published', 'Is featured?',
            'Visibility in catalog', 'Short description', 'Description',
            'Date sale price starts', 'Date sale price ends',
            'Tax status', 'Tax class', 'In stock?', 'Stock', 'Low stock amount',
            'Backorders allowed?', 'Sold individually?',
            'Weight (kg)', 'Length (cm)', 'Width (cm)', 'Height (cm)',
            'Allow customer reviews?', 'Purchase note',
            'Sale price', 'Regular price', 'Categories', 'Tags',
            'Shipping class', 'Images', 'Parent',
            'Attribute 1 name', 'Attribute 1 value(s)', 'Attribute 1 visible', 'Attribute 1 global',
            'Attribute 2 name', 'Attribute 2 value(s)', 'Attribute 2 visible', 'Attribute 2 global'
        );
        
        fputcsv($output, $headers, ',');
        
        // Simple product
        fputcsv($output, array(
            '', 'simple', 'WALLET-001', 'Leather Wallet', '1', '0',
            'visible', 'Premium leather wallet', 'Genuine leather mens wallet. Handmade.',
            '', '', 'taxable', '', '1', '30', '',
            '0', '0', '0.1', '12', '10', '2', '1', '',
            '', '299.90', 'Accessories', 'wallet, leather',
            '', 'https://example.com/wallet.jpg', '',
            '', '', '', '', '', '', '', ''
        ), ',');
        
        // Variable product
        fputcsv($output, array(
            '', 'variable', 'TSHIRT-001', 'Basic T-Shirt', '1', '0',
            'visible', 'Comfortable basic t-shirt', 'High quality cotton basic t-shirt.',
            '', '', 'taxable', '', '1', '', '',
            '0', '0', '0.2', '30', '25', '2', '1', '',
            '', '', 'Clothing', 'tshirt, cotton',
            '', 'https://example.com/tshirt.jpg', '',
            'Color', 'Red, Blue, Black', '1', '0',
            'Size', 'S, M, L, XL', '1', '0'
        ), ',');
        
        // Variation
        fputcsv($output, array(
            '', 'variation', 'TSHIRT-001-RED-S', 'Basic T-Shirt - Red S', '1', '0',
            'visible', '', '', '', '', 'taxable', '', '1', '25', '',
            '0', '0', '', '', '', '', '1', '',
            '79.90', '99.90', '', '', '', '', 'TSHIRT-001',
            'Color', 'Red', '1', '0', 'Size', 'S', '1', '0'
        ), ',');
    }
    
    /**
     * Write stock update only template
     */
    private function write_stock_template($output) {
        $headers = array('sku', 'stock_quantity', 'regular_price', 'sale_price');
        
        fputcsv($output, $headers, ';');
        
        fputcsv($output, array('URUN-001', '50', '199.90', '149.90'), ';');
        fputcsv($output, array('URUN-002', '25', '299.90', ''), ';');
        fputcsv($output, array('TSHIRT-001-RED-S', '30', '99.90', '79.90'), ';');
        fputcsv($output, array('TSHIRT-001-RED-M', '45', '109.90', '89.90'), ';');
    }
    
    /**
     * Export products
     */
    public function export_products() {
        $this->verify_nonce();
        
        $filters = array(
            'category' => isset($_POST['category']) ? intval($_POST['category']) : '',
            'stock_status' => isset($_POST['stock_status']) ? sanitize_text_field($_POST['stock_status']) : '',
            'price_min' => isset($_POST['price_min']) ? floatval($_POST['price_min']) : '',
            'price_max' => isset($_POST['price_max']) ? floatval($_POST['price_max']) : '',
            'product_type' => isset($_POST['product_type']) ? sanitize_text_field($_POST['product_type']) : ''
        );
        
        $exporter = new EPI_Exporter();
        $data = $exporter->export($filters);
        $csv = $exporter->generate_csv($data);
        
        wp_send_json_success(array(
            'filename' => 'urunler-' . date('Y-m-d-His') . '.csv',
            'content' => base64_encode($csv),
            'type' => 'text/csv',
            'count' => $data['count']
        ));
    }
    
    /**
     * Update stock only
     */
    public function update_stock() {
        $this->verify_nonce();
        
        $mapping = isset($_POST['mapping']) ? json_decode(stripslashes($_POST['mapping']), true) : array();
        $batch = isset($_POST['batch']) ? intval($_POST['batch']) : 0;
        
        $filepath = get_transient('epi_current_file_' . get_current_user_id());
        
        if (!$filepath || !file_exists($filepath)) {
            wp_send_json_error(array('message' => __('Dosya bulunamadı.', 'excel-product-importer')));
        }
        
        $parser = new EPI_Excel_Parser();
        $data = $parser->parse($filepath);
        
        if (is_wp_error($data)) {
            wp_send_json_error(array('message' => $data->get_error_message()));
        }
        
        $updater = new EPI_Stock_Updater();
        $batch_size = 20;
        $start = $batch * $batch_size;
        $rows = array_slice($data['rows'], $start, $batch_size);
        
        $results = $updater->bulk_update($rows, $mapping);
        
        $total_rows = count($data['rows']);
        $processed = min(($batch + 1) * $batch_size, $total_rows);
        $has_more = $processed < $total_rows;
        
        wp_send_json_success(array(
            'results' => $results,
            'processed' => $processed,
            'total' => $total_rows,
            'has_more' => $has_more,
            'next_batch' => $batch + 1,
            'progress' => round(($processed / $total_rows) * 100)
        ));
    }
    
    /**
     * Get templates
     */
    public function get_templates() {
        $this->verify_nonce();
        
        $templates = new EPI_Templates();
        wp_send_json_success($templates->get_templates());
    }
    
    /**
     * Save template
     */
    public function save_template() {
        $this->verify_nonce();
        
        $data = array(
            'id' => isset($_POST['template_id']) ? sanitize_key($_POST['template_id']) : '',
            'name' => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'description' => isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '',
            'mapping' => isset($_POST['mapping']) ? json_decode(stripslashes($_POST['mapping']), true) : array(),
            'options' => isset($_POST['options']) ? json_decode(stripslashes($_POST['options']), true) : array()
        );
        
        if (empty($data['name'])) {
            wp_send_json_error(array('message' => __('Şablon adı zorunludur.', 'excel-product-importer')));
        }
        
        $templates = new EPI_Templates();
        $id = $templates->save_template($data);
        
        wp_send_json_success(array(
            'id' => $id,
            'message' => __('Şablon kaydedildi.', 'excel-product-importer')
        ));
    }
    
    /**
     * Delete template
     */
    public function delete_template() {
        $this->verify_nonce();
        
        $id = isset($_POST['template_id']) ? sanitize_key($_POST['template_id']) : '';
        
        if (empty($id)) {
            wp_send_json_error(array('message' => __('Şablon ID gerekli.', 'excel-product-importer')));
        }
        
        $templates = new EPI_Templates();
        $templates->delete_template($id);
        
        wp_send_json_success(array('message' => __('Şablon silindi.', 'excel-product-importer')));
    }
    
    /**
     * Load template
     */
    public function load_template() {
        $this->verify_nonce();
        
        $id = isset($_POST['template_id']) ? sanitize_key($_POST['template_id']) : '';
        
        $templates = new EPI_Templates();
        $template = $templates->get_template($id);
        
        if (!$template) {
            wp_send_json_error(array('message' => __('Şablon bulunamadı.', 'excel-product-importer')));
        }
        
        wp_send_json_success($template);
    }
    
    /**
     * Get import history
     */
    public function get_history() {
        $this->verify_nonce();
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = 10;
        $offset = ($page - 1) * $per_page;
        
        $history = new EPI_History();
        $records = $history->get_history($per_page, $offset);
        $total = $history->get_total_count();
        $stats = $history->get_stats();
        
        wp_send_json_success(array(
            'records' => $records,
            'total' => $total,
            'pages' => ceil($total / $per_page),
            'stats' => $stats
        ));
    }
    
    /**
     * Rollback import
     */
    public function rollback_import() {
        $this->verify_nonce();
        
        $id = isset($_POST['import_id']) ? intval($_POST['import_id']) : 0;
        
        if (!$id) {
            wp_send_json_error(array('message' => __('İçe aktarma ID gerekli.', 'excel-product-importer')));
        }
        
        $history = new EPI_History();
        $result = $history->rollback($id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array(
            'deleted' => $result,
            'message' => sprintf(__('%d ürün silindi.', 'excel-product-importer'), $result)
        ));
    }
    
    /**
     * Get schedules
     */
    public function get_schedules() {
        $this->verify_nonce();
        
        $scheduler = new EPI_Scheduler();
        wp_send_json_success($scheduler->get_schedules());
    }
    
    /**
     * Save schedule
     */
    public function save_schedule() {
        $this->verify_nonce();
        
        $data = array(
            'id' => isset($_POST['schedule_id']) ? sanitize_key($_POST['schedule_id']) : '',
            'name' => isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '',
            'source_type' => isset($_POST['source_type']) ? sanitize_text_field($_POST['source_type']) : 'url',
            'source_url' => isset($_POST['source_url']) ? esc_url_raw($_POST['source_url']) : '',
            'ftp_host' => isset($_POST['ftp_host']) ? sanitize_text_field($_POST['ftp_host']) : '',
            'ftp_user' => isset($_POST['ftp_user']) ? sanitize_text_field($_POST['ftp_user']) : '',
            'ftp_pass' => isset($_POST['ftp_pass']) ? $_POST['ftp_pass'] : '',
            'ftp_path' => isset($_POST['ftp_path']) ? sanitize_text_field($_POST['ftp_path']) : '',
            'frequency' => isset($_POST['frequency']) ? sanitize_text_field($_POST['frequency']) : 'daily',
            'time' => isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '03:00',
            'day_of_week' => isset($_POST['day_of_week']) ? intval($_POST['day_of_week']) : 1,
            'template_id' => isset($_POST['template_id']) ? sanitize_key($_POST['template_id']) : '',
            'mode' => isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'full',
            'enabled' => isset($_POST['enabled']) && $_POST['enabled'] === 'true'
        );
        
        if (empty($data['name'])) {
            wp_send_json_error(array('message' => __('Zamanlama adı zorunludur.', 'excel-product-importer')));
        }
        
        $scheduler = new EPI_Scheduler();
        $id = $scheduler->save_schedule($data);
        
        wp_send_json_success(array(
            'id' => $id,
            'message' => __('Zamanlama kaydedildi.', 'excel-product-importer')
        ));
    }
    
    /**
     * Delete schedule
     */
    public function delete_schedule() {
        $this->verify_nonce();
        
        $id = isset($_POST['schedule_id']) ? sanitize_key($_POST['schedule_id']) : '';
        
        $scheduler = new EPI_Scheduler();
        $scheduler->delete_schedule($id);
        
        wp_send_json_success(array('message' => __('Zamanlama silindi.', 'excel-product-importer')));
    }
    
    /**
     * Toggle schedule
     */
    public function toggle_schedule() {
        $this->verify_nonce();
        
        $id = isset($_POST['schedule_id']) ? sanitize_key($_POST['schedule_id']) : '';
        
        $scheduler = new EPI_Scheduler();
        $enabled = $scheduler->toggle_schedule($id);
        
        wp_send_json_success(array(
            'enabled' => $enabled,
            'message' => $enabled ? __('Zamanlama aktif.', 'excel-product-importer') : __('Zamanlama pasif.', 'excel-product-importer')
        ));
    }
    
    /**
     * Run schedule now
     */
    public function run_schedule() {
        $this->verify_nonce();
        
        $id = isset($_POST['schedule_id']) ? sanitize_key($_POST['schedule_id']) : '';
        
        $scheduler = new EPI_Scheduler();
        $scheduler->run_now($id);
        
        wp_send_json_success(array('message' => __('Zamanlama çalıştırıldı.', 'excel-product-importer')));
    }
}
