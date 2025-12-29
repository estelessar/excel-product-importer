<?php
/**
 * Admin page template
 */

if (!defined('ABSPATH')) {
    exit;
}

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'import';
?>

<div class="epi-wrapper">
    <!-- Header -->
    <header class="epi-header">
        <div class="epi-header-content">
            <div class="epi-logo">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                    <rect width="40" height="40" rx="8" fill="#1a1a1a"/>
                    <path d="M10 12h20v3H10zM10 18h20v3H10zM10 24h14v3H10z" fill="#fff"/>
                </svg>
                <div class="epi-logo-text">
                    <h1><?php _e('Excel Product Importer', 'excel-product-importer'); ?></h1>
                    <span><?php _e('WooCommerce için Toplu Ürün Yönetimi', 'excel-product-importer'); ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Tabs -->
    <nav class="epi-tabs">
        <a href="?page=excel-product-importer&tab=import" class="epi-tab <?php echo $active_tab === 'import' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <?php _e('İçe Aktar', 'excel-product-importer'); ?>
        </a>
        <a href="?page=excel-product-importer&tab=export" class="epi-tab <?php echo $active_tab === 'export' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <?php _e('Dışa Aktar', 'excel-product-importer'); ?>
        </a>
        <a href="?page=excel-product-importer&tab=stock" class="epi-tab <?php echo $active_tab === 'stock' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>
            </svg>
            <?php _e('Stok/Fiyat', 'excel-product-importer'); ?>
        </a>
        <a href="?page=excel-product-importer&tab=templates" class="epi-tab <?php echo $active_tab === 'templates' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
            <?php _e('Şablonlar', 'excel-product-importer'); ?>
        </a>
        <a href="?page=excel-product-importer&tab=history" class="epi-tab <?php echo $active_tab === 'history' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
            </svg>
            <?php _e('Geçmiş', 'excel-product-importer'); ?>
        </a>
        <a href="?page=excel-product-importer&tab=schedule" class="epi-tab <?php echo $active_tab === 'schedule' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <?php _e('Zamanlama', 'excel-product-importer'); ?>
        </a>
    </nav>

    <!-- Main Content -->
    <main class="epi-main">
        
        <?php if ($active_tab === 'import'): ?>
        <!-- IMPORT TAB -->
        <div class="epi-tab-content" id="tab-import">
            <!-- Steps Indicator -->
            <div class="epi-steps">
                <div class="epi-step active" data-step="1">
                    <div class="epi-step-number">1</div>
                    <div class="epi-step-label"><?php _e('Dosya Yükle', 'excel-product-importer'); ?></div>
                </div>
                <div class="epi-step-line"></div>
                <div class="epi-step" data-step="2">
                    <div class="epi-step-number">2</div>
                    <div class="epi-step-label"><?php _e('Eşleştir', 'excel-product-importer'); ?></div>
                </div>
                <div class="epi-step-line"></div>
                <div class="epi-step" data-step="3">
                    <div class="epi-step-number">3</div>
                    <div class="epi-step-label"><?php _e('Önizleme', 'excel-product-importer'); ?></div>
                </div>
                <div class="epi-step-line"></div>
                <div class="epi-step" data-step="4">
                    <div class="epi-step-number">4</div>
                    <div class="epi-step-label"><?php _e('İçe Aktar', 'excel-product-importer'); ?></div>
                </div>
            </div>

            <!-- Step 1: Upload -->
            <section class="epi-section epi-section-upload active" data-section="1">
                <div class="epi-card">
                    <div class="epi-card-header">
                        <h2 class="epi-card-title"><?php _e('Dosya Yükle', 'excel-product-importer'); ?></h2>
                        <div class="epi-dropdown">
                            <button type="button" class="epi-btn epi-btn-outline epi-btn-sm epi-dropdown-toggle" id="epi-template-dropdown-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                <?php _e('Şablon İndir', 'excel-product-importer'); ?>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 12,15 18,9"/></svg>
                            </button>
                            <div class="epi-dropdown-menu" id="epi-template-dropdown">
                                <a href="#" class="epi-dropdown-item" data-format="custom">
                                    <strong><?php _e('Özel Şablon', 'excel-product-importer'); ?></strong>
                                    <small><?php _e('Basit format, noktalı virgül ayraçlı', 'excel-product-importer'); ?></small>
                                </a>
                                <a href="#" class="epi-dropdown-item" data-format="woocommerce_tr">
                                    <strong><?php _e('WooCommerce Türkçe', 'excel-product-importer'); ?></strong>
                                    <small><?php _e('WooCommerce export formatı (TR)', 'excel-product-importer'); ?></small>
                                </a>
                                <a href="#" class="epi-dropdown-item" data-format="woocommerce_en">
                                    <strong><?php _e('WooCommerce English', 'excel-product-importer'); ?></strong>
                                    <small><?php _e('WooCommerce export format (EN)', 'excel-product-importer'); ?></small>
                                </a>
                                <a href="#" class="epi-dropdown-item" data-format="stock_only">
                                    <strong><?php _e('Stok Güncelleme', 'excel-product-importer'); ?></strong>
                                    <small><?php _e('Sadece SKU, stok ve fiyat', 'excel-product-importer'); ?></small>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="epi-upload-zone" id="epi-upload-zone">
                        <div class="epi-upload-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9,15 12,12 15,15"/>
                            </svg>
                        </div>
                        <h3><?php _e('Excel Dosyasını Sürükle & Bırak', 'excel-product-importer'); ?></h3>
                        <p><?php _e('veya dosya seçmek için tıklayın', 'excel-product-importer'); ?></p>
                        <span class="epi-upload-formats">.xlsx, .xls, .csv</span>
                        <input type="file" id="epi-file-input" accept=".xlsx,.xls,.csv" hidden>
                    </div>
                    
                    <div class="epi-upload-progress" id="epi-upload-progress" style="display: none;">
                        <div class="epi-progress-bar"><div class="epi-progress-fill"></div></div>
                        <span class="epi-progress-text"><?php _e('Yükleniyor...', 'excel-product-importer'); ?></span>
                    </div>
                    
                    <div class="epi-file-info" id="epi-file-info" style="display: none;">
                        <div class="epi-file-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/>
                            </svg>
                        </div>
                        <div class="epi-file-details">
                            <span class="epi-file-name"></span>
                            <span class="epi-file-size"></span>
                        </div>
                        <button type="button" class="epi-btn-icon" id="epi-remove-file">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="epi-actions">
                    <button type="button" class="epi-btn epi-btn-primary" id="epi-next-step-1" disabled>
                        <?php _e('Devam Et', 'excel-product-importer'); ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9,18 15,12 9,6"/></svg>
                    </button>
                </div>
            </section>

            <!-- Step 2: Mapping -->
            <section class="epi-section epi-section-mapping" data-section="2" style="display: none;">
                <div class="epi-card">
                    <div class="epi-card-header">
                        <h2 class="epi-card-title"><?php _e('Sütun Eşleştirme', 'excel-product-importer'); ?></h2>
                        <div class="epi-template-actions">
                            <select id="epi-load-template" class="epi-select">
                                <option value=""><?php _e('Şablon Seç...', 'excel-product-importer'); ?></option>
                            </select>
                            <button type="button" class="epi-btn epi-btn-outline epi-btn-sm" id="epi-save-template-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/>
                                </svg>
                                <?php _e('Kaydet', 'excel-product-importer'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="epi-mapping-grid" id="epi-mapping-grid"></div>
                    <div class="epi-options">
                        <h3><?php _e('Seçenekler', 'excel-product-importer'); ?></h3>
                        <label class="epi-checkbox">
                            <input type="checkbox" id="epi-skip-existing" checked>
                            <span class="epi-checkbox-mark"></span>
                            <?php _e('Mevcut ürünleri atla (SKU bazlı)', 'excel-product-importer'); ?>
                        </label>
                        <label class="epi-checkbox">
                            <input type="checkbox" id="epi-update-existing">
                            <span class="epi-checkbox-mark"></span>
                            <?php _e('Mevcut ürünleri güncelle', 'excel-product-importer'); ?>
                        </label>
                    </div>
                </div>
                <div class="epi-actions">
                    <button type="button" class="epi-btn epi-btn-outline" id="epi-prev-step-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15,18 9,12 15,6"/></svg>
                        <?php _e('Geri', 'excel-product-importer'); ?>
                    </button>
                    <button type="button" class="epi-btn epi-btn-primary" id="epi-next-step-2">
                        <?php _e('Önizleme', 'excel-product-importer'); ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9,18 15,12 9,6"/></svg>
                    </button>
                </div>
            </section>

            <!-- Step 3: Preview -->
            <section class="epi-section epi-section-preview" data-section="3" style="display: none;">
                <div class="epi-card">
                    <h2 class="epi-card-title"><?php _e('Önizleme', 'excel-product-importer'); ?></h2>
                    <div class="epi-preview-stats">
                        <div class="epi-stat">
                            <span class="epi-stat-value" id="epi-total-rows">0</span>
                            <span class="epi-stat-label"><?php _e('Toplam Satır', 'excel-product-importer'); ?></span>
                        </div>
                        <div class="epi-stat">
                            <span class="epi-stat-value" id="epi-mapped-cols">0</span>
                            <span class="epi-stat-label"><?php _e('Eşleşen Sütun', 'excel-product-importer'); ?></span>
                        </div>
                    </div>
                    <div class="epi-preview-table-wrapper">
                        <table class="epi-preview-table" id="epi-preview-table"><thead></thead><tbody></tbody></table>
                    </div>
                </div>
                <div class="epi-actions">
                    <button type="button" class="epi-btn epi-btn-outline" id="epi-prev-step-3">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15,18 9,12 15,6"/></svg>
                        <?php _e('Geri', 'excel-product-importer'); ?>
                    </button>
                    <button type="button" class="epi-btn epi-btn-primary" id="epi-start-import">
                        <?php _e('İçe Aktarmayı Başlat', 'excel-product-importer'); ?>
                    </button>
                </div>
            </section>

            <!-- Step 4: Import Progress -->
            <section class="epi-section epi-section-import" data-section="4" style="display: none;">
                <div class="epi-card">
                    <h2 class="epi-card-title"><?php _e('İçe Aktarma', 'excel-product-importer'); ?></h2>
                    <div class="epi-import-progress">
                        <div class="epi-progress-circle" id="epi-progress-circle">
                            <svg viewBox="0 0 100 100">
                                <circle class="epi-progress-bg" cx="50" cy="50" r="45"/>
                                <circle class="epi-progress-bar" cx="50" cy="50" r="45"/>
                            </svg>
                            <div class="epi-progress-value"><span id="epi-progress-percent">0</span>%</div>
                        </div>
                        <div class="epi-progress-info">
                            <span id="epi-progress-status"><?php _e('Hazırlanıyor...', 'excel-product-importer'); ?></span>
                            <span id="epi-progress-count">0 / 0</span>
                        </div>
                    </div>
                    <div class="epi-import-results">
                        <div class="epi-result-item epi-result-success">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12"/></svg>
                            <span><?php _e('Başarılı:', 'excel-product-importer'); ?></span>
                            <strong id="epi-success-count">0</strong>
                        </div>
                        <div class="epi-result-item epi-result-warning">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                            <span><?php _e('Atlanan:', 'excel-product-importer'); ?></span>
                            <strong id="epi-skipped-count">0</strong>
                        </div>
                        <div class="epi-result-item epi-result-error">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            <span><?php _e('Hata:', 'excel-product-importer'); ?></span>
                            <strong id="epi-error-count">0</strong>
                        </div>
                    </div>
                    <div class="epi-import-log" id="epi-import-log"></div>
                </div>
                <div class="epi-actions" id="epi-import-actions" style="display: none;">
                    <button type="button" class="epi-btn epi-btn-outline" id="epi-new-import"><?php _e('Yeni İçe Aktarma', 'excel-product-importer'); ?></button>
                    <a href="<?php echo admin_url('edit.php?post_type=product'); ?>" class="epi-btn epi-btn-primary"><?php _e('Ürünleri Görüntüle', 'excel-product-importer'); ?></a>
                </div>
            </section>
        </div>
        <?php endif; ?>

        <?php if ($active_tab === 'export'): ?>
        <!-- EXPORT TAB -->
        <div class="epi-tab-content" id="tab-export">
            <div class="epi-card">
                <h2 class="epi-card-title"><?php _e('Ürünleri Dışa Aktar', 'excel-product-importer'); ?></h2>
                <p class="epi-card-desc"><?php _e('WooCommerce ürünlerinizi Excel formatında indirin.', 'excel-product-importer'); ?></p>
                
                <div class="epi-export-filters">
                    <div class="epi-form-row">
                        <div class="epi-form-group">
                            <label><?php _e('Kategori', 'excel-product-importer'); ?></label>
                            <select id="export-category" class="epi-select">
                                <option value=""><?php _e('Tümü', 'excel-product-importer'); ?></option>
                                <?php
                                $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
                                foreach ($categories as $cat) {
                                    echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="epi-form-group">
                            <label><?php _e('Stok Durumu', 'excel-product-importer'); ?></label>
                            <select id="export-stock" class="epi-select">
                                <option value=""><?php _e('Tümü', 'excel-product-importer'); ?></option>
                                <option value="instock"><?php _e('Stokta', 'excel-product-importer'); ?></option>
                                <option value="outofstock"><?php _e('Stok Dışı', 'excel-product-importer'); ?></option>
                            </select>
                        </div>
                        <div class="epi-form-group">
                            <label><?php _e('Ürün Tipi', 'excel-product-importer'); ?></label>
                            <select id="export-type" class="epi-select">
                                <option value=""><?php _e('Tümü', 'excel-product-importer'); ?></option>
                                <option value="simple"><?php _e('Basit', 'excel-product-importer'); ?></option>
                                <option value="variable"><?php _e('Varyasyonlu', 'excel-product-importer'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="epi-form-row">
                        <div class="epi-form-group">
                            <label><?php _e('Min Fiyat', 'excel-product-importer'); ?></label>
                            <input type="number" id="export-price-min" class="epi-input" placeholder="0">
                        </div>
                        <div class="epi-form-group">
                            <label><?php _e('Max Fiyat', 'excel-product-importer'); ?></label>
                            <input type="number" id="export-price-max" class="epi-input" placeholder="9999">
                        </div>
                    </div>
                </div>
                
                <div class="epi-actions">
                    <button type="button" class="epi-btn epi-btn-primary" id="epi-export-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        <?php _e('CSV İndir', 'excel-product-importer'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($active_tab === 'stock'): ?>
        <!-- STOCK UPDATE TAB -->
        <div class="epi-tab-content" id="tab-stock">
            <div class="epi-card">
                <h2 class="epi-card-title"><?php _e('Hızlı Stok/Fiyat Güncelleme', 'excel-product-importer'); ?></h2>
                <p class="epi-card-desc"><?php _e('Sadece SKU, stok ve fiyat bilgilerini içeren dosya ile hızlı güncelleme yapın.', 'excel-product-importer'); ?></p>
                
                <div class="epi-upload-zone" id="epi-stock-upload-zone">
                    <div class="epi-upload-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>
                        </svg>
                    </div>
                    <h3><?php _e('Stok/Fiyat Dosyası Yükle', 'excel-product-importer'); ?></h3>
                    <p><?php _e('SKU, regular_price, sale_price, stock_quantity sütunları yeterli', 'excel-product-importer'); ?></p>
                    <input type="file" id="epi-stock-file-input" accept=".xlsx,.xls,.csv" hidden>
                </div>
                
                <div id="epi-stock-mapping" style="display:none;">
                    <h3><?php _e('Sütun Eşleştirme', 'excel-product-importer'); ?></h3>
                    <div class="epi-mapping-grid" id="epi-stock-mapping-grid"></div>
                    <div class="epi-actions">
                        <button type="button" class="epi-btn epi-btn-primary" id="epi-start-stock-update">
                            <?php _e('Güncellemeyi Başlat', 'excel-product-importer'); ?>
                        </button>
                    </div>
                </div>
                
                <div id="epi-stock-progress" style="display:none;">
                    <div class="epi-import-progress">
                        <div class="epi-progress-bar"><div class="epi-progress-fill" id="stock-progress-fill"></div></div>
                        <span id="stock-progress-text">0%</span>
                    </div>
                    <div class="epi-import-log" id="epi-stock-log"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($active_tab === 'templates'): ?>
        <!-- TEMPLATES TAB -->
        <div class="epi-tab-content" id="tab-templates">
            <div class="epi-card">
                <h2 class="epi-card-title"><?php _e('Kayıtlı Şablonlar', 'excel-product-importer'); ?></h2>
                <p class="epi-card-desc"><?php _e('Farklı tedarikçiler için eşleştirme şablonları oluşturun ve yönetin.', 'excel-product-importer'); ?></p>
                
                <div class="epi-templates-list" id="epi-templates-list">
                    <div class="epi-empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>
                        </svg>
                        <p><?php _e('Henüz şablon yok. İçe aktarma sırasında şablon kaydedebilirsiniz.', 'excel-product-importer'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($active_tab === 'history'): ?>
        <!-- HISTORY TAB -->
        <div class="epi-tab-content" id="tab-history">
            <div class="epi-card">
                <h2 class="epi-card-title"><?php _e('İçe Aktarma Geçmişi', 'excel-product-importer'); ?></h2>
                
                <div class="epi-history-stats" id="epi-history-stats"></div>
                
                <table class="epi-table" id="epi-history-table">
                    <thead>
                        <tr>
                            <th><?php _e('Tarih', 'excel-product-importer'); ?></th>
                            <th><?php _e('Dosya', 'excel-product-importer'); ?></th>
                            <th><?php _e('Başarılı', 'excel-product-importer'); ?></th>
                            <th><?php _e('Hata', 'excel-product-importer'); ?></th>
                            <th><?php _e('Durum', 'excel-product-importer'); ?></th>
                            <th><?php _e('İşlem', 'excel-product-importer'); ?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                
                <div class="epi-pagination" id="epi-history-pagination"></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($active_tab === 'schedule'): ?>
        <!-- SCHEDULE TAB -->
        <div class="epi-tab-content" id="tab-schedule">
            <div class="epi-card">
                <div class="epi-card-header">
                    <h2 class="epi-card-title"><?php _e('Zamanlanmış İçe Aktarmalar', 'excel-product-importer'); ?></h2>
                    <button type="button" class="epi-btn epi-btn-primary epi-btn-sm" id="epi-add-schedule">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <?php _e('Yeni Zamanlama', 'excel-product-importer'); ?>
                    </button>
                </div>
                
                <div class="epi-schedules-list" id="epi-schedules-list">
                    <div class="epi-empty-state">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <p><?php _e('Henüz zamanlama yok.', 'excel-product-importer'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Schedule Form Modal -->
            <div class="epi-modal" id="epi-schedule-modal" style="display:none;">
                <div class="epi-modal-content">
                    <div class="epi-modal-header">
                        <h3><?php _e('Zamanlama Ayarları', 'excel-product-importer'); ?></h3>
                        <button type="button" class="epi-modal-close">&times;</button>
                    </div>
                    <div class="epi-modal-body">
                        <input type="hidden" id="schedule-id">
                        <div class="epi-form-group">
                            <label><?php _e('Zamanlama Adı', 'excel-product-importer'); ?></label>
                            <input type="text" id="schedule-name" class="epi-input" placeholder="Tedarikçi A Günlük">
                        </div>
                        <div class="epi-form-group">
                            <label><?php _e('Kaynak Tipi', 'excel-product-importer'); ?></label>
                            <select id="schedule-source-type" class="epi-select">
                                <option value="url">URL</option>
                                <option value="ftp">FTP</option>
                                <option value="google_sheets">Google Sheets</option>
                            </select>
                        </div>
                        <div class="epi-form-group" id="source-url-group">
                            <label><?php _e('Dosya URL', 'excel-product-importer'); ?></label>
                            <input type="url" id="schedule-source-url" class="epi-input" placeholder="https://...">
                        </div>
                        <div id="ftp-fields" style="display:none;">
                            <div class="epi-form-row">
                                <div class="epi-form-group">
                                    <label><?php _e('FTP Host', 'excel-product-importer'); ?></label>
                                    <input type="text" id="schedule-ftp-host" class="epi-input">
                                </div>
                                <div class="epi-form-group">
                                    <label><?php _e('FTP Kullanıcı', 'excel-product-importer'); ?></label>
                                    <input type="text" id="schedule-ftp-user" class="epi-input">
                                </div>
                            </div>
                            <div class="epi-form-row">
                                <div class="epi-form-group">
                                    <label><?php _e('FTP Şifre', 'excel-product-importer'); ?></label>
                                    <input type="password" id="schedule-ftp-pass" class="epi-input">
                                </div>
                                <div class="epi-form-group">
                                    <label><?php _e('Dosya Yolu', 'excel-product-importer'); ?></label>
                                    <input type="text" id="schedule-ftp-path" class="epi-input" placeholder="/exports/products.csv">
                                </div>
                            </div>
                        </div>
                        <div class="epi-form-row">
                            <div class="epi-form-group">
                                <label><?php _e('Sıklık', 'excel-product-importer'); ?></label>
                                <select id="schedule-frequency" class="epi-select">
                                    <option value="hourly"><?php _e('Saatlik', 'excel-product-importer'); ?></option>
                                    <option value="daily"><?php _e('Günlük', 'excel-product-importer'); ?></option>
                                    <option value="weekly"><?php _e('Haftalık', 'excel-product-importer'); ?></option>
                                </select>
                            </div>
                            <div class="epi-form-group">
                                <label><?php _e('Saat', 'excel-product-importer'); ?></label>
                                <input type="time" id="schedule-time" class="epi-input" value="03:00">
                            </div>
                        </div>
                        <div class="epi-form-group">
                            <label><?php _e('Eşleştirme Şablonu', 'excel-product-importer'); ?></label>
                            <select id="schedule-template" class="epi-select">
                                <option value=""><?php _e('Şablon Seç...', 'excel-product-importer'); ?></option>
                            </select>
                        </div>
                        <div class="epi-form-group">
                            <label><?php _e('Mod', 'excel-product-importer'); ?></label>
                            <select id="schedule-mode" class="epi-select">
                                <option value="full"><?php _e('Tam İçe Aktarma', 'excel-product-importer'); ?></option>
                                <option value="stock_only"><?php _e('Sadece Stok/Fiyat', 'excel-product-importer'); ?></option>
                            </select>
                        </div>
                        <label class="epi-checkbox">
                            <input type="checkbox" id="schedule-enabled" checked>
                            <span class="epi-checkbox-mark"></span>
                            <?php _e('Aktif', 'excel-product-importer'); ?>
                        </label>
                    </div>
                    <div class="epi-modal-footer">
                        <button type="button" class="epi-btn epi-btn-outline epi-modal-cancel"><?php _e('İptal', 'excel-product-importer'); ?></button>
                        <button type="button" class="epi-btn epi-btn-primary" id="epi-save-schedule"><?php _e('Kaydet', 'excel-product-importer'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="epi-footer">
        <p><?php _e('Geliştirici:', 'excel-product-importer'); ?> <a href="https://www.adnbilisim.com.tr" target="_blank">ADN Bilişim Teknolojileri LTD. ŞTİ.</a></p>
        <p class="epi-version">v<?php echo EPI_VERSION; ?></p>
    </footer>

    <!-- Save Template Modal - Inside wrapper for CSS targeting -->
    <div class="epi-modal" id="epi-template-modal" style="display:none;">
        <div class="epi-modal-content">
            <div class="epi-modal-header">
                <h3><?php _e('Şablonu Kaydet', 'excel-product-importer'); ?></h3>
                <button type="button" class="epi-modal-close">&times;</button>
            </div>
            <div class="epi-modal-body">
                <div class="epi-form-group">
                    <label><?php _e('Şablon Adı', 'excel-product-importer'); ?></label>
                    <input type="text" id="template-name" class="epi-input" placeholder="Tedarikçi A Şablonu">
                </div>
                <div class="epi-form-group">
                    <label><?php _e('Açıklama', 'excel-product-importer'); ?></label>
                    <textarea id="template-description" class="epi-textarea" rows="3"></textarea>
                </div>
            </div>
            <div class="epi-modal-footer">
                <button type="button" class="epi-btn epi-btn-outline epi-modal-cancel"><?php _e('İptal', 'excel-product-importer'); ?></button>
                <button type="button" class="epi-btn epi-btn-primary" id="epi-confirm-save-template"><?php _e('Kaydet', 'excel-product-importer'); ?></button>
            </div>
        </div>
    </div>
</div>
