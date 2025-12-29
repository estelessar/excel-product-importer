<?php
/**
 * Admin class
 * 
 * @package     Excel_Product_Importer
 * @author      ADN Bilişim Teknolojileri LTD. ŞTİ.
 * @copyright   2024 ADN Bilişim Teknolojileri LTD. ŞTİ.
 * @license     Proprietary - See LICENSE file
 * @link        https://www.adnbilisim.com.tr
 * 
 * NOTICE: Bu dosyanın izinsiz kopyalanması, dağıtılması veya satılması yasaktır.
 * Unauthorized copying, distribution, or selling of this file is prohibited.
 */

if (!defined('ABSPATH')) {
    exit;
}

class EPI_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_footer', array($this, 'force_light_theme_css'), 9999);
    }
    
    /**
     * Add admin menu page
     */
    public function add_menu_page() {
        add_menu_page(
            __('Excel Ürün İçe Aktarıcı', 'excel-product-importer'),
            __('Excel İçe Aktar', 'excel-product-importer'),
            'manage_woocommerce',
            'excel-product-importer',
            array($this, 'render_admin_page'),
            'dashicons-upload',
            56
        );
    }
    
    /**
     * Force light theme CSS - loads LAST to override dark mode plugins
     * Uses extremely specific selectors to override any dark mode plugin
     */
    public function force_light_theme_css() {
        $screen = get_current_screen();
        if ($screen->id !== 'toplevel_page_excel-product-importer') {
            return;
        }
        ?>
        <style type="text/css" id="epi-force-light-theme">
            /* =============================================
               NUCLEAR RESET - Force light on content area
               ============================================= */
            html body.wp-admin.toplevel_page_excel-product-importer #wpcontent,
            html body.wp-admin.toplevel_page_excel-product-importer #wpbody,
            html body.wp-admin.toplevel_page_excel-product-importer #wpbody-content,
            body.toplevel_page_excel-product-importer #wpcontent,
            body.toplevel_page_excel-product-importer #wpbody,
            body.toplevel_page_excel-product-importer #wpbody-content {
                background: #f0f0f1 !important;
                background-color: #f0f0f1 !important;
            }
            
            /* Reset ALL elements - exclude SVG elements */
            html body .epi-wrapper,
            html body .epi-wrapper *:not(svg):not(path):not(circle):not(line):not(polyline):not(rect):not(input):not(select):not(textarea):not(option) {
                background: transparent !important;
                background-color: transparent !important;
                color: #1d2327 !important;
            }
            
            /* =============================================
               GLOBAL FORM ELEMENTS - HIGHEST PRIORITY
               Force white background on ALL form inputs
               ============================================= */
            html body.wp-admin .epi-wrapper input[type="text"],
            html body.wp-admin .epi-wrapper input[type="number"],
            html body.wp-admin .epi-wrapper input[type="url"],
            html body.wp-admin .epi-wrapper input[type="email"],
            html body.wp-admin .epi-wrapper input[type="password"],
            html body.wp-admin .epi-wrapper input[type="time"],
            html body.wp-admin .epi-wrapper input[type="date"],
            html body.wp-admin .epi-wrapper select,
            html body.wp-admin .epi-wrapper textarea,
            html body.wp-admin .epi-wrapper .epi-input,
            html body.wp-admin .epi-wrapper .epi-select,
            html body.wp-admin .epi-wrapper .epi-textarea,
            html body .epi-wrapper input[type="text"],
            html body .epi-wrapper input[type="number"],
            html body .epi-wrapper input[type="url"],
            html body .epi-wrapper input[type="email"],
            html body .epi-wrapper input[type="password"],
            html body .epi-wrapper input[type="time"],
            html body .epi-wrapper input[type="date"],
            html body .epi-wrapper select,
            html body .epi-wrapper textarea,
            .epi-wrapper input,
            .epi-wrapper select,
            .epi-wrapper textarea {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #8c8f94 !important;
                -webkit-appearance: none !important;
            }
            
            /* Select option elements */
            html body .epi-wrapper select option,
            .epi-wrapper select option {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
            }
            
            /* Export page specific form elements */
            html body .epi-wrapper #tab-export input,
            html body .epi-wrapper #tab-export select,
            html body .epi-wrapper .epi-export-filters input,
            html body .epi-wrapper .epi-export-filters select,
            #tab-export input,
            #tab-export select,
            .epi-export-filters input,
            .epi-export-filters select {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #8c8f94 !important;
            }
            
            /* Stock page specific form elements */
            html body .epi-wrapper #tab-stock input,
            html body .epi-wrapper #tab-stock select,
            html body .epi-wrapper #epi-stock-mapping input,
            html body .epi-wrapper #epi-stock-mapping select,
            #tab-stock input,
            #tab-stock select {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #8c8f94 !important;
            }
            
            /* Schedule modal specific form elements */
            html body .epi-wrapper #epi-schedule-modal input,
            html body .epi-wrapper #epi-schedule-modal select,
            html body .epi-wrapper #epi-schedule-modal textarea,
            html body #epi-schedule-modal input,
            html body #epi-schedule-modal select,
            html body #epi-schedule-modal textarea,
            #epi-schedule-modal input,
            #epi-schedule-modal select,
            #epi-schedule-modal textarea,
            #schedule-name,
            #schedule-source-type,
            #schedule-source-url,
            #schedule-ftp-host,
            #schedule-ftp-user,
            #schedule-ftp-pass,
            #schedule-ftp-path,
            #schedule-frequency,
            #schedule-time,
            #schedule-template,
            #schedule-mode {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #8c8f94 !important;
            }
            
            /* Template modal specific form elements */
            html body .epi-wrapper #epi-template-modal input,
            html body .epi-wrapper #epi-template-modal select,
            html body .epi-wrapper #epi-template-modal textarea,
            html body #epi-template-modal input,
            html body #epi-template-modal select,
            html body #epi-template-modal textarea,
            #epi-template-modal input,
            #epi-template-modal select,
            #epi-template-modal textarea,
            #template-name,
            #template-description {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #8c8f94 !important;
            }
            
            /* Export filter specific IDs */
            #export-category,
            #export-stock,
            #export-type,
            #export-price-min,
            #export-price-max {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #8c8f94 !important;
            }
            
            /* =============================================
               WRAPPER
               ============================================= */
            html body .epi-wrapper,
            .epi-wrapper {
                background: #f0f0f1 !important;
                background-color: #f0f0f1 !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            }
            
            /* =============================================
               HEADER
               ============================================= */
            html body .epi-wrapper .epi-header,
            .epi-wrapper .epi-header {
                background: #ffffff !important;
                background-color: #ffffff !important;
                border-bottom: 1px solid #c3c4c7 !important;
            }
            html body .epi-wrapper .epi-header *:not(svg):not(path):not(circle):not(line):not(polyline):not(rect),
            .epi-wrapper .epi-header *:not(svg):not(path):not(circle):not(line):not(polyline):not(rect) {
                background: transparent !important;
                background-color: transparent !important;
            }
            html body .epi-wrapper .epi-logo-text h1 { color: #1d2327 !important; }
            html body .epi-wrapper .epi-logo-text span { color: #646970 !important; }
            
            /* =============================================
               TABS
               ============================================= */
            html body .epi-wrapper .epi-tabs,
            .epi-wrapper .epi-tabs {
                background: #ffffff !important;
                background-color: #ffffff !important;
                border-bottom: 1px solid #c3c4c7 !important;
            }
            html body .epi-wrapper .epi-tabs *:not(svg):not(path):not(circle):not(line):not(polyline):not(rect),
            .epi-wrapper .epi-tabs *:not(svg):not(path):not(circle):not(line):not(polyline):not(rect) {
                background: transparent !important;
                background-color: transparent !important;
            }
            html body .epi-wrapper .epi-tab,
            .epi-wrapper .epi-tab {
                color: #50575e !important;
                text-decoration: none !important;
            }
            html body .epi-wrapper .epi-tab:hover,
            .epi-wrapper .epi-tab:hover {
                color: #135e96 !important;
                background: #f6f7f7 !important;
                background-color: #f6f7f7 !important;
            }
            html body .epi-wrapper .epi-tab.active,
            .epi-wrapper .epi-tab.active {
                color: #135e96 !important;
                border-bottom: 3px solid #135e96 !important;
            }
            
            /* =============================================
               MAIN & CARDS
               ============================================= */
            html body .epi-wrapper .epi-main,
            .epi-wrapper .epi-main {
                background: #f0f0f1 !important;
                background-color: #f0f0f1 !important;
            }
            html body .epi-wrapper .epi-card,
            .epi-wrapper .epi-card {
                background: #ffffff !important;
                background-color: #ffffff !important;
                border: 1px solid #c3c4c7 !important;
            }
            html body .epi-wrapper .epi-card *:not(input):not(select):not(textarea):not(option):not(svg):not(path):not(circle):not(line):not(polyline):not(rect),
            .epi-wrapper .epi-card *:not(input):not(select):not(textarea):not(option):not(svg):not(path):not(circle):not(line):not(polyline):not(rect) {
                background: transparent !important;
                background-color: transparent !important;
            }
            html body .epi-wrapper .epi-card-title,
            html body .epi-wrapper h2,
            html body .epi-wrapper h3 { color: #1d2327 !important; }
            html body .epi-wrapper .epi-card-desc,
            html body .epi-wrapper p { color: #646970 !important; }
            
            /* =============================================
               FORM ELEMENTS - CARD LEVEL
               ============================================= */
            html body .epi-wrapper .epi-card input,
            html body .epi-wrapper .epi-card select,
            html body .epi-wrapper .epi-card textarea,
            .epi-wrapper .epi-card input,
            .epi-wrapper .epi-card select,
            .epi-wrapper .epi-card textarea {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #8c8f94 !important;
            }
            html body .epi-wrapper label,
            .epi-wrapper label { color: #1d2327 !important; }
            
            html body .epi-wrapper .epi-form-group,
            html body .epi-wrapper .epi-form-row,
            html body .epi-wrapper .epi-export-filters,
            .epi-wrapper .epi-form-group,
            .epi-wrapper .epi-form-row,
            .epi-wrapper .epi-export-filters {
                background: transparent !important;
                background-color: transparent !important;
            }
            
            /* =============================================
               TABLES
               ============================================= */
            html body .epi-wrapper table,
            html body .epi-wrapper .epi-table,
            html body .epi-wrapper .epi-preview-table,
            html body .epi-wrapper .epi-preview-table-wrapper,
            .epi-wrapper table {
                background: #ffffff !important;
                background-color: #ffffff !important;
            }
            html body .epi-wrapper table th,
            .epi-wrapper table th {
                background: #f6f7f7 !important;
                background-color: #f6f7f7 !important;
                color: #1d2327 !important;
            }
            html body .epi-wrapper table td,
            .epi-wrapper table td {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
            }
            html body .epi-wrapper table tr:hover td {
                background: #f9f9f9 !important;
                background-color: #f9f9f9 !important;
            }
            
            /* =============================================
               UPLOAD ZONE
               ============================================= */
            html body .epi-wrapper .epi-upload-zone,
            .epi-wrapper .epi-upload-zone {
                background: #f9f9f9 !important;
                background-color: #f9f9f9 !important;
                border: 2px dashed #c3c4c7 !important;
            }
            html body .epi-wrapper .epi-upload-zone *:not(input):not(select):not(textarea):not(option):not(svg):not(path):not(circle):not(line):not(polyline):not(rect),
            .epi-wrapper .epi-upload-zone *:not(input):not(select):not(textarea):not(option):not(svg):not(path):not(circle):not(line):not(polyline):not(rect) {
                background: transparent !important;
                background-color: transparent !important;
            }
            html body .epi-wrapper .epi-upload-zone h3 { color: #1d2327 !important; }
            html body .epi-wrapper .epi-upload-zone p { color: #646970 !important; }
            html body .epi-wrapper .epi-upload-zone .epi-upload-formats {
                background: #f0f0f1 !important;
                background-color: #f0f0f1 !important;
                color: #8c8f94 !important;
            }
            
            /* =============================================
               MAPPING ITEMS
               ============================================= */
            html body .epi-wrapper .epi-mapping-item,
            html body .epi-wrapper .epi-mapping-grid > div,
            .epi-wrapper .epi-mapping-item {
                background: #f9f9f9 !important;
                background-color: #f9f9f9 !important;
                border: 1px solid #dcdcde !important;
            }
            html body .epi-wrapper .epi-mapping-item *:not(input):not(select):not(textarea):not(option),
            .epi-wrapper .epi-mapping-item *:not(input):not(select):not(textarea):not(option) {
                background: transparent !important;
                background-color: transparent !important;
                color: #1d2327 !important;
            }
            html body .epi-wrapper .epi-mapping-item select,
            html body .epi-wrapper .epi-mapping-grid select,
            .epi-wrapper .epi-mapping-item select,
            .epi-wrapper .epi-mapping-grid select {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #8c8f94 !important;
            }
            
            /* =============================================
               BUTTONS
               ============================================= */
            html body .epi-wrapper .epi-btn-primary,
            .epi-wrapper .epi-btn-primary {
                background: #135e96 !important;
                background-color: #135e96 !important;
                color: #ffffff !important;
                border-color: #135e96 !important;
            }
            html body .epi-wrapper .epi-btn-primary *:not(svg):not(path):not(circle):not(line):not(polyline):not(rect),
            .epi-wrapper .epi-btn-primary *:not(svg):not(path):not(circle):not(line):not(polyline):not(rect) {
                color: #ffffff !important;
                background: transparent !important;
            }
            html body .epi-wrapper .epi-btn-outline,
            .epi-wrapper .epi-btn-outline {
                background: #f6f7f7 !important;
                background-color: #f6f7f7 !important;
                color: #2c3338 !important;
                border-color: #8c8f94 !important;
            }
            html body .epi-wrapper .epi-btn-outline *:not(svg):not(path):not(circle):not(line):not(polyline):not(rect),
            .epi-wrapper .epi-btn-outline *:not(svg):not(path):not(circle):not(line):not(polyline):not(rect) {
                color: #2c3338 !important;
                background: transparent !important;
            }
            
            /* Button icon colors */
            html body .epi-wrapper .epi-btn-primary svg,
            .epi-wrapper .epi-btn-primary svg {
                stroke: #ffffff !important;
            }
            html body .epi-wrapper .epi-btn-outline svg,
            .epi-wrapper .epi-btn-outline svg {
                stroke: #2c3338 !important;
            }
            
            /* =============================================
               STEPS
               ============================================= */
            html body .epi-wrapper .epi-step-number,
            .epi-wrapper .epi-step-number {
                background: #dcdcde !important;
                background-color: #dcdcde !important;
                color: #50575e !important;
            }
            html body .epi-wrapper .epi-step.active .epi-step-number,
            .epi-wrapper .epi-step.active .epi-step-number {
                background: #135e96 !important;
                background-color: #135e96 !important;
                color: #ffffff !important;
            }
            html body .epi-wrapper .epi-step.completed .epi-step-number,
            .epi-wrapper .epi-step.completed .epi-step-number {
                background: #00a32a !important;
                background-color: #00a32a !important;
                color: #ffffff !important;
            }
            html body .epi-wrapper .epi-step-label { color: #50575e !important; }
            html body .epi-wrapper .epi-step-line {
                background: #dcdcde !important;
                background-color: #dcdcde !important;
            }
            
            /* =============================================
               FILE INFO
               ============================================= */
            html body .epi-wrapper .epi-file-info,
            .epi-wrapper .epi-file-info {
                background: #f0f6fc !important;
                background-color: #f0f6fc !important;
                border: 1px solid #c3c4c7 !important;
            }
            html body .epi-wrapper .epi-file-info *:not(input):not(select):not(textarea):not(option):not(svg):not(path):not(circle):not(line):not(polyline):not(rect),
            .epi-wrapper .epi-file-info *:not(input):not(select):not(textarea):not(option):not(svg):not(path):not(circle):not(line):not(polyline):not(rect) {
                background: transparent !important;
                background-color: transparent !important;
            }
            html body .epi-wrapper .epi-file-name { color: #1d2327 !important; }
            html body .epi-wrapper .epi-file-size { color: #646970 !important; }
            
            /* =============================================
               MODAL - COMPREHENSIVE FIX
               ============================================= */
            /* Modal overlay */
            html body .epi-wrapper .epi-modal,
            html body .epi-modal,
            .epi-wrapper .epi-modal,
            .epi-modal {
                background: rgba(0, 0, 0, 0.5) !important;
            }
            
            /* Modal content box */
            html body .epi-wrapper .epi-modal-content,
            html body .epi-modal .epi-modal-content,
            .epi-wrapper .epi-modal-content,
            .epi-modal-content {
                background: #ffffff !important;
                background-color: #ffffff !important;
            }
            
            /* Modal header */
            html body .epi-wrapper .epi-modal-header,
            html body .epi-modal .epi-modal-header,
            .epi-wrapper .epi-modal-header,
            .epi-modal-header {
                background: #ffffff !important;
                background-color: #ffffff !important;
                border-bottom: 1px solid #dcdcde !important;
            }
            html body .epi-wrapper .epi-modal-header *:not(input):not(select):not(textarea),
            html body .epi-modal .epi-modal-header *:not(input):not(select):not(textarea),
            .epi-modal-header *:not(input):not(select):not(textarea) {
                background: transparent !important;
                background-color: transparent !important;
            }
            html body .epi-modal-header h3,
            .epi-modal-header h3 { color: #1d2327 !important; }
            html body .epi-modal-close,
            .epi-modal-close { color: #646970 !important; background: transparent !important; }
            
            /* Modal body */
            html body .epi-wrapper .epi-modal-body,
            html body .epi-modal .epi-modal-body,
            .epi-wrapper .epi-modal-body,
            .epi-modal-body {
                background: #ffffff !important;
                background-color: #ffffff !important;
            }
            html body .epi-wrapper .epi-modal-body *:not(input):not(select):not(textarea):not(option),
            html body .epi-modal .epi-modal-body *:not(input):not(select):not(textarea):not(option),
            .epi-modal-body *:not(input):not(select):not(textarea):not(option) {
                background: transparent !important;
                background-color: transparent !important;
            }
            
            /* Modal body form elements - FORCE white background */
            html body.wp-admin .epi-modal-body input,
            html body.wp-admin .epi-modal-body select,
            html body.wp-admin .epi-modal-body textarea,
            html body .epi-modal-body input,
            html body .epi-modal-body select,
            html body .epi-modal-body textarea,
            html body .epi-modal .epi-modal-body input,
            html body .epi-modal .epi-modal-body select,
            html body .epi-modal .epi-modal-body textarea,
            .epi-modal-body input,
            .epi-modal-body select,
            .epi-modal-body textarea {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #8c8f94 !important;
            }
            html body .epi-modal-body label,
            .epi-modal-body label { color: #1d2327 !important; }
            
            /* Modal form group backgrounds */
            html body .epi-modal-body .epi-form-group,
            html body .epi-modal-body .epi-form-row,
            .epi-modal-body .epi-form-group,
            .epi-modal-body .epi-form-row {
                background: transparent !important;
                background-color: transparent !important;
            }
            
            /* FTP fields container */
            html body .epi-wrapper #ftp-fields,
            html body #ftp-fields,
            #ftp-fields {
                background: transparent !important;
                background-color: transparent !important;
            }
            
            /* Modal footer */
            html body .epi-wrapper .epi-modal-footer,
            html body .epi-modal .epi-modal-footer,
            .epi-wrapper .epi-modal-footer,
            .epi-modal-footer {
                background: #f9f9f9 !important;
                background-color: #f9f9f9 !important;
                border-top: 1px solid #dcdcde !important;
            }
            html body .epi-modal-footer *:not(input):not(select):not(textarea),
            .epi-modal-footer *:not(input):not(select):not(textarea) {
                background: transparent !important;
                background-color: transparent !important;
            }
            
            /* Modal buttons */
            html body .epi-modal-footer .epi-btn-primary,
            .epi-modal-footer .epi-btn-primary {
                background: #135e96 !important;
                background-color: #135e96 !important;
                color: #ffffff !important;
            }
            html body .epi-modal-footer .epi-btn-outline,
            .epi-modal-footer .epi-btn-outline {
                background: #f6f7f7 !important;
                background-color: #f6f7f7 !important;
                color: #2c3338 !important;
            }
            
            /* =============================================
               RESULTS & LOG
               ============================================= */
            html body .epi-wrapper .epi-import-results,
            html body .epi-wrapper .epi-import-log,
            html body .epi-wrapper .epi-history-stats,
            .epi-wrapper .epi-import-results,
            .epi-wrapper .epi-import-log {
                background: #f9f9f9 !important;
                background-color: #f9f9f9 !important;
            }
            html body .epi-wrapper .epi-import-log {
                border: 1px solid #dcdcde !important;
            }
            html body .epi-wrapper .epi-result-success strong { color: #00a32a !important; }
            html body .epi-wrapper .epi-result-warning strong { color: #dba617 !important; }
            html body .epi-wrapper .epi-result-error strong { color: #d63638 !important; }
            
            /* =============================================
               LIST ITEMS
               ============================================= */
            html body .epi-wrapper .epi-template-item,
            html body .epi-wrapper .epi-schedule-item,
            .epi-wrapper .epi-template-item,
            .epi-wrapper .epi-schedule-item {
                background: #f9f9f9 !important;
                background-color: #f9f9f9 !important;
                border: 1px solid #dcdcde !important;
            }
            html body .epi-wrapper .epi-template-item *:not(input):not(select):not(textarea):not(option),
            html body .epi-wrapper .epi-schedule-item *:not(input):not(select):not(textarea):not(option),
            .epi-wrapper .epi-template-item *:not(input):not(select):not(textarea):not(option),
            .epi-wrapper .epi-schedule-item *:not(input):not(select):not(textarea):not(option) {
                background: transparent !important;
                background-color: transparent !important;
            }
            html body .epi-wrapper .epi-template-name,
            html body .epi-wrapper .epi-schedule-name { color: #1d2327 !important; }
            html body .epi-wrapper .epi-template-meta,
            html body .epi-wrapper .epi-schedule-meta { color: #646970 !important; }
            
            /* =============================================
               EMPTY STATE
               ============================================= */
            html body .epi-wrapper .epi-empty-state,
            .epi-wrapper .epi-empty-state {
                background: transparent !important;
                background-color: transparent !important;
            }
            html body .epi-wrapper .epi-empty-state p { color: #646970 !important; }
            
            /* =============================================
               PROGRESS
               ============================================= */
            html body .epi-wrapper .epi-progress-bar,
            .epi-wrapper .epi-progress-bar {
                background: #dcdcde !important;
                background-color: #dcdcde !important;
            }
            html body .epi-wrapper .epi-progress-fill,
            .epi-wrapper .epi-progress-fill {
                background: #135e96 !important;
                background-color: #135e96 !important;
            }
            html body .epi-wrapper .epi-progress-value { color: #1d2327 !important; }
            html body .epi-wrapper .epi-stat-value { color: #135e96 !important; }
            html body .epi-wrapper .epi-stat-label { color: #646970 !important; }
            
            /* =============================================
               FOOTER
               ============================================= */
            html body .epi-wrapper .epi-footer,
            .epi-wrapper .epi-footer {
                background: transparent !important;
                background-color: transparent !important;
                border-top: 1px solid #dcdcde !important;
            }
            html body .epi-wrapper .epi-footer p { color: #646970 !important; }
            html body .epi-wrapper .epi-footer a { color: #135e96 !important; }
            
            /* =============================================
               CHECKBOX & OPTIONS
               ============================================= */
            html body .epi-wrapper .epi-checkbox,
            html body .epi-wrapper .epi-options,
            .epi-wrapper .epi-checkbox,
            .epi-wrapper .epi-options {
                background: transparent !important;
                background-color: transparent !important;
            }
            html body .epi-wrapper .epi-options {
                border-top: 1px solid #dcdcde !important;
            }
            
            /* =============================================
               PAGINATION
               ============================================= */
            html body .epi-wrapper .epi-pagination button,
            .epi-wrapper .epi-pagination button {
                background: #ffffff !important;
                background-color: #ffffff !important;
                color: #1d2327 !important;
                border: 1px solid #dcdcde !important;
            }
            html body .epi-wrapper .epi-pagination button.active,
            .epi-wrapper .epi-pagination button.active {
                background: #135e96 !important;
                background-color: #135e96 !important;
                color: #ffffff !important;
            }
        </style>
        <?php
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_excel-product-importer') {
            return;
        }
        
        // Enqueue plugin CSS
        wp_enqueue_style(
            'epi-admin-style',
            EPI_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            EPI_VERSION
        );
        
        wp_enqueue_script(
            'epi-admin-script',
            EPI_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery'),
            EPI_VERSION,
            true
        );
        
        wp_localize_script('epi-admin-script', 'epiData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('epi_nonce'),
            'strings' => array(
                'uploading' => __('Yükleniyor...', 'excel-product-importer'),
                'processing' => __('İşleniyor...', 'excel-product-importer'),
                'success' => __('Başarılı!', 'excel-product-importer'),
                'error' => __('Hata oluştu!', 'excel-product-importer'),
                'confirmImport' => __('İçe aktarma işlemini başlatmak istiyor musunuz?', 'excel-product-importer'),
                'selectFile' => __('Lütfen bir dosya seçin', 'excel-product-importer'),
                'invalidFile' => __('Geçersiz dosya formatı. Lütfen .xlsx, .xls veya .csv dosyası seçin.', 'excel-product-importer')
            )
        ));
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        include EPI_PLUGIN_DIR . 'templates/admin-page.php';
    }
}
