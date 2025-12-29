<?php
/**
 * Stock & Price Updater class
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

class EPI_Stock_Updater {
    
    /**
     * Update stock and price only
     */
    public function update($row, $mapping) {
        $sku = $this->get_mapped_value($row, $mapping, 'sku');
        
        if (empty($sku)) {
            return new WP_Error('missing_sku', __('SKU zorunludur.', 'excel-product-importer'));
        }
        
        $product_id = wc_get_product_id_by_sku($sku);
        
        if (!$product_id) {
            return new WP_Error('not_found', sprintf(__('Ürün bulunamadı: %s', 'excel-product-importer'), $sku));
        }
        
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return new WP_Error('invalid_product', __('Geçersiz ürün.', 'excel-product-importer'));
        }
        
        $updated = array();
        
        // Update regular price
        $regular_price = $this->get_mapped_value($row, $mapping, 'regular_price');
        if ($regular_price !== null && $regular_price !== '') {
            $old_price = $product->get_regular_price();
            $product->set_regular_price($this->format_price($regular_price));
            $updated['regular_price'] = array('old' => $old_price, 'new' => $regular_price);
        }
        
        // Update sale price
        $sale_price = $this->get_mapped_value($row, $mapping, 'sale_price');
        if ($sale_price !== null) {
            $old_sale = $product->get_sale_price();
            if ($sale_price === '' || $sale_price === '0') {
                $product->set_sale_price('');
            } else {
                $product->set_sale_price($this->format_price($sale_price));
            }
            $updated['sale_price'] = array('old' => $old_sale, 'new' => $sale_price);
        }
        
        // Update stock quantity
        $stock = $this->get_mapped_value($row, $mapping, 'stock_quantity');
        if ($stock !== null && $stock !== '') {
            $old_stock = $product->get_stock_quantity();
            $product->set_manage_stock(true);
            $product->set_stock_quantity(intval($stock));
            $updated['stock'] = array('old' => $old_stock, 'new' => $stock);
        }
        
        // Update stock status
        $stock_status = $this->get_mapped_value($row, $mapping, 'stock_status');
        if ($stock_status !== null && $stock_status !== '') {
            $old_status = $product->get_stock_status();
            $product->set_stock_status(sanitize_text_field($stock_status));
            $updated['stock_status'] = array('old' => $old_status, 'new' => $stock_status);
        }
        
        if (empty($updated)) {
            return 'no_changes';
        }
        
        $product->save();
        
        return array(
            'product_id' => $product_id,
            'sku' => $sku,
            'name' => $product->get_name(),
            'changes' => $updated
        );
    }
    
    /**
     * Bulk update from data
     */
    public function bulk_update($rows, $mapping) {
        $results = array(
            'success' => 0,
            'errors' => 0,
            'no_changes' => 0,
            'messages' => array()
        );
        
        foreach ($rows as $index => $row) {
            $result = $this->update($row, $mapping);
            
            if (is_wp_error($result)) {
                $results['errors']++;
                $results['messages'][] = array(
                    'type' => 'error',
                    'row' => $index + 2,
                    'message' => $result->get_error_message()
                );
            } elseif ($result === 'no_changes') {
                $results['no_changes']++;
                $results['messages'][] = array(
                    'type' => 'warning',
                    'row' => $index + 2,
                    'message' => __('Değişiklik yok.', 'excel-product-importer')
                );
            } else {
                $results['success']++;
                $changes = array();
                foreach ($result['changes'] as $field => $change) {
                    $changes[] = "$field: {$change['old']} → {$change['new']}";
                }
                $results['messages'][] = array(
                    'type' => 'success',
                    'row' => $index + 2,
                    'message' => sprintf(
                        __('%s güncellendi: %s', 'excel-product-importer'),
                        $result['name'],
                        implode(', ', $changes)
                    )
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Get mapped value from row
     */
    private function get_mapped_value($row, $mapping, $field) {
        foreach ($mapping as $excel_col => $woo_field) {
            if ($woo_field === $field && isset($row[$excel_col])) {
                return trim($row[$excel_col]);
            }
        }
        return null;
    }
    
    /**
     * Format price
     */
    private function format_price($price) {
        $price = str_replace(array(',', ' '), array('.', ''), $price);
        return floatval($price);
    }
}
