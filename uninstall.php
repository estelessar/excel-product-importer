<?php
/**
 * Uninstall Excel Product Importer
 * 
 * @package     Excel_Product_Importer
 * @author      ADN Bilişim Teknolojileri LTD. ŞTİ.
 * @copyright   2024 ADN Bilişim Teknolojileri LTD. ŞTİ.
 * @license     Proprietary - See LICENSE file
 * @link        https://www.adnbilisim.com.tr
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('epi_settings');

// Delete transients
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_epi_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_epi_%'");

// Delete upload directory
$upload_dir = wp_upload_dir();
$epi_dir = $upload_dir['basedir'] . '/epi-imports';

if (file_exists($epi_dir)) {
    // Delete all files in directory
    $files = glob($epi_dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    // Delete directory
    rmdir($epi_dir);
}
