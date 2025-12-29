<?php
/**
 * Product Creator class
 * Supports WooCommerce native export format (Turkish & English)
 */

if (!defined('ABSPATH')) {
    exit;
}

class EPI_Product_Creator {
    
    private $variation_handler;
    
    /**
     * WooCommerce field mappings (Turkish => English => Internal)
     */
    private $wc_field_map = array(
        // Turkish WooCommerce Export Fields
        'Kimlik' => 'id',
        'Tür' => 'product_type',
        'Stok kodu (SKU)' => 'sku',
        'GTIN, UPC, EAN, or ISBN' => 'gtin',
        'İsim' => 'name',
        'Yayımlanmış' => 'published',
        'Öne çıkan?' => 'featured',
        'Katalogda görünürlük' => 'catalog_visibility',
        'Kısa açıklama' => 'short_description',
        'Açıklama' => 'description',
        'İndirimli fiyatın başladığı tarih' => 'sale_price_dates_from',
        'İndirimli fiyatın bittiği tarih' => 'sale_price_dates_to',
        'Vergi durumu' => 'tax_status',
        'Vergi sınıfı' => 'tax_class',
        'Stokta?' => 'in_stock',
        'Stok' => 'stock_quantity',
        'Düşük stok miktarı' => 'low_stock_amount',
        'Yok satmaya izin?' => 'backorders_allowed',
        'Ayrı ayrı mı satılıyor?' => 'sold_individually',
        'Ağırlık (kg)' => 'weight',
        'Uzunluk (cm)' => 'length',
        'Genişlik (cm)' => 'width',
        'Yükseklik (cm)' => 'height',
        'Müşteri değerlendirmelerine izin verilsin mi?' => 'reviews_allowed',
        'Satın alma notu' => 'purchase_note',
        'İndirimli satış fiyatı' => 'sale_price',
        'Normal fiyat' => 'regular_price',
        'Kategoriler' => 'category',
        'Etiketler' => 'tags',
        'Gönderim sınıfı' => 'shipping_class',
        'Görseller' => 'images',
        'İndirme sınırı' => 'download_limit',
        'İndirme sona erme günü' => 'download_expiry',
        'Ebeveyn' => 'parent_sku',
        'Gruplanmış ürünler' => 'grouped_products',
        'Yukarı satışlar' => 'upsell_ids',
        'Çapraz satışlar' => 'cross_sell_ids',
        'Harici URL' => 'external_url',
        'Düğme metni' => 'button_text',
        'Konum' => 'menu_order',
        'Markalar' => 'brands',
        
        // English WooCommerce Export Fields
        'ID' => 'id',
        'Type' => 'product_type',
        'SKU' => 'sku',
        'Name' => 'name',
        'Published' => 'published',
        'Is featured?' => 'featured',
        'Visibility in catalog' => 'catalog_visibility',
        'Short description' => 'short_description',
        'Description' => 'description',
        'Date sale price starts' => 'sale_price_dates_from',
        'Date sale price ends' => 'sale_price_dates_to',
        'Tax status' => 'tax_status',
        'Tax class' => 'tax_class',
        'In stock?' => 'in_stock',
        'Stock' => 'stock_quantity',
        'Low stock amount' => 'low_stock_amount',
        'Backorders allowed?' => 'backorders_allowed',
        'Sold individually?' => 'sold_individually',
        'Weight (kg)' => 'weight',
        'Length (cm)' => 'length',
        'Width (cm)' => 'width',
        'Height (cm)' => 'height',
        'Allow customer reviews?' => 'reviews_allowed',
        'Purchase note' => 'purchase_note',
        'Sale price' => 'sale_price',
        'Regular price' => 'regular_price',
        'Categories' => 'category',
        'Tags' => 'tags',
        'Shipping class' => 'shipping_class',
        'Images' => 'images',
        'Download limit' => 'download_limit',
        'Download expiry days' => 'download_expiry',
        'Parent' => 'parent_sku',
        'Grouped products' => 'grouped_products',
        'Upsells' => 'upsell_ids',
        'Cross-sells' => 'cross_sell_ids',
        'External URL' => 'external_url',
        'Button text' => 'button_text',
        'Position' => 'menu_order',
        'Brands' => 'brands',
        
        // Custom template fields
        'product_name' => 'name',
        'sku' => 'sku',
        'description' => 'description',
        'short_description' => 'short_description',
        'regular_price' => 'regular_price',
        'sale_price' => 'sale_price',
        'stock_quantity' => 'stock_quantity',
        'category' => 'category',
        'tags' => 'tags',
        'image_url' => 'image_url',
        'gallery_urls' => 'gallery_urls',
        'weight' => 'weight',
        'length' => 'length',
        'width' => 'width',
        'height' => 'height',
        'product_type' => 'product_type',
        'parent_sku' => 'parent_sku',
    );
    
