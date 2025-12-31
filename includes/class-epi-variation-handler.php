<?php
/**
 * Variation Handler class
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

class EPI_Variation_Handler {
    
    /**
     * Create product variation
     */
    public function create_variation($parent_id, $data) {
        $parent = wc_get_product($parent_id);
        
        if (!$parent || !$parent->is_type('variable')) {
            return new WP_Error('invalid_parent', __('Geçersiz parent ürün.', 'excel-product-importer'));
        }
        
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($parent_id);
        
        if (!empty($data['sku'])) {
            $variation->set_sku($data['sku']);
        }
        
        if (!empty($data['regular_price'])) {
            $variation->set_regular_price($this->format_price($data['regular_price']));
        }
        
        if (!empty($data['sale_price'])) {
            $variation->set_sale_price($this->format_price($data['sale_price']));
        } elseif (isset($data['sale_price']) && $data['sale_price'] === '') {
            $variation->set_sale_price('');
        }
        
        // Sale price dates
        if (!empty($data['sale_price_dates_from'])) {
            $variation->set_date_on_sale_from($data['sale_price_dates_from']);
        }
        if (!empty($data['sale_price_dates_to'])) {
            $variation->set_date_on_sale_to($data['sale_price_dates_to']);
        }
        
        // Stock management
        if (isset($data['stock_quantity']) && $data['stock_quantity'] !== '') {
            $variation->set_manage_stock(true);
            $stock_qty = intval($data['stock_quantity']);
            $variation->set_stock_quantity($stock_qty);
            
            // Otomatik stok durumu ayarla
            if ($stock_qty <= 0) {
                $variation->set_stock_status('outofstock');
            } else {
                $variation->set_stock_status('instock');
            }
        }
        
        // Manuel stok durumu (varsa üzerine yazar)
        if (isset($data['in_stock'])) {
            $stock_status = ($data['in_stock'] == '1' || strtolower($data['in_stock']) === 'instock') ? 'instock' : 'outofstock';
            $variation->set_stock_status($stock_status);
        }
        
        if (isset($data['backorders_allowed'])) {
            $backorders = $data['backorders_allowed'] == '1' ? 'yes' : 'no';
            $variation->set_backorders($backorders);
        }
        
        if (!empty($data['low_stock_amount'])) {
            $variation->set_low_stock_amount(intval($data['low_stock_amount']));
        }
        
        // Set variation attributes
        if (!empty($data['attributes'])) {
            $attributes = array();
            
            foreach ($data['attributes'] as $name => $attr_data) {
                $attr_slug = sanitize_title($name);
                // Handle both array format and simple value
                if (is_array($attr_data)) {
                    $value = isset($attr_data['values']) ? $attr_data['values'][0] : (is_array($attr_data) ? $attr_data[0] : $attr_data);
                } else {
                    $value = $attr_data;
                }
                $attributes[$attr_slug] = $value;
            }
            
            $variation->set_attributes($attributes);
        }
        
        // Dimensions
        if (!empty($data['weight'])) {
            $variation->set_weight($data['weight']);
        }
        if (!empty($data['length'])) {
            $variation->set_length($data['length']);
        }
        if (!empty($data['width'])) {
            $variation->set_width($data['width']);
        }
        if (!empty($data['height'])) {
            $variation->set_height($data['height']);
        }
        
        // Tax
        if (!empty($data['tax_status'])) {
            $variation->set_tax_status($data['tax_status']);
        }
        if (!empty($data['tax_class'])) {
            $variation->set_tax_class($data['tax_class']);
        }
        
        // Description
        if (!empty($data['description'])) {
            $variation->set_description($data['description']);
        }
        
        // Status
        $status = 'publish';
        if (isset($data['published'])) {
            $status = ($data['published'] == '1' || $data['published'] === 'publish') ? 'publish' : 'private';
        }
        $variation->set_status($status);
        
        $variation_id = $variation->save();
        
        // Set image
        if (!empty($data['image_url']) || !empty($data['images'])) {
            $image = !empty($data['image_url']) ? $data['image_url'] : $data['images'];
            // Get first image if multiple
            if (strpos($image, ',') !== false) {
                $image = trim(explode(',', $image)[0]);
            }
            $this->set_variation_image($variation_id, $image);
        }
        
        // Sync parent product
        WC_Product_Variable::sync($parent_id);
        
        return $data['name'] ?? 'Variation #' . $variation_id;
    }
    
    /**
     * Create all variations for a variable product
     */
    public function create_all_variations($parent_id) {
        $parent = wc_get_product($parent_id);
        
        if (!$parent || !$parent->is_type('variable')) {
            return new WP_Error('invalid_parent', __('Geçersiz parent ürün.', 'excel-product-importer'));
        }
        
        $attributes = $parent->get_attributes();
        
        if (empty($attributes)) {
            return new WP_Error('no_attributes', __('Ürünün özniteliği yok.', 'excel-product-importer'));
        }
        
        // Get all attribute options
        $attribute_options = array();
        
        foreach ($attributes as $attribute) {
            if ($attribute->get_variation()) {
                $name = $attribute->get_name();
                $options = $attribute->get_options();
                $attribute_options[$name] = $options;
            }
        }
        
        // Generate all combinations
        $combinations = $this->generate_combinations($attribute_options);
        
        $created = 0;
        
        foreach ($combinations as $combination) {
            $variation = new WC_Product_Variation();
            $variation->set_parent_id($parent_id);
            
            $attrs = array();
            foreach ($combination as $attr_name => $attr_value) {
                $attrs[sanitize_title($attr_name)] = $attr_value;
            }
            
            $variation->set_attributes($attrs);
            $variation->set_status('publish');
            $variation->save();
            
            $created++;
        }
        
        WC_Product_Variable::sync($parent_id);
        
        return $created;
    }
    
    /**
     * Generate all attribute combinations
     */
    private function generate_combinations($arrays) {
        $result = array(array());
        
        foreach ($arrays as $key => $values) {
            $temp = array();
            
            foreach ($result as $item) {
                foreach ($values as $value) {
                    $temp[] = array_merge($item, array($key => $value));
                }
            }
            
            $result = $temp;
        }
        
        return $result;
    }
    
    /**
     * Format price
     */
    private function format_price($price) {
        $price = str_replace(array(',', ' ', '₺', 'TL'), array('.', '', '', ''), $price);
        return floatval($price);
    }
    
    /**
     * Set variation image
     */
    private function set_variation_image($variation_id, $image_url) {
        $image_url = trim($image_url);
        
        // Check if it's already an attachment ID
        if (is_numeric($image_url)) {
            $variation = wc_get_product($variation_id);
            if ($variation) {
                $variation->set_image_id(intval($image_url));
                $variation->save();
            }
            return intval($image_url);
        }
        
        if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check if image already exists in media library
        $existing_id = $this->get_attachment_by_url($image_url);
        if ($existing_id) {
            $variation = wc_get_product($variation_id);
            if ($variation) {
                $variation->set_image_id($existing_id);
                $variation->save();
            }
            return $existing_id;
        }
        
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        $tmp = download_url($image_url);
        
        if (is_wp_error($tmp)) {
            return false;
        }
        
        $file_array = array(
            'name' => basename(parse_url($image_url, PHP_URL_PATH)),
            'tmp_name' => $tmp
        );
        
        $attachment_id = media_handle_sideload($file_array, 0);
        
        if (is_wp_error($attachment_id)) {
            @unlink($tmp);
            return false;
        }
        
        $variation = wc_get_product($variation_id);
        if ($variation) {
            $variation->set_image_id($attachment_id);
            $variation->save();
        }
        
        return $attachment_id;
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
     * Update variation
     */
    public function update_variation($variation_id, $data) {
        $variation = wc_get_product($variation_id);
        
        if (!$variation || !$variation->is_type('variation')) {
            return new WP_Error('invalid_variation', __('Geçersiz varyasyon.', 'excel-product-importer'));
        }
        
        if (!empty($data['regular_price'])) {
            $variation->set_regular_price($this->format_price($data['regular_price']));
        }
        
        if (!empty($data['sale_price'])) {
            $variation->set_sale_price($this->format_price($data['sale_price']));
        }
        
        if (isset($data['stock_quantity']) && $data['stock_quantity'] !== '') {
            $variation->set_manage_stock(true);
            $variation->set_stock_quantity(intval($data['stock_quantity']));
        }
        
        $variation->save();
        
        return true;
    }
    
    /**
     * Delete all variations
     */
    public function delete_all_variations($parent_id) {
        $parent = wc_get_product($parent_id);
        
        if (!$parent || !$parent->is_type('variable')) {
            return false;
        }
        
        $variations = $parent->get_children();
        
        foreach ($variations as $variation_id) {
            $variation = wc_get_product($variation_id);
            if ($variation) {
                $variation->delete(true);
            }
        }
        
        return count($variations);
    }
}
