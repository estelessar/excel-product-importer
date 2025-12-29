<?php
/**
 * Import History class
 * 
 * @package     Excel_Product_Importer
 * @author      ADN Bilişim Teknolojileri LTD. ŞTİ.
 * @copyright   2024 ADN Bilişim Teknolojileri LTD. ŞTİ.
 * @license     Proprietary - See LICENSE file
 * @link        https://www.adnbilisim.com.tr
 */

if (!defined('ABSPATH')) {
    exit;
}

class EPI_History {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'epi_import_history';
    }
    
    /**
     * Create history table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'epi_import_history';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            filename varchar(255) NOT NULL,
            file_size bigint(20) DEFAULT 0,
            total_rows int(11) DEFAULT 0,
            success_count int(11) DEFAULT 0,
            error_count int(11) DEFAULT 0,
            skipped_count int(11) DEFAULT 0,
            product_ids longtext,
            mapping longtext,
            options longtext,
            status varchar(20) DEFAULT 'completed',
            error_log longtext,
            user_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Log import
     */
    public function log_import($data) {
        global $wpdb;
        
        $wpdb->insert(
            $this->table_name,
            array(
                'filename' => sanitize_file_name($data['filename']),
                'file_size' => intval($data['file_size']),
                'total_rows' => intval($data['total_rows']),
                'success_count' => intval($data['success_count']),
                'error_count' => intval($data['error_count']),
                'skipped_count' => intval($data['skipped_count']),
                'product_ids' => json_encode($data['product_ids']),
                'mapping' => json_encode($data['mapping']),
                'options' => json_encode($data['options']),
                'status' => sanitize_text_field($data['status']),
                'error_log' => json_encode($data['error_log']),
                'user_id' => get_current_user_id()
            ),
            array('%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get import history
     */
    public function get_history($limit = 20, $offset = 0) {
        global $wpdb;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT h.*, u.display_name as user_name 
                FROM {$this->table_name} h 
                LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID 
                ORDER BY h.created_at DESC 
                LIMIT %d OFFSET %d",
                $limit,
                $offset
            ),
            ARRAY_A
        );
        
        return $results;
    }
    
    /**
     * Get single import record
     */
    public function get_import($id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );
    }
    
    /**
     * Get total count
     */
    public function get_total_count() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }
    
    /**
     * Rollback import - delete imported products
     */
    public function rollback($id) {
        $import = $this->get_import($id);
        
        if (!$import) {
            return new WP_Error('not_found', __('İçe aktarma kaydı bulunamadı.', 'excel-product-importer'));
        }
        
        $product_ids = json_decode($import['product_ids'], true);
        
        if (empty($product_ids)) {
            return new WP_Error('no_products', __('Geri alınacak ürün bulunamadı.', 'excel-product-importer'));
        }
        
        $deleted = 0;
        
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                // Delete variations first
                if ($product->is_type('variable')) {
                    foreach ($product->get_children() as $child_id) {
                        wp_delete_post($child_id, true);
                    }
                }
                wp_delete_post($product_id, true);
                $deleted++;
            }
        }
        
        // Update status
        global $wpdb;
        $wpdb->update(
            $this->table_name,
            array('status' => 'rolled_back'),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        return $deleted;
    }
    
    /**
     * Delete old history records
     */
    public function cleanup($days = 30) {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
    }
    
    /**
     * Get statistics
     */
    public function get_stats() {
        global $wpdb;
        
        $stats = $wpdb->get_row(
            "SELECT 
                COUNT(*) as total_imports,
                SUM(success_count) as total_products,
                SUM(error_count) as total_errors,
                MAX(created_at) as last_import
            FROM {$this->table_name}",
            ARRAY_A
        );
        
        return $stats;
    }
}