    public function __construct() {
        $this->variation_handler = new EPI_Variation_Handler();
    }
    
    /**
     * Create product from row data
     */
    public function create_product($row, $mapping, $options = array()) {
        $product_data = $this->map_data($row, $mapping);
        
        if (empty($product_data['name'])) {
            return new WP_Error('missing_name', __('Ürün adı zorunludur.', 'excel-product-importer'));
        }
        
        // Check if updating by ID
        $existing_id = null;
        if (!empty($product_data['id'])) {
            $existing_id = intval($product_data['id']);
            $existing_product = wc_get_product($existing_id);
            if (!$existing_product) {
                $existing_id = null;
            }
        }
        
        // Check if product exists by SKU
        if (!$existing_id && !empty($product_data['sku'])) {
            $existing_id = $this->get_product_by_sku($product_data['sku']);
        }
        
        if ($existing_id) {
            if (!empty($options['skip_existing'])) {
                return 'skipped';
            }
            
            if (!empty($options['update_existing'])) {
                return $this->update_product($existing_id, $product_data, $options);
            }
            
            return 'skipped';
        }
        
        // Determine product type
        $product_type = isset($product_data['product_type']) ? strtolower($product_data['product_type']) : 'simple';
        
        if ($product_type === 'variation') {
            return $this->create_variation($product_data, $options);
        }
        
        if ($product_type === 'variable' || (!empty($product_data['attributes']) && $this->has_variation_attributes($product_data['attributes']))) {
            return $this->create_variable_product($product_data, $options);
        }
        
        if ($product_type === 'grouped') {
            return $this->create_grouped_product($product_data, $options);
        }
        
        if ($product_type === 'external') {
            return $this->create_external_product($product_data, $options);
        }
        
        return $this->create_simple_product($product_data, $options);
    }
    
