/**
 * Excel Product Importer - Admin Script
 * 
 * @package     Excel_Product_Importer
 * @author      ADN Bilişim Teknolojileri LTD. ŞTİ.
 * @copyright   2024 ADN Bilişim Teknolojileri LTD. ŞTİ.
 * @license     Proprietary - See LICENSE file
 * @link        https://www.adnbilisim.com.tr
 * 
 * NOTICE: Bu dosyanın izinsiz kopyalanması, dağıtılması veya satılması yasaktır.
 */

(function($) {
    'use strict';

    var EPI = {
        currentStep: 1,
        fileData: null,
        mapping: {},
        options: {},
        templates: [],

        init: function() {
            var self = this;
            console.log('EPI: Initializing...');
            self.bindEvents();
            self.initUploadZone();
            self.loadTemplates();
            self.initTabSpecific();
            console.log('EPI: Initialized successfully');
        },

        bindEvents: function() {
            var self = this;
            
            // File upload
            $(document).on('change', '#epi-file-input', function(e) {
                self.handleFileSelect(e);
            });
            
            $(document).on('click', '#epi-remove-file', function() {
                self.removeFile();
            });
            
            // Navigation
            $(document).on('click', '#epi-next-step-1', function() { self.goToStep(2); });
            $(document).on('click', '#epi-prev-step-2', function() { self.goToStep(1); });
            $(document).on('click', '#epi-next-step-2', function() { self.goToStep(3); });
            $(document).on('click', '#epi-prev-step-3', function() { self.goToStep(2); });
            $(document).on('click', '#epi-start-import', function() { self.startImport(); });
            $(document).on('click', '#epi-new-import', function() { self.resetImport(); });
            
            // Download template dropdown
            $(document).on('click', '#epi-template-dropdown-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#epi-template-dropdown').toggleClass('show');
            });
            
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.epi-dropdown').length) {
                    $('#epi-template-dropdown').removeClass('show');
                }
            });
            
            $(document).on('click', '.epi-dropdown-item[data-format]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var format = $(this).data('format');
                self.downloadTemplate(format);
                $('#epi-template-dropdown').removeClass('show');
            });
            
            // Export
            $(document).on('click', '#epi-export-btn', function() { self.exportProducts(); });
            
            // Templates
            $(document).on('click', '#epi-save-template-btn', function() { $('#epi-template-modal').show(); });
            $(document).on('click', '#epi-confirm-save-template', function() { self.saveTemplate(); });
            $(document).on('change', '#epi-load-template', function() { self.loadTemplate(); });
            
            // Modals
            $(document).on('click', '.epi-modal-close, .epi-modal-cancel', function() {
                $(this).closest('.epi-modal').hide();
            });
            
            // Options
            $(document).on('change', '#epi-skip-existing, #epi-update-existing', function() {
                if ($(this).is(':checked')) {
                    var other = $(this).attr('id') === 'epi-skip-existing' ? '#epi-update-existing' : '#epi-skip-existing';
                    $(other).prop('checked', false);
                }
            });
        },

        initTabSpecific: function() {
            var urlParams = new URLSearchParams(window.location.search);
            var tab = urlParams.get('tab') || 'import';
            
            if (tab === 'history') this.loadHistory(1);
            if (tab === 'templates') this.loadTemplatesList();
            if (tab === 'schedule') this.initSchedule();
            if (tab === 'stock') this.initStockUpdate();
        },

        initUploadZone: function() {
            var self = this;
            var $zone = $('#epi-upload-zone');
            var $input = $('#epi-file-input');
            
            if (!$zone.length) return;

            $zone.on('click', function(e) {
                e.preventDefault();
                $input.trigger('click');
            });
            
            $zone.on('dragover dragenter', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            
            $zone.on('dragleave dragend', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            
            $zone.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                var files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    $input[0].files = files;
                    self.handleFileSelect({ target: $input[0] });
                }
            });
        },

        handleFileSelect: function(e) {
            var file = e.target.files[0];
            if (!file) return;
            var ext = file.name.split('.').pop().toLowerCase();
            if (['xlsx', 'xls', 'csv'].indexOf(ext) === -1) {
                alert('Geçersiz dosya formatı. Sadece .xlsx, .xls ve .csv dosyaları kabul edilir.');
                return;
            }
            this.uploadFile(file);
        },

        uploadFile: function(file) {
            var self = this;
            var formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'epi_upload_file');
            formData.append('nonce', epiData.nonce);

            $('#epi-upload-zone').hide();
            $('#epi-upload-progress').show();
            $('.epi-progress-fill').css('width', '50%');

            $.ajax({
                url: epiData.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('.epi-progress-fill').css('width', '100%');
                    if (response.success) {
                        setTimeout(function() {
                            $('#epi-upload-progress').hide();
                            self.showFileInfo(file);
                            self.parseFile();
                        }, 300);
                    } else {
                        alert(response.data.message);
                        self.resetUploadZone();
                    }
                },
                error: function() {
                    alert('Bir hata oluştu');
                    self.resetUploadZone();
                }
            });
        },

        parseFile: function() {
            var self = this;
            $.post(epiData.ajaxUrl, { action: 'epi_parse_file', nonce: epiData.nonce }, function(response) {
                if (response.success) {
                    self.fileData = response.data;
                    self.buildMappingUI();
                    $('#epi-next-step-1').prop('disabled', false);
                } else {
                    alert(response.data.message);
                }
            });
        },

        showFileInfo: function(file) {
            var size = (file.size / 1024).toFixed(1) + ' KB';
            $('.epi-file-name').text(file.name);
            $('.epi-file-size').text(size);
            $('#epi-file-info').show();
        },

        removeFile: function() {
            this.fileData = null;
            this.mapping = {};
            $('#epi-file-input').val('');
            $('#epi-file-info').hide();
            $('#epi-next-step-1').prop('disabled', true);
            this.resetUploadZone();
        },

        resetUploadZone: function() {
            $('#epi-upload-progress').hide();
            $('.epi-progress-fill').css('width', '0%');
            $('#epi-upload-zone').show();
        },

        buildMappingUI: function() {
            var self = this;
            if (!this.fileData || !this.fileData.headers) return;
            var grid = $('#epi-mapping-grid');
            grid.empty();
            var wooFields = this.getWooCommerceFields();

            this.fileData.headers.forEach(function(header) {
                var row = $('<div class="epi-mapping-row"></div>');
                var colName = $('<div class="epi-mapping-col"></div>').text(header);
                var arrow = $('<div class="epi-mapping-arrow">→</div>');
                var select = $('<select class="epi-mapping-select"></select>').attr('data-column', header);
                
                select.append('<option value="">-- Eşleştirme Yok --</option>');
                wooFields.forEach(function(field) {
                    var option = $('<option></option>').val(field.value).text(field.label);
                    if (self.autoMatch(header, field.value)) {
                        option.prop('selected', true);
                        self.mapping[header] = field.value;
                    }
                    select.append(option);
                });

                select.on('change', function() {
                    var col = $(this).data('column');
                    var val = $(this).val();
                    if (val) self.mapping[col] = val;
                    else delete self.mapping[col];
                });

                row.append(colName, arrow, select);
                grid.append(row);
            });
        },

        getWooCommerceFields: function() {
            return [
                { value: 'product_name', label: 'Ürün Adı *' },
                { value: 'sku', label: 'SKU *' },
                { value: 'description', label: 'Açıklama' },
                { value: 'short_description', label: 'Kısa Açıklama' },
                { value: 'regular_price', label: 'Normal Fiyat *' },
                { value: 'sale_price', label: 'İndirimli Fiyat' },
                { value: 'stock_quantity', label: 'Stok Miktarı' },
                { value: 'stock_status', label: 'Stok Durumu' },
                { value: 'category', label: 'Kategori' },
                { value: 'tags', label: 'Etiketler' },
                { value: 'image_url', label: 'Görsel URL' },
                { value: 'gallery_urls', label: 'Galeri URL' },
                { value: 'weight', label: 'Ağırlık' },
                { value: 'length', label: 'Uzunluk' },
                { value: 'width', label: 'Genişlik' },
                { value: 'height', label: 'Yükseklik' },
                { value: 'product_type', label: 'Ürün Tipi' },
                { value: 'parent_sku', label: 'Parent SKU' },
                { value: 'attribute_1_name', label: 'Öznitelik 1 Adı' },
                { value: 'attribute_1_values', label: 'Öznitelik 1 Değerleri' },
                { value: 'attribute_2_name', label: 'Öznitelik 2 Adı' },
                { value: 'attribute_2_values', label: 'Öznitelik 2 Değerleri' },
                { value: 'attribute_3_name', label: 'Öznitelik 3 Adı' },
                { value: 'attribute_3_values', label: 'Öznitelik 3 Değerleri' }
            ];
        },

        autoMatch: function(header, field) {
            var h = header.toLowerCase().replace(/[_\-\s\(\)]/g, '');
            var matches = {
                'productname': 'product_name', 'urunadi': 'product_name', 'ürünadı': 'product_name', 
                'name': 'product_name', 'isim': 'product_name', 'İsim': 'product_name',
                'sku': 'sku', 'stokkodu': 'sku', 'stokkodusku': 'sku',
                'description': 'description', 'aciklama': 'description', 'açıklama': 'description',
                'shortdescription': 'short_description', 'kisaaciklama': 'short_description', 'kısaaçıklama': 'short_description',
                'regularprice': 'regular_price', 'price': 'regular_price', 'fiyat': 'regular_price', 'normalfiyat': 'regular_price',
                'saleprice': 'sale_price', 'indirimlifyat': 'sale_price', 'indirimlisatisfiyati': 'sale_price',
                'stock': 'stock_quantity', 'stockquantity': 'stock_quantity', 'stok': 'stock_quantity', 'stokmiktari': 'stock_quantity',
                'category': 'category', 'kategori': 'category', 'kategoriler': 'category',
                'tags': 'tags', 'etiketler': 'tags',
                'image': 'image_url', 'imageurl': 'image_url', 'gorsel': 'image_url', 'görseller': 'image_url',
                'weight': 'weight', 'agirlik': 'weight', 'ağırlıkkg': 'weight',
                'length': 'length', 'uzunluk': 'length', 'uzunlukcm': 'length',
                'width': 'width', 'genislik': 'width', 'genişlikcm': 'width',
                'height': 'height', 'yukseklik': 'height', 'yükseklikcm': 'height',
                'producttype': 'product_type', 'type': 'product_type', 'tür': 'product_type',
                'parentsku': 'parent_sku', 'ebeveyn': 'parent_sku',
                'nitelik1ismi': 'attribute_1_name', 'attribute1name': 'attribute_1_name',
                'nitelik1değerleri': 'attribute_1_values', 'attribute1values': 'attribute_1_values',
                'nitelik2ismi': 'attribute_2_name', 'attribute2name': 'attribute_2_name',
                'nitelik2değerleri': 'attribute_2_values', 'attribute2values': 'attribute_2_values'
            };
            return matches[h] === field;
        },

        goToStep: function(step) {
            if (step > this.currentStep && this.currentStep === 2) {
                this.collectOptions();
                this.buildPreview();
            }
            $('.epi-step').each(function() {
                var s = $(this).data('step');
                $(this).removeClass('active completed');
                if (s < step) $(this).addClass('completed');
                else if (s === step) $(this).addClass('active');
            });
            $('.epi-section').removeClass('active').hide();
            $('.epi-section[data-section="' + step + '"]').addClass('active').show();
            this.currentStep = step;
        },

        collectOptions: function() {
            this.options = {
                skip_existing: $('#epi-skip-existing').is(':checked'),
                update_existing: $('#epi-update-existing').is(':checked')
            };
        },

        buildPreview: function() {
            var self = this;
            if (!this.fileData) return;
            $('#epi-total-rows').text(this.fileData.total);
            $('#epi-mapped-cols').text(Object.keys(this.mapping).length);

            var thead = $('#epi-preview-table thead').empty();
            var tbody = $('#epi-preview-table tbody').empty();
            var headerRow = $('<tr></tr>');
            var mappedHeaders = [];
            
            for (var key in this.mapping) {
                mappedHeaders.push([key, this.mapping[key]]);
            }
            
            mappedHeaders.forEach(function(item) {
                headerRow.append($('<th></th>').text(item[0]));
            });
            thead.append(headerRow);

            if (this.fileData.preview) {
                this.fileData.preview.forEach(function(row) {
                    var tr = $('<tr></tr>');
                    mappedHeaders.forEach(function(item) {
                        var val = row[item[0]] || '';
                        tr.append($('<td></td>').text(val.substring(0, 40)));
                    });
                    tbody.append(tr);
                });
            }
        },

        startImport: function() {
            if (!confirm('İçe aktarmayı başlatmak istediğinize emin misiniz?')) return;
            this.goToStep(4);
            this.runImport(0);
        },

        runImport: function(batch) {
            var self = this;
            $.post(epiData.ajaxUrl, {
                action: 'epi_import_products',
                nonce: epiData.nonce,
                mapping: JSON.stringify(this.mapping),
                options: JSON.stringify(this.options),
                batch: batch
            }, function(response) {
                if (response.success) {
                    var data = response.data;
                    self.updateProgress(data.progress, data.processed, data.total);
                    self.updateCounts(data.results);
                    self.addLogMessages(data.results.messages);
                    if (data.has_more) {
                        setTimeout(function() { self.runImport(data.next_batch); }, 100);
                    } else {
                        self.importComplete();
                    }
                }
            });
        },

        updateProgress: function(percent, processed, total) {
            $('#epi-progress-percent').text(percent);
            $('#epi-progress-count').text(processed + ' / ' + total);
            $('#epi-progress-status').text('İşleniyor...');
            var offset = 283 - (percent / 100) * 283;
            $('.epi-progress-circle .epi-progress-bar').css('stroke-dashoffset', offset);
        },

        updateCounts: function(results) {
            var s = parseInt($('#epi-success-count').text()) || 0;
            var sk = parseInt($('#epi-skipped-count').text()) || 0;
            var e = parseInt($('#epi-error-count').text()) || 0;
            $('#epi-success-count').text(s + results.success);
            $('#epi-skipped-count').text(sk + results.skipped);
            $('#epi-error-count').text(e + results.errors);
        },

        addLogMessages: function(messages) {
            var log = $('#epi-import-log');
            messages.forEach(function(msg) {
                var icon = msg.type === 'success' ? '✓' : msg.type === 'error' ? '✗' : '⚠';
                log.append('<div class="epi-log-item ' + msg.type + '"><span>' + icon + '</span><span class="epi-log-row">Satır ' + msg.row + ':</span><span>' + msg.message + '</span></div>');
            });
            log.scrollTop(log[0].scrollHeight);
        },

        importComplete: function() {
            $('#epi-progress-status').text('Tamamlandı!');
            $('#epi-import-actions').show();
        },

        resetImport: function() {
            this.currentStep = 1;
            this.fileData = null;
            this.mapping = {};
            $('#epi-file-input').val('');
            $('#epi-file-info').hide();
            $('#epi-next-step-1').prop('disabled', true);
            $('#epi-mapping-grid').empty();
            $('#epi-preview-table thead, #epi-preview-table tbody').empty();
            $('#epi-import-log').empty();
            $('#epi-success-count, #epi-skipped-count, #epi-error-count').text('0');
            $('#epi-progress-percent').text('0');
            $('#epi-import-actions').hide();
            $('.epi-progress-circle .epi-progress-bar').css('stroke-dashoffset', 283);
            $('.epi-step').removeClass('active completed');
            $('.epi-step[data-step="1"]').addClass('active');
            $('.epi-section').removeClass('active').hide();
            $('.epi-section[data-section="1"]').addClass('active').show();
            this.resetUploadZone();
        },

        downloadTemplate: function(format) {
            format = format || 'custom';
            $.post(epiData.ajaxUrl, { 
                action: 'epi_download_template', 
                nonce: epiData.nonce,
                format: format
            }, function(response) {
                if (response.success) {
                    var byteCharacters = atob(response.data.content);
                    var byteNumbers = new Array(byteCharacters.length);
                    for (var i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    var byteArray = new Uint8Array(byteNumbers);
                    var blob = new Blob([byteArray], { type: 'text/csv;charset=utf-8' });
                    
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = response.data.filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            });
        },

        exportProducts: function() {
            var btn = $('#epi-export-btn');
            btn.prop('disabled', true).text('İndiriliyor...');
            
            $.post(epiData.ajaxUrl, {
                action: 'epi_export_products',
                nonce: epiData.nonce,
                category: $('#export-category').val(),
                stock_status: $('#export-stock').val(),
                product_type: $('#export-type').val(),
                price_min: $('#export-price-min').val(),
                price_max: $('#export-price-max').val()
            }, function(response) {
                btn.prop('disabled', false).html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> CSV İndir');
                if (response.success) {
                    var byteCharacters = atob(response.data.content);
                    var byteNumbers = new Array(byteCharacters.length);
                    for (var i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    var byteArray = new Uint8Array(byteNumbers);
                    var blob = new Blob([byteArray], { type: 'text/csv;charset=utf-8' });
                    
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = response.data.filename;
                    a.click();
                    window.URL.revokeObjectURL(url);
                    alert(response.data.count + ' ürün dışa aktarıldı.');
                }
            });
        },

        loadTemplates: function() {
            var self = this;
            $.post(epiData.ajaxUrl, { action: 'epi_get_templates', nonce: epiData.nonce }, function(response) {
                if (response.success) {
                    self.templates = response.data;
                    self.updateTemplateSelect();
                }
            });
        },

        updateTemplateSelect: function() {
            var select = $('#epi-load-template, #schedule-template');
            select.find('option:not(:first)').remove();
            var templates = this.templates;
            for (var id in templates) {
                select.append('<option value="' + templates[id].id + '">' + templates[id].name + '</option>');
            }
        },

        saveTemplate: function() {
            var self = this;
            var name = $('#template-name').val();
            if (!name) { alert('Şablon adı gerekli'); return; }
            
            $.post(epiData.ajaxUrl, {
                action: 'epi_save_template',
                nonce: epiData.nonce,
                name: name,
                description: $('#template-description').val(),
                mapping: JSON.stringify(this.mapping),
                options: JSON.stringify(this.options)
            }, function(response) {
                if (response.success) {
                    alert('Şablon kaydedildi');
                    $('#epi-template-modal').hide();
                    $('#template-name, #template-description').val('');
                    self.loadTemplates();
                }
            });
        },

        loadTemplate: function() {
            var self = this;
            var id = $('#epi-load-template').val();
            if (!id) return;
            
            $.post(epiData.ajaxUrl, { action: 'epi_load_template', nonce: epiData.nonce, template_id: id }, function(response) {
                if (response.success) {
                    self.mapping = response.data.mapping;
                    self.options = response.data.options || {};
                    $('.epi-mapping-select').each(function() {
                        var col = $(this).data('column');
                        $(this).val(self.mapping[col] || '');
                    });
                    $('#epi-skip-existing').prop('checked', self.options.skip_existing !== false);
                    $('#epi-update-existing').prop('checked', self.options.update_existing === true);
                }
            });
        },

        loadTemplatesList: function() {
            var list = $('#epi-templates-list');
            $.post(epiData.ajaxUrl, { action: 'epi_get_templates', nonce: epiData.nonce }, function(response) {
                if (response.success && Object.keys(response.data).length > 0) {
                    list.empty();
                    for (var id in response.data) {
                        var t = response.data[id];
                        list.append('<div class="epi-template-item" data-id="' + t.id + '"><div class="epi-template-info"><h4>' + t.name + '</h4><p>' + (t.description || 'Açıklama yok') + '</p></div><div class="epi-template-actions"><button class="epi-btn epi-btn-outline epi-btn-sm epi-delete-template" data-id="' + t.id + '">Sil</button></div></div>');
                    }
                    $('.epi-delete-template').on('click', function() {
                        if (confirm('Şablonu silmek istediğinize emin misiniz?')) {
                            $.post(epiData.ajaxUrl, { action: 'epi_delete_template', nonce: epiData.nonce, template_id: $(this).data('id') }, function() {
                                EPI.loadTemplatesList();
                                EPI.loadTemplates();
                            });
                        }
                    });
                }
            });
        },

        loadHistory: function(page) {
            page = page || 1;
            $.post(epiData.ajaxUrl, { action: 'epi_get_history', nonce: epiData.nonce, page: page }, function(response) {
                if (response.success) {
                    var tbody = $('#epi-history-table tbody').empty();
                    if (response.data.records.length === 0) {
                        tbody.append('<tr><td colspan="6" style="text-align:center">Henüz içe aktarma yok</td></tr>');
                        return;
                    }
                    response.data.records.forEach(function(r) {
                        var statusClass = r.status === 'completed' ? 'success' : r.status === 'rolled_back' ? 'warning' : 'error';
                        var statusText = r.status === 'completed' ? 'Tamamlandı' : r.status === 'rolled_back' ? 'Geri Alındı' : 'Hata';
                        var rollbackBtn = r.status === 'completed' ? '<button class="epi-btn epi-btn-outline epi-btn-sm epi-rollback" data-id="' + r.id + '">Geri Al</button>' : '';
                        tbody.append('<tr><td>' + r.created_at + '</td><td>' + r.filename + '</td><td><span class="epi-badge epi-badge-success">' + r.success_count + '</span></td><td><span class="epi-badge epi-badge-error">' + r.error_count + '</span></td><td><span class="epi-badge epi-badge-' + statusClass + '">' + statusText + '</span></td><td>' + rollbackBtn + '</td></tr>');
                    });
                    
                    var pagination = $('#epi-history-pagination').empty();
                    for (var i = 1; i <= response.data.pages; i++) {
                        pagination.append('<button class="' + (i === page ? 'active' : '') + '" data-page="' + i + '">' + i + '</button>');
                    }
                    pagination.find('button').on('click', function() {
                        EPI.loadHistory($(this).data('page'));
                    });
                    
                    $('.epi-rollback').on('click', function() {
                        if (confirm('Bu içe aktarmayı geri almak istediğinize emin misiniz? Tüm ürünler silinecek.')) {
                            $.post(epiData.ajaxUrl, { action: 'epi_rollback_import', nonce: epiData.nonce, import_id: $(this).data('id') }, function(res) {
                                alert(res.success ? res.data.message : res.data.message);
                                EPI.loadHistory(1);
                            });
                        }
                    });
                    
                    if (response.data.stats) {
                        var s = response.data.stats;
                        $('#epi-history-stats').html('<div class="epi-stat"><span class="epi-stat-value">' + (s.total_imports || 0) + '</span><span class="epi-stat-label">Toplam İçe Aktarma</span></div><div class="epi-stat"><span class="epi-stat-value">' + (s.total_products || 0) + '</span><span class="epi-stat-label">Toplam Ürün</span></div>');
                    }
                }
            });
        },

        initStockUpdate: function() {
            var self = this;
            var zone = $('#epi-stock-upload-zone');
            var input = $('#epi-stock-file-input');
            if (!zone.length) return;

            zone.on('click', function() { input.trigger('click'); });
            input.on('change', function(e) {
                var file = e.target.files[0];
                if (!file) return;
                self.uploadFile(file);
                zone.hide();
                $('#epi-stock-mapping').show();
            });

            $(document).on('click', '#epi-start-stock-update', function() { self.runStockUpdate(0); });
        },

        runStockUpdate: function(batch) {
            var self = this;
            $('#epi-stock-mapping').hide();
            $('#epi-stock-progress').show();
            
            $.post(epiData.ajaxUrl, {
                action: 'epi_update_stock',
                nonce: epiData.nonce,
                mapping: JSON.stringify(this.mapping),
                batch: batch
            }, function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#stock-progress-fill').css('width', data.progress + '%');
                    $('#stock-progress-text').text(data.progress + '%');
                    
                    data.results.messages.forEach(function(msg) {
                        var icon = msg.type === 'success' ? '✓' : '✗';
                        $('#epi-stock-log').append('<div class="epi-log-item ' + msg.type + '"><span>' + icon + '</span><span>' + msg.message + '</span></div>');
                    });
                    
                    if (data.has_more) {
                        setTimeout(function() { self.runStockUpdate(data.next_batch); }, 100);
                    } else {
                        alert('Stok güncelleme tamamlandı!');
                    }
                }
            });
        },

        initSchedule: function() {
            var self = this;
            
            $(document).on('click', '#epi-add-schedule', function() {
                $('#schedule-id').val('');
                $('#schedule-name, #schedule-source-url, #schedule-ftp-host, #schedule-ftp-user, #schedule-ftp-pass, #schedule-ftp-path').val('');
                $('#schedule-source-type').val('url');
                $('#schedule-frequency').val('daily');
                $('#schedule-time').val('03:00');
                $('#schedule-enabled').prop('checked', true);
                $('#ftp-fields').hide();
                $('#source-url-group').show();
                $('#epi-schedule-modal').show();
            });
            
            $(document).on('change', '#schedule-source-type', function() {
                var type = $(this).val();
                if (type === 'ftp') {
                    $('#ftp-fields').show();
                    $('#source-url-group').hide();
                } else {
                    $('#ftp-fields').hide();
                    $('#source-url-group').show();
                }
            });
            
            $(document).on('click', '#epi-save-schedule', function() { self.saveSchedule(); });
            
            this.loadSchedules();
        },

        loadSchedules: function() {
            var list = $('#epi-schedules-list');
            $.post(epiData.ajaxUrl, { action: 'epi_get_schedules', nonce: epiData.nonce }, function(response) {
                if (response.success && Object.keys(response.data).length > 0) {
                    list.empty();
                    for (var id in response.data) {
                        var s = response.data[id];
                        var statusClass = s.enabled ? 'success' : 'secondary';
                        var statusText = s.enabled ? 'Aktif' : 'Pasif';
                        list.append('<div class="epi-schedule-item" data-id="' + s.id + '"><div class="epi-schedule-info"><h4>' + s.name + '</h4><p>' + s.frequency + ' - ' + s.time + '</p></div><div class="epi-schedule-status"><span class="epi-badge epi-badge-' + statusClass + '">' + statusText + '</span></div><div class="epi-schedule-actions"><button class="epi-btn epi-btn-outline epi-btn-sm epi-toggle-schedule" data-id="' + s.id + '">' + (s.enabled ? 'Durdur' : 'Başlat') + '</button><button class="epi-btn epi-btn-outline epi-btn-sm epi-run-schedule" data-id="' + s.id + '">Şimdi Çalıştır</button><button class="epi-btn epi-btn-outline epi-btn-sm epi-delete-schedule" data-id="' + s.id + '">Sil</button></div></div>');
                    }
                    
                    $('.epi-toggle-schedule').on('click', function() {
                        $.post(epiData.ajaxUrl, { action: 'epi_toggle_schedule', nonce: epiData.nonce, schedule_id: $(this).data('id') }, function() {
                            EPI.loadSchedules();
                        });
                    });
                    
                    $('.epi-run-schedule').on('click', function() {
                        $.post(epiData.ajaxUrl, { action: 'epi_run_schedule', nonce: epiData.nonce, schedule_id: $(this).data('id') }, function(res) {
                            alert(res.data.message);
                        });
                    });
                    
                    $('.epi-delete-schedule').on('click', function() {
                        if (confirm('Zamanlamayı silmek istediğinize emin misiniz?')) {
                            $.post(epiData.ajaxUrl, { action: 'epi_delete_schedule', nonce: epiData.nonce, schedule_id: $(this).data('id') }, function() {
                                EPI.loadSchedules();
                            });
                        }
                    });
                }
            });
        },

        saveSchedule: function() {
            var self = this;
            var name = $('#schedule-name').val();
            if (!name) { alert('Zamanlama adı gerekli'); return; }
            
            $.post(epiData.ajaxUrl, {
                action: 'epi_save_schedule',
                nonce: epiData.nonce,
                schedule_id: $('#schedule-id').val(),
                name: name,
                source_type: $('#schedule-source-type').val(),
                source_url: $('#schedule-source-url').val(),
                ftp_host: $('#schedule-ftp-host').val(),
                ftp_user: $('#schedule-ftp-user').val(),
                ftp_pass: $('#schedule-ftp-pass').val(),
                ftp_path: $('#schedule-ftp-path').val(),
                frequency: $('#schedule-frequency').val(),
                time: $('#schedule-time').val(),
                template_id: $('#schedule-template').val(),
                mode: $('#schedule-mode').val(),
                enabled: $('#schedule-enabled').is(':checked') ? 'true' : 'false'
            }, function(response) {
                if (response.success) {
                    alert('Zamanlama kaydedildi');
                    $('#epi-schedule-modal').hide();
                    self.loadSchedules();
                }
            });
        }
    };

    $(document).ready(function() {
        EPI.init();
    });

})(jQuery);
