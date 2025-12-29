<?php
/**
 * Plugin Name: Excel Product Importer for WooCommerce
 * Plugin URI: https://www.adnbilisim.com.tr
 * Description: Excel dosyalarından WooCommerce'e toplu ürün yükleme eklentisi. Varyasyonlu ürün desteği ile.
 * Version: 1.0.0
 * Author: ADN Bilişim Teknolojileri LTD. ŞTİ.
 * Author URI: https://www.adnbilisim.com.tr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: excel-product-importer
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('EPI_VERSION', '1.0.0');
define('EPI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EPI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EPI_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if WooCommerce is active
 */
function epi_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'epi_woocommerce_missing_notice');
        return false;
    }
    return true;
}

/**
 * WooCommerce missing notice
 */
function epi_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Excel Product Importer için WooCommerce eklentisinin yüklü ve aktif olması gerekmektedir.', 'excel-product-importer'); ?></p>
    </div>
    <?php
}

/**
 * Initialize the plugin
 */
function epi_init() {
    if (!epi_check_woocommerce()) {
        return;
    }
    
    // Load classes
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-loader.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-admin.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-ajax-handler.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-excel-parser.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-product-creator.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-variation-handler.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-exporter.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-templates.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-history.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-stock-updater.php';
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-scheduler.php';
    
    // Initialize admin
    if (is_admin()) {
        new EPI_Admin();
        new EPI_Ajax_Handler();
    }
    
    // Initialize scheduler
    new EPI_Scheduler();
}
add_action('plugins_loaded', 'epi_init');

/**
 * Activation hook
 */
function epi_activate() {
    // Create upload directory
    $upload_dir = wp_upload_dir();
    $epi_dir = $upload_dir['basedir'] . '/epi-imports';
    
    if (!file_exists($epi_dir)) {
        wp_mkdir_p($epi_dir);
    }
    
    // Add .htaccess for security
    $htaccess = $epi_dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, 'deny from all');
    }
    
    // Set default options
    add_option('epi_settings', array(
        'batch_size' => 10,
        'skip_existing' => true,
        'update_existing' => false
    ));
    
    // Create history table
    require_once EPI_PLUGIN_DIR . 'includes/class-epi-history.php';
    EPI_History::create_table();
}
register_activation_hook(__FILE__, 'epi_activate');

/**
 * Deactivation hook
 */
function epi_deactivate() {
    // Clean up temporary files
    $upload_dir = wp_upload_dir();
    $epi_dir = $upload_dir['basedir'] . '/epi-imports';
    
    if (file_exists($epi_dir)) {
        array_map('unlink', glob("$epi_dir/*.*"));
    }
}
register_deactivation_hook(__FILE__, 'epi_deactivate');

/**
 * HPOS compatibility declaration
 */
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