    /**
     * Map row data to product fields - supports WooCommerce export format
     */
    private function map_data($row, $mapping) {
        $data = array();
        
        // First, try direct column mapping from WooCommerce export
        foreach ($row as $col_name => $value) {
            $value = trim($value);
            if ($value === '') continue;
            
            // Check if column name matches WooCommerce field
            if (isset($this->wc_field_map[$col_name])) {
                $internal_field = $this->wc_field_map[$col_name];
                $data[$internal_field] = $value;
            }
            
            // Handle attribute columns (Turkish)
            if (preg_match('/^Nitelik (\d+) ismi$/', $col_name, $matches)) {
                $attr_num = $matches[1];
                $data['attr_' . $attr_num . '_name'] = $value;
            }
            if (preg_match('/^Nitelik (\d+) değer\(ler\)i$/', $col_name, $matches)) {
                $attr_num = $matches[1];
                $data['attr_' . $attr_num . '_values'] = $value;
            }
            if (preg_match('/^Nitelik (\d+) görünür$/', $col_name, $matches)) {
                $attr_num = $matches[1];
                $data['attr_' . $attr_num . '_visible'] = $value;
            }
            if (preg_match('/^Nitelik (\d+) genel$/', $col_name, $matches)) {
                $attr_num = $matches[1];
                $data['attr_' . $attr_num . '_global'] = $value;
            }
            
            // Handle attribute columns (English)
            if (preg_match('/^Attribute (\d+) name$/', $col_name, $matches)) {
                $attr_num = $matches[1];
                $data['attr_' . $attr_num . '_name'] = $value;
            }
            if (preg_match('/^Attribute (\d+) value\(s\)$/', $col_name, $matches)) {
                $attr_num = $matches[1];
                $data['attr_' . $attr_num . '_values'] = $value;
            }
            if (preg_match('/^Attribute (\d+) visible$/', $col_name, $matches)) {
                $attr_num = $matches[1];
                $data['attr_' . $attr_num . '_visible'] = $value;
            }
            if (preg_match('/^Attribute (\d+) global$/', $col_name, $matches)) {
                $attr_num = $matches[1];
                $data['attr_' . $attr_num . '_global'] = $value;
            }
            
            // Handle Meta fields
            if (strpos($col_name, 'Meta:') === 0) {
                $meta_key = trim(str_replace('Meta:', '', $col_name));
                if (!isset($data['meta'])) $data['meta'] = array();
                $data['meta'][$meta_key] = $value;
            }
        }
        
        // Then apply custom mapping if provided
        foreach ($mapping as $excel_col => $woo_field) {
            if (isset($row[$excel_col]) && !empty(trim($row[$excel_col]))) {
                if (isset($this->wc_field_map[$woo_field])) {
                    $data[$this->wc_field_map[$woo_field]] = trim($row[$excel_col]);
                } else {
                    $data[$woo_field] = trim($row[$excel_col]);
                }
            }
        }
        
        // Build attributes array
        $attributes = array();
        for ($i = 1; $i <= 10; $i++) {
            $attr_name = isset($data['attr_' . $i . '_name']) ? $data['attr_' . $i . '_name'] : null;
            $attr_values = isset($data['attr_' . $i . '_values']) ? $data['attr_' . $i . '_values'] : null;
            $attr_visible = isset($data['attr_' . $i . '_visible']) ? $data['attr_' . $i . '_visible'] : '1';
            $attr_global = isset($data['attr_' . $i . '_global']) ? $data['attr_' . $i . '_global'] : '0';
            
            // Also check custom template format
            if (!$attr_name) {
                foreach ($mapping as $excel_col => $woo_field) {
                    if ($woo_field === "attribute_{$i}_name" && isset($row[$excel_col])) {
                        $attr_name = trim($row[$excel_col]);
                    }
                    if ($woo_field === "attribute_{$i}_values" && isset($row[$excel_col])) {
                        $attr_values = trim($row[$excel_col]);
                    }
                }
            }
            
            if ($attr_name && $attr_values) {
                // WooCommerce uses comma for multiple values, our template uses pipe
                $separator = strpos($attr_values, '|') !== false ? '|' : ',';
                $values = array_map('trim', explode($separator, $attr_values));
                
                $attributes[$attr_name] = array(
                    'values' => $values,
                    'visible' => $attr_visible == '1',
                    'global' => $attr_global == '1',
                );
            }
        }
        
        if (!empty($attributes)) {
            $data['attributes'] = $attributes;
        }
        
        // Handle images - WooCommerce format has all images in one field
        if (!empty($data['images']) && empty($data['image_url'])) {
            $images = array_map('trim', explode(',', $data['images']));
            if (!empty($images[0])) {
                $data['image_url'] = $images[0];
            }
            if (count($images) > 1) {
                array_shift($images);
                $data['gallery_urls'] = implode(',', $images);
            }
        }
        
        return $data;
    }
    
