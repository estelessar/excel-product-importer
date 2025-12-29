<?php
/**
 * Autoloader class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EPI_Loader {
    
    /**
     * Register autoloader
     */
    public static function register() {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    
    /**
     * Autoload classes
     */
    public static function autoload($class) {
        $prefix = 'EPI_';
        
        if (strpos($class, $prefix) !== 0) {
            return;
        }
        
        $class_name = str_replace($prefix, '', $class);
        $class_name = strtolower(str_replace('_', '-', $class_name));
        $file = EPI_PLUGIN_DIR . 'includes/class-epi-' . $class_name . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

EPI_Loader::register();
