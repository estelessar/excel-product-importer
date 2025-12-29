<?php
/**
 * Product Exporter class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EPI_Exporter {
    
    /**
     * Export products to CSV
     */
    public function export($filters = array()) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
        
        // Category filter
        if (!empty($filters['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => intval($filters['category'])
                )
            );
        }
        
        // Stock status filter
        if (!empty($filters['stock_status'])) {
            $args['meta_query'][] = array(
                'key' => '_stock_status',
                'value' => sanitize_text_field($filters['stock_status'])
            );
        }
        
        // Price range filter
        if (!empty($filters['price_min'])) {
            $args['meta_query'][] = array(
                'key' => '_price',
                'value' => floatval($filters['price_min']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if (!empty($filters['price_max'])) {
            $args['meta_query'][] = array(
                'key' => '_price',
                'value' => floatval($filters['price_max']),
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }
        
        // Product type filter
        if (!empty($filters['product_type'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($filters['product_type'])
            );
        }
        
        $products = get_posts($args);
        $export_data = array();
        
        // Headers
        $headers = array(
            'product_name',
            'sku',
            'description',
            'short_description',
            'regular_price',
            'sale_price',
            'stock_quantity',
            'stock_status',
            'category',
            'tags',
            'image_url',
            'gallery_urls',
            'weight',
            'length',
            'width',
            'height',
            'product_type',
            'parent_sku',
            'attribute_1_name',
            'attribute_1_values',
            'attribute_2_name',
            'attribute_2_values',
            'attribute_3_name',
            'attribute_3_values'
        );
        
        foreach ($products as $post) {
            $product = wc_get_product($post->ID);
            
            if (!$product) continue;
            
            $row = $this->get_product_row($product);
            $export_data[] = $row;
            
            // Export variations for variable products
            if ($product->is_type('variable')) {
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    $variation = wc_get_product($variation_id);
                    if ($variation) {
                        $var_row = $this->get_variation_row($variation, $product->get_sku());
                        $export_data[] = $var_row;
                    }
                }
            }
        }
        
        return array(
            'headers' => $headers,
            'data' => $export_data,
            'count' => count($export_data)
        );
    }
    
    /**
     * Get product row data
     */
    private function get_product_row($product) {
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
        $tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
        
        // Get image URLs
        $image_url = '';
        $image_id = $product->get_image_id();
        if ($image_id) {
            $image_url = wp_get_attachment_url($image_id);
        }
        
        // Gallery URLs
        $gallery_ids = $product->get_gallery_image_ids();
        $gallery_urls = array();
        foreach ($gallery_ids as $gid) {
            $gallery_urls[] = wp_get_attachment_url($gid);
        }
        
        // Attributes
        $attributes = $product->get_attributes();
        $attr_data = $this->format_attributes($attributes);
        
        // Product type
        $product_type = 'simple';
        if ($product->is_type('variable')) {
            $product_type = 'variable';
        } elseif ($product->is_type('grouped')) {
            $product_type = 'grouped';
        } elseif ($product->is_type('external')) {
            $product_type = 'external';
        }
        
        return array(
            $product->get_name(),
            $product->get_sku(),
            $product->get_description(),
            $product->get_short_description(),
            $product->get_regular_price(),
            $product->get_sale_price(),
            $product->get_stock_quantity(),
            $product->get_stock_status(),
            implode(', ', $categories),
            implode(', ', $tags),
            $image_url,
            implode(', ', $gallery_urls),
            $product->get_weight(),
            $product->get_length(),
            $product->get_width(),
            $product->get_height(),
            $product_type,
            '',
            $attr_data[0]['name'] ?? '',
            $attr_data[0]['values'] ?? '',
            $attr_data[1]['name'] ?? '',
            $attr_data[1]['values'] ?? '',
            $attr_data[2]['name'] ?? '',
            $attr_data[2]['values'] ?? ''
        );
    }
    
    /**
     * Get variation row data
     */
    private function get_variation_row($variation, $parent_sku) {
        $attributes = $variation->get_attributes();
        $attr_data = array();
        
        $i = 0;
        foreach ($attributes as $name => $value) {
            $attr_data[$i] = array(
                'name' => wc_attribute_label($name),
                'values' => $value
            );
            $i++;
        }
        
        $image_url = '';
        $image_id = $variation->get_image_id();
        if ($image_id) {
            $image_url = wp_get_attachment_url($image_id);
        }
        
        return array(
            $variation->get_name(),
            $variation->get_sku(),
            '',
            '',
            $variation->get_regular_price(),
            $variation->get_sale_price(),
            $variation->get_stock_quantity(),
            $variation->get_stock_status(),
            '',
            '',
            $image_url,
            '',
            $variation->get_weight(),
            $variation->get_length(),
            $variation->get_width(),
            $variation->get_height(),
            'variation',
            $parent_sku,
            $attr_data[0]['name'] ?? '',
            $attr_data[0]['values'] ?? '',
            $attr_data[1]['name'] ?? '',
            $attr_data[1]['values'] ?? '',
            $attr_data[2]['name'] ?? '',
            $attr_data[2]['values'] ?? ''
        );
    }
    
    /**
     * Format attributes for export
     */
    private function format_attributes($attributes) {
        $result = array();
        $i = 0;
        
        foreach ($attributes as $attribute) {
            if ($i >= 3) break;
            
            $name = '';
            $values = array();
            
            if ($attribute instanceof WC_Product_Attribute) {
                $name = wc_attribute_label($attribute->get_name());
                $values = $attribute->get_options();
            }
            
            $result[$i] = array(
                'name' => $name,
                'values' => implode('|', $values)
            );
            $i++;
        }
        
        return $result;
    }
    
    /**
     * Generate CSV content
     */
    public function generate_csv($data) {
        $output = fopen('php://temp', 'r+');
        
        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, $data['headers'], ';');
        
        // Data rows
        foreach ($data['data'] as $row) {
            fputcsv($output, $row, ';');
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