    /**
     * Check if attributes have variation flag
     */
    private function has_variation_attributes($attributes) {
        foreach ($attributes as $attr) {
            if (is_array($attr) && count($attr['values']) > 1) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Create simple product
     */
    private function create_simple_product($data, $options) {
        $product = new WC_Product_Simple();
        $this->set_common_product_data($product, $data);
        
        if (!empty($data['regular_price'])) {
            $product->set_regular_price($this->format_price($data['regular_price']));
        }
        
        if (!empty($data['sale_price'])) {
            $product->set_sale_price($this->format_price($data['sale_price']));
        }
        
        // Sale price dates
        if (!empty($data['sale_price_dates_from'])) {
            $product->set_date_on_sale_from($data['sale_price_dates_from']);
        }
        if (!empty($data['sale_price_dates_to'])) {
            $product->set_date_on_sale_to($data['sale_price_dates_to']);
        }
        
        $product_id = $product->save();
        $this->set_product_extras($product_id, $data);
        
        return array('id' => $product_id, 'name' => $data['name']);
    }
    
    /**
     * Create variable product
     */
    private function create_variable_product($data, $options) {
        $product = new WC_Product_Variable();
        $this->set_common_product_data($product, $data);
        
        // Set attributes for variations
        if (!empty($data['attributes'])) {
            $attributes = array();
            $position = 0;
            
            foreach ($data['attributes'] as $name => $attr_data) {
                $attribute = new WC_Product_Attribute();
                
                // Check if global attribute
                $values = is_array($attr_data) ? $attr_data['values'] : explode('|', $attr_data);
                $is_global = is_array($attr_data) && !empty($attr_data['global']);
                $is_visible = is_array($attr_data) ? $attr_data['visible'] : true;
                
                if ($is_global) {
                    // Use taxonomy attribute
                    $taxonomy = 'pa_' . sanitize_title($name);
                    $attribute->set_id(wc_attribute_taxonomy_id_by_name($taxonomy));
                    $attribute->set_name($taxonomy);
                    
                    // Create terms if they don't exist
                    foreach ($values as $term_name) {
                        if (!term_exists($term_name, $taxonomy)) {
                            wp_insert_term($term_name, $taxonomy);
                        }
                    }
                    $attribute->set_options($values);
                } else {
                    $attribute->set_name($name);
                    $attribute->set_options($values);
                }
                
                $attribute->set_position($position);
                $attribute->set_visible($is_visible);
                $attribute->set_variation(true);
                
                $attributes[] = $attribute;
                $position++;
            }
            
            $product->set_attributes($attributes);
        }
        
        $product_id = $product->save();
        $this->set_product_extras($product_id, $data);
        
        return array('id' => $product_id, 'name' => $data['name'] . ' (Variable)');
    }
    
    /**
     * Create grouped product
     */
    private function create_grouped_product($data, $options) {
        $product = new WC_Product_Grouped();
        $this->set_common_product_data($product, $data);
        
        // Set grouped products
        if (!empty($data['grouped_products'])) {
            $child_ids = array();
            $skus = array_map('trim', explode(',', $data['grouped_products']));
            foreach ($skus as $sku) {
                $child_id = wc_get_product_id_by_sku($sku);
                if ($child_id) {
                    $child_ids[] = $child_id;
                }
            }
            $product->set_children($child_ids);
        }
        
        $product_id = $product->save();
        $this->set_product_extras($product_id, $data);
        
        return array('id' => $product_id, 'name' => $data['name'] . ' (Grouped)');
    }
    
    /**
     * Create external/affiliate product
     */
    private function create_external_product($data, $options) {
        $product = new WC_Product_External();
        $this->set_common_product_data($product, $data);
        
        if (!empty($data['regular_price'])) {
            $product->set_regular_price($this->format_price($data['regular_price']));
        }
        
        if (!empty($data['sale_price'])) {
            $product->set_sale_price($this->format_price($data['sale_price']));
        }
        
        if (!empty($data['external_url'])) {
            $product->set_product_url($data['external_url']);
        }
        
        if (!empty($data['button_text'])) {
            $product->set_button_text($data['button_text']);
        }
        
        $product_id = $product->save();
        $this->set_product_extras($product_id, $data);
        
        return array('id' => $product_id, 'name' => $data['name'] . ' (External)');
    }
    
    /**
     * Create product variation
     */
    private function create_variation($data, $options) {
        if (empty($data['parent_sku'])) {
            return new WP_Error('missing_parent', __('Varyasyon için parent SKU gerekli.', 'excel-product-importer'));
        }
        
        $parent_id = $this->get_product_by_sku($data['parent_sku']);
        
        if (!$parent_id) {
            return new WP_Error('parent_not_found', sprintf(__('Parent ürün bulunamadı: %s', 'excel-product-importer'), $data['parent_sku']));
        }
        
        return $this->variation_handler->create_variation($parent_id, $data);
    }
    
    /**
     * Set common product data
     */
    private function set_common_product_data($product, $data) {
        $product->set_name($data['name']);
        
        if (!empty($data['sku'])) {
            $product->set_sku($data['sku']);
        }
        
        if (!empty($data['gtin'])) {
            if (method_exists($product, 'set_global_unique_id')) {
                $product->set_global_unique_id($data['gtin']);
            }
        }
        
        if (!empty($data['description'])) {
            $product->set_description($data['description']);
        }
        
        if (!empty($data['short_description'])) {
            $product->set_short_description($data['short_description']);
        }
        
        // Stock management
        if (isset($data['stock_quantity']) && $data['stock_quantity'] !== '') {
            $product->set_manage_stock(true);
            $product->set_stock_quantity(intval($data['stock_quantity']));
        }
        
        if (isset($data['in_stock'])) {
            $stock_status = ($data['in_stock'] == '1' || $data['in_stock'] === 'instock') ? 'instock' : 'outofstock';
            $product->set_stock_status($stock_status);
        }
        
        if (!empty($data['low_stock_amount'])) {
            $product->set_low_stock_amount(intval($data['low_stock_amount']));
        }
        
        if (isset($data['backorders_allowed'])) {
            $backorders = $data['backorders_allowed'] == '1' ? 'yes' : 'no';
            $product->set_backorders($backorders);
        }
        
        if (isset($data['sold_individually'])) {
            $product->set_sold_individually($data['sold_individually'] == '1');
        }
        
        // Dimensions
        if (!empty($data['weight'])) {
            $product->set_weight($data['weight']);
        }
        if (!empty($data['length'])) {
            $product->set_length($data['length']);
        }
        if (!empty($data['width'])) {
            $product->set_width($data['width']);
        }
        if (!empty($data['height'])) {
            $product->set_height($data['height']);
        }
        
        // Tax
        if (!empty($data['tax_status'])) {
            $product->set_tax_status($data['tax_status']);
        }
        if (!empty($data['tax_class'])) {
            $product->set_tax_class($data['tax_class']);
        }
        
        // Visibility
        if (!empty($data['catalog_visibility'])) {
            $product->set_catalog_visibility($data['catalog_visibility']);
        }
        
        // Featured
        if (isset($data['featured'])) {
            $product->set_featured($data['featured'] == '1');
        }
        
        // Reviews
        if (isset($data['reviews_allowed'])) {
            $product->set_reviews_allowed($data['reviews_allowed'] == '1');
        }
        
        // Purchase note
        if (!empty($data['purchase_note'])) {
            $product->set_purchase_note($data['purchase_note']);
        }
        
        // Menu order
        if (isset($data['menu_order'])) {
            $product->set_menu_order(intval($data['menu_order']));
        }
        
        // Status
        $status = 'publish';
        if (isset($data['published'])) {
            $status = ($data['published'] == '1' || $data['published'] === 'publish') ? 'publish' : 'draft';
        }
        $product->set_status($status);
        
        // Simple attributes (non-variation)
        if (!empty($data['attributes']) && !$product->is_type('variable')) {
            $attributes = array();
            $position = 0;
            
            foreach ($data['attributes'] as $name => $attr_data) {
                $attribute = new WC_Product_Attribute();
                $values = is_array($attr_data) ? $attr_data['values'] : explode('|', $attr_data);
                $is_visible = is_array($attr_data) ? $attr_data['visible'] : true;
                
                $attribute->set_name($name);
                $attribute->set_options($values);
                $attribute->set_position($position);
                $attribute->set_visible($is_visible);
                $attribute->set_variation(false);
                
                $attributes[] = $attribute;
                $position++;
            }
            
            $product->set_attributes($attributes);
        }
    }
    
    /**
     * Set product extras (categories, tags, images, etc.)
     */
    private function set_product_extras($product_id, $data) {
        // Categories - support WooCommerce hierarchical format
        if (!empty($data['category'])) {
            $this->set_product_categories($product_id, $data['category']);
        }
        
        // Tags
        if (!empty($data['tags'])) {
            $this->set_product_tags($product_id, $data['tags']);
        }
        
        // Shipping class
        if (!empty($data['shipping_class'])) {
            $this->set_shipping_class($product_id, $data['shipping_class']);
        }
        
        // Brands
        if (!empty($data['brands'])) {
            $this->set_product_brands($product_id, $data['brands']);
        }
        
        // Images
        if (!empty($data['image_url'])) {
            $this->set_product_image($product_id, $data['image_url']);
        }
        
        // Gallery
        if (!empty($data['gallery_urls'])) {
            $this->set_product_gallery($product_id, $data['gallery_urls']);
        }
        
        // Upsells
        if (!empty($data['upsell_ids'])) {
            $this->set_linked_products($product_id, $data['upsell_ids'], '_upsell_ids');
        }
        
        // Cross-sells
        if (!empty($data['cross_sell_ids'])) {
            $this->set_linked_products($product_id, $data['cross_sell_ids'], '_crosssell_ids');
        }
        
        // Custom meta
        if (!empty($data['meta'])) {
            foreach ($data['meta'] as $key => $value) {
                update_post_meta($product_id, $key, $value);
            }
        }
    }
    
    /**
     * Update existing product
     */
    private function update_product($product_id, $data, $options) {
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return new WP_Error('product_not_found', __('Ürün bulunamadı.', 'excel-product-importer'));
        }
        
        if (!empty($data['name'])) {
            $product->set_name($data['name']);
        }
        
        if (!empty($data['description'])) {
            $product->set_description($data['description']);
        }
        
        if (!empty($data['short_description'])) {
            $product->set_short_description($data['short_description']);
        }
        
        if (!empty($data['regular_price'])) {
            $product->set_regular_price($this->format_price($data['regular_price']));
        }
        
        if (!empty($data['sale_price'])) {
            $product->set_sale_price($this->format_price($data['sale_price']));
        } elseif (isset($data['sale_price']) && $data['sale_price'] === '') {
            $product->set_sale_price('');
        }
        
        if (isset($data['stock_quantity']) && $data['stock_quantity'] !== '') {
            $product->set_manage_stock(true);
            $product->set_stock_quantity(intval($data['stock_quantity']));
        }
        
        // Dimensions
        if (!empty($data['weight'])) {
            $product->set_weight($data['weight']);
        }
        if (!empty($data['length'])) {
            $product->set_length($data['length']);
        }
        if (!empty($data['width'])) {
            $product->set_width($data['width']);
        }
        if (!empty($data['height'])) {
            $product->set_height($data['height']);
        }
        
        // Tax
        if (!empty($data['tax_status'])) {
            $product->set_tax_status($data['tax_status']);
        }
        if (!empty($data['tax_class'])) {
            $product->set_tax_class($data['tax_class']);
        }
        
        // Status
        if (isset($data['published'])) {
            $status = ($data['published'] == '1' || $data['published'] === 'publish') ? 'publish' : 'draft';
            $product->set_status($status);
        }
        
        $product->save();
        
        // Update extras
        $this->set_product_extras($product_id, $data);
        
        return $data['name'] . ' (Updated)';
    }
    
    /**
     * Get product by SKU
     */
    private function get_product_by_sku($sku) {
        if (empty($sku)) {
            return false;
        }
        return wc_get_product_id_by_sku($sku);
    }
    
    /**
     * Format price
     */
    private function format_price($price) {
        $price = str_replace(array(',', ' ', '₺', 'TL'), array('.', '', '', ''), $price);
        return floatval($price);
    }
    
    /**
     * Set product categories - supports WooCommerce hierarchical format
     */
    private function set_product_categories($product_id, $categories) {
        $category_ids = array();
        
        // WooCommerce uses comma to separate categories, > for hierarchy
        $category_paths = array_map('trim', explode(',', $categories));
        
        foreach ($category_paths as $path) {
            $path = trim($path);
            if (empty($path)) continue;
            
            // Handle hierarchical categories (Parent > Child > Grandchild)
            $parts = array_map('trim', explode('>', $path));
            $parent_id = 0;
            
            foreach ($parts as $name) {
                $name = trim($name);
                if (empty($name)) continue;
                
                // Look for existing term
                $term = get_term_by('name', $name, 'product_cat');
                
                if (!$term) {
                    // Also try by slug
                    $term = get_term_by('slug', sanitize_title($name), 'product_cat');
                }
                
                if (!$term) {
                    // Create new term
                    $result = wp_insert_term($name, 'product_cat', array('parent' => $parent_id));
                    if (!is_wp_error($result)) {
                        $parent_id = $result['term_id'];
                        $category_ids[] = $result['term_id'];
                    }
                } else {
                    $parent_id = $term->term_id;
                    $category_ids[] = $term->term_id;
                }
            }
        }
        
        if (!empty($category_ids)) {
            wp_set_object_terms($product_id, array_unique($category_ids), 'product_cat');
        }
    }
    
    /**
     * Set product tags
     */
    private function set_product_tags($product_id, $tags) {
        $tag_names = array_map('trim', explode(',', $tags));
        wp_set_object_terms($product_id, $tag_names, 'product_tag');
    }
    
    /**
     * Set shipping class
     */
    private function set_shipping_class($product_id, $shipping_class) {
        $term = get_term_by('name', $shipping_class, 'product_shipping_class');
        
        if (!$term) {
            $term = get_term_by('slug', sanitize_title($shipping_class), 'product_shipping_class');
        }
        
        if (!$term) {
            $result = wp_insert_term($shipping_class, 'product_shipping_class');
            if (!is_wp_error($result)) {
                wp_set_object_terms($product_id, $result['term_id'], 'product_shipping_class');
            }
        } else {
            wp_set_object_terms($product_id, $term->term_id, 'product_shipping_class');
        }
    }
    
    /**
     * Set product brands
     */
    private function set_product_brands($product_id, $brands) {
        // Check if brands taxonomy exists (from various brand plugins)
        $taxonomy = null;
        if (taxonomy_exists('product_brand')) {
            $taxonomy = 'product_brand';
        } elseif (taxonomy_exists('pwb-brand')) {
            $taxonomy = 'pwb-brand';
        } elseif (taxonomy_exists('brand')) {
            $taxonomy = 'brand';
        }
        
        if (!$taxonomy) return;
        
        $brand_names = array_map('trim', explode(',', $brands));
        $brand_ids = array();
        
        foreach ($brand_names as $name) {
            $term = get_term_by('name', $name, $taxonomy);
            if (!$term) {
                $result = wp_insert_term($name, $taxonomy);
                if (!is_wp_error($result)) {
                    $brand_ids[] = $result['term_id'];
                }
            } else {
                $brand_ids[] = $term->term_id;
            }
        }
        
        if (!empty($brand_ids)) {
            wp_set_object_terms($product_id, $brand_ids, $taxonomy);
        }
    }
    
    /**
     * Set linked products (upsells/cross-sells)
     */
    private function set_linked_products($product_id, $linked_skus, $meta_key) {
        $linked_ids = array();
        $skus = array_map('trim', explode(',', $linked_skus));
        
        foreach ($skus as $sku) {
            $linked_id = wc_get_product_id_by_sku($sku);
            if ($linked_id) {
                $linked_ids[] = $linked_id;
            }
        }
        
        if (!empty($linked_ids)) {
            update_post_meta($product_id, $meta_key, $linked_ids);
        }
    }
    
    /**
     * Set product image
     */
    private function set_product_image($product_id, $image_url) {
        $image_url = trim($image_url);
        
        // Check if it's already an attachment ID
        if (is_numeric($image_url)) {
            set_post_thumbnail($product_id, intval($image_url));
            return;
        }
        
        // Check if image already exists in media library
        $existing_id = $this->get_attachment_by_url($image_url);
        if ($existing_id) {
            set_post_thumbnail($product_id, $existing_id);
            return;
        }
        
        $attachment_id = $this->upload_image_from_url($image_url);
        if ($attachment_id) {
            set_post_thumbnail($product_id, $attachment_id);
        }
    }
    
    /**
     * Set product gallery
     */
    private function set_product_gallery($product_id, $gallery_urls) {
        $urls = array_map('trim', explode(',', $gallery_urls));
        $gallery_ids = array();
        
        foreach ($urls as $url) {
            $url = trim($url);
            if (empty($url)) continue;
            
            // Check if it's already an attachment ID
            if (is_numeric($url)) {
                $gallery_ids[] = intval($url);
                continue;
            }
            
            // Check if image already exists
            $existing_id = $this->get_attachment_by_url($url);
            if ($existing_id) {
                $gallery_ids[] = $existing_id;
                continue;
            }
            
            $attachment_id = $this->upload_image_from_url($url);
            if ($attachment_id) {
                $gallery_ids[] = $attachment_id;
            }
        }
        
        if (!empty($gallery_ids)) {
            update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_ids));
        }
    }
    
    /**
     * Get attachment ID by URL
     */
    private function get_attachment_by_url($url) {
        global $wpdb;
        
        $attachment = $wpdb->get_col($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts WHERE guid = %s",
            $url
        ));
        
        if (!empty($attachment[0])) {
            return $attachment[0];
        }
        
        // Try by filename
        $filename = basename(parse_url($url, PHP_URL_PATH));
        $attachment = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
            '%' . $wpdb->esc_like($filename)
        ));
        
        return !empty($attachment[0]) ? $attachment[0] : false;
    }
    
    /**
     * Upload image from URL
     */
    private function upload_image_from_url($url) {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        $tmp = download_url($url);
        
        if (is_wp_error($tmp)) {
            return false;
        }
        
        $file_array = array(
            'name' => basename(parse_url($url, PHP_URL_PATH)),
            'tmp_name' => $tmp
        );
        
        $attachment_id = media_handle_sideload($file_array, 0);
        
        if (is_wp_error($attachment_id)) {
            @unlink($tmp);
            return false;
        }
        
        return $attachment_id;
    }
}
