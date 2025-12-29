<?php
/**
 * Scheduled Import class
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

class EPI_Scheduler {
    
    private $option_name = 'epi_scheduled_imports';
    private $cron_hook = 'epi_scheduled_import';
    
    public function __construct() {
        add_action($this->cron_hook, array($this, 'run_scheduled_import'), 10, 1);
    }
    
    /**
     * Get all scheduled imports
     */
    public function get_schedules() {
        $schedules = get_option($this->option_name, array());
        return is_array($schedules) ? $schedules : array();
    }
    
    /**
     * Get single schedule
     */
    public function get_schedule($id) {
        $schedules = $this->get_schedules();
        return isset($schedules[$id]) ? $schedules[$id] : null;
    }
    
    /**
     * Save schedule
     */
    public function save_schedule($data) {
        $schedules = $this->get_schedules();
        
        $id = isset($data['id']) && !empty($data['id']) ? sanitize_key($data['id']) : 'sch_' . time();
        
        $schedule = array(
            'id' => $id,
            'name' => sanitize_text_field($data['name']),
            'source_type' => sanitize_text_field($data['source_type']), // url, ftp, google_sheets
            'source_url' => esc_url_raw($data['source_url']),
            'ftp_host' => sanitize_text_field($data['ftp_host'] ?? ''),
            'ftp_user' => sanitize_text_field($data['ftp_user'] ?? ''),
            'ftp_pass' => $data['ftp_pass'] ?? '',
            'ftp_path' => sanitize_text_field($data['ftp_path'] ?? ''),
            'frequency' => sanitize_text_field($data['frequency']), // hourly, daily, weekly
            'time' => sanitize_text_field($data['time'] ?? '03:00'),
            'day_of_week' => intval($data['day_of_week'] ?? 1),
            'template_id' => sanitize_key($data['template_id'] ?? ''),
            'mode' => sanitize_text_field($data['mode'] ?? 'full'), // full, stock_only
            'enabled' => !empty($data['enabled']),
            'last_run' => isset($schedules[$id]) ? $schedules[$id]['last_run'] : null,
            'last_status' => isset($schedules[$id]) ? $schedules[$id]['last_status'] : null,
            'created_at' => isset($schedules[$id]) ? $schedules[$id]['created_at'] : current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        $schedules[$id] = $schedule;
        update_option($this->option_name, $schedules);
        
        // Update cron
        $this->update_cron($id, $schedule);
        
        return $id;
    }
    
    /**
     * Delete schedule
     */
    public function delete_schedule($id) {
        $schedules = $this->get_schedules();
        
        if (isset($schedules[$id])) {
            // Remove cron
            $this->remove_cron($id);
            
            unset($schedules[$id]);
            update_option($this->option_name, $schedules);
            return true;
        }
        
        return false;
    }
    
    /**
     * Toggle schedule enabled/disabled
     */
    public function toggle_schedule($id) {
        $schedule = $this->get_schedule($id);
        
        if (!$schedule) {
            return false;
        }
        
        $schedule['enabled'] = !$schedule['enabled'];
        $this->save_schedule($schedule);
        
        return $schedule['enabled'];
    }
    
    /**
     * Update cron job
     */
    private function update_cron($id, $schedule) {
        // Remove existing
        $this->remove_cron($id);
        
        if (!$schedule['enabled']) {
            return;
        }
        
        // Calculate next run time
        $next_run = $this->calculate_next_run($schedule);
        
        wp_schedule_single_event($next_run, $this->cron_hook, array($id));
    }
    
    /**
     * Remove cron job
     */
    private function remove_cron($id) {
        $timestamp = wp_next_scheduled($this->cron_hook, array($id));
        if ($timestamp) {
            wp_unschedule_event($timestamp, $this->cron_hook, array($id));
        }
    }
    
    /**
     * Calculate next run time
     */
    private function calculate_next_run($schedule) {
        $time_parts = explode(':', $schedule['time']);
        $hour = intval($time_parts[0]);
        $minute = intval($time_parts[1] ?? 0);
        
        $now = current_time('timestamp');
        
        switch ($schedule['frequency']) {
            case 'hourly':
                $next = strtotime('+1 hour', $now);
                break;
                
            case 'daily':
                $today = strtotime("today {$schedule['time']}", $now);
                $next = $today > $now ? $today : strtotime('+1 day', $today);
                break;
                
            case 'weekly':
                $day_name = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
                $target_day = $day_name[$schedule['day_of_week']];
                $next = strtotime("next {$target_day} {$schedule['time']}", $now);
                break;
                
            default:
                $next = strtotime('+1 day', $now);
        }
        
        return $next;
    }
    
    /**
     * Run scheduled import
     */
    public function run_scheduled_import($schedule_id) {
        $schedule = $this->get_schedule($schedule_id);
        
        if (!$schedule || !$schedule['enabled']) {
            return;
        }
        
        // Download file
        $file_path = $this->download_file($schedule);
        
        if (is_wp_error($file_path)) {
            $this->update_schedule_status($schedule_id, 'error', $file_path->get_error_message());
            $this->reschedule($schedule_id);
            return;
        }
        
        // Parse file
        $parser = new EPI_Excel_Parser();
        $data = $parser->parse($file_path);
        
        if (is_wp_error($data)) {
            $this->update_schedule_status($schedule_id, 'error', $data->get_error_message());
            @unlink($file_path);
            $this->reschedule($schedule_id);
            return;
        }
        
        // Get template mapping
        $mapping = array();
        if (!empty($schedule['template_id'])) {
            $templates = new EPI_Templates();
            $template = $templates->get_template($schedule['template_id']);
            if ($template) {
                $mapping = $template['mapping'];
            }
        }
        
        // Run import
        if ($schedule['mode'] === 'stock_only') {
            $updater = new EPI_Stock_Updater();
            $results = $updater->bulk_update($data['rows'], $mapping);
        } else {
            $creator = new EPI_Product_Creator();
            $results = array('success' => 0, 'errors' => 0);
            
            foreach ($data['rows'] as $row) {
                $result = $creator->create_product($row, $mapping, array('skip_existing' => false, 'update_existing' => true));
                if (is_wp_error($result)) {
                    $results['errors']++;
                } else {
                    $results['success']++;
                }
            }
        }
        
        // Update status
        $status_msg = sprintf(
            __('Başarılı: %d, Hata: %d', 'excel-product-importer'),
            $results['success'],
            $results['errors']
        );
        $this->update_schedule_status($schedule_id, 'success', $status_msg);
        
        // Cleanup
        @unlink($file_path);
        
        // Reschedule
        $this->reschedule($schedule_id);
    }
    
    /**
     * Download file from source
     */
    private function download_file($schedule) {
        $upload_dir = wp_upload_dir();
        $target_dir = $upload_dir['basedir'] . '/epi-imports';
        
        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }
        
        $filename = 'scheduled_' . $schedule['id'] . '_' . time() . '.csv';
        $target_path = $target_dir . '/' . $filename;
        
        switch ($schedule['source_type']) {
            case 'url':
                $response = wp_remote_get($schedule['source_url'], array('timeout' => 60));
                
                if (is_wp_error($response)) {
                    return $response;
                }
                
                $body = wp_remote_retrieve_body($response);
                file_put_contents($target_path, $body);
                break;
                
            case 'ftp':
                $ftp = ftp_connect($schedule['ftp_host']);
                
                if (!$ftp) {
                    return new WP_Error('ftp_error', __('FTP bağlantısı kurulamadı.', 'excel-product-importer'));
                }
                
                if (!ftp_login($ftp, $schedule['ftp_user'], $schedule['ftp_pass'])) {
                    ftp_close($ftp);
                    return new WP_Error('ftp_login', __('FTP girişi başarısız.', 'excel-product-importer'));
                }
                
                ftp_pasv($ftp, true);
                
                if (!ftp_get($ftp, $target_path, $schedule['ftp_path'], FTP_BINARY)) {
                    ftp_close($ftp);
                    return new WP_Error('ftp_download', __('FTP dosya indirme başarısız.', 'excel-product-importer'));
                }
                
                ftp_close($ftp);
                break;
                
            case 'google_sheets':
                // Google Sheets export URL
                $url = $schedule['source_url'];
                if (strpos($url, '/edit') !== false) {
                    $url = preg_replace('/\/edit.*$/', '/export?format=csv', $url);
                }
                
                $response = wp_remote_get($url, array('timeout' => 60));
                
                if (is_wp_error($response)) {
                    return $response;
                }
                
                $body = wp_remote_retrieve_body($response);
                file_put_contents($target_path, $body);
                break;
                
            default:
                return new WP_Error('invalid_source', __('Geçersiz kaynak tipi.', 'excel-product-importer'));
        }
        
        return $target_path;
    }
    
    /**
     * Update schedule status
     */
    private function update_schedule_status($id, $status, $message = '') {
        $schedules = $this->get_schedules();
        
        if (isset($schedules[$id])) {
            $schedules[$id]['last_run'] = current_time('mysql');
            $schedules[$id]['last_status'] = $status;
            $schedules[$id]['last_message'] = $message;
            update_option($this->option_name, $schedules);
        }
    }
    
    /**
     * Reschedule for next run
     */
    private function reschedule($id) {
        $schedule = $this->get_schedule($id);
        if ($schedule && $schedule['enabled']) {
            $this->update_cron($id, $schedule);
        }
    }
    
    /**
     * Run import manually
     */
    public function run_now($id) {
        $this->run_scheduled_import($id);
    }
}
