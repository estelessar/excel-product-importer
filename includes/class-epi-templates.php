<?php
/**
 * Mapping Templates class
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

class EPI_Templates {
    
    private $option_name = 'epi_mapping_templates';
    
    /**
     * Get all templates
     */
    public function get_templates() {
        $templates = get_option($this->option_name, array());
        return is_array($templates) ? $templates : array();
    }
    
    /**
     * Get single template
     */
    public function get_template($id) {
        $templates = $this->get_templates();
        return isset($templates[$id]) ? $templates[$id] : null;
    }
    
    /**
     * Save template
     */
    public function save_template($data) {
        $templates = $this->get_templates();
        
        $id = isset($data['id']) && !empty($data['id']) ? sanitize_key($data['id']) : 'tpl_' . time();
        
        $templates[$id] = array(
            'id' => $id,
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'mapping' => $data['mapping'],
            'options' => $data['options'] ?? array(),
            'created_at' => isset($templates[$id]) ? $templates[$id]['created_at'] : current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        update_option($this->option_name, $templates);
        
        return $id;
    }
    
    /**
     * Delete template
     */
    public function delete_template($id) {
        $templates = $this->get_templates();
        
        if (isset($templates[$id])) {
            unset($templates[$id]);
            update_option($this->option_name, $templates);
            return true;
        }
        
        return false;
    }
    
    /**
     * Duplicate template
     */
    public function duplicate_template($id) {
        $template = $this->get_template($id);
        
        if (!$template) {
            return false;
        }
        
        $new_id = 'tpl_' . time();
        $template['id'] = $new_id;
        $template['name'] = $template['name'] . ' (Kopya)';
        $template['created_at'] = current_time('mysql');
        $template['updated_at'] = current_time('mysql');
        
        $templates = $this->get_templates();
        $templates[$new_id] = $template;
        update_option($this->option_name, $templates);
        
        return $new_id;
    }
    
    /**
     * Export template as JSON
     */
    public function export_template($id) {
        $template = $this->get_template($id);
        
        if (!$template) {
            return false;
        }
        
        return json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Import template from JSON
     */
    public function import_template($json) {
        $data = json_decode($json, true);
        
        if (!$data || !isset($data['name']) || !isset($data['mapping'])) {
            return new WP_Error('invalid_json', __('Geçersiz şablon formatı.', 'excel-product-importer'));
        }
        
        // Generate new ID
        $data['id'] = 'tpl_' . time();
        
        return $this->save_template($data);
    }
}
