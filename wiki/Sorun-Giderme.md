# Sorun Giderme

Bu sayfa, yaygın sorunları ve çözümlerini içerir.

## Dosya Yükleme Sorunları

### Dosya yüklenmiyor

**Belirtiler:**
- Yükleme başlamıyor
- "Dosya yüklenemedi" hatası

**Çözümler:**

1. **PHP Ayarlarını Kontrol Edin**
```php
// php.ini veya .htaccess
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
memory_limit = 256M
```

2. **WordPress Bellek Limitini Artırın**
```php
// wp-config.php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
```

3. **Dosya İzinlerini Kontrol Edin**
```bash
chmod 755 /wp-content/uploads/
chmod 755 /wp-content/uploads/epi-imports/
```

### "Geçersiz dosya formatı" hatası

**Çözümler:**
1. Dosya uzantısının `.xlsx`, `.xls` veya `.csv` olduğundan emin olun
2. Dosyayı yeniden kaydedin
3. Farklı bir format deneyin (örn: XLSX yerine CSV)

---

## İçe Aktarma Hataları

### "Ürün adı zorunludur"

**Neden:** Ürün adı sütunu eşleştirilmemiş veya boş.

**Çözüm:**
1. Eşleştirme adımında `product_name` veya `İsim` alanını seçin
2. Excel'de boş satır olmadığından emin olun

### "Parent ürün bulunamadı"

**Neden:** Varyasyon için belirtilen `parent_sku` mevcut değil.

**Çözüm:**
1. Önce ana ürünü içe aktarın
2. `parent_sku` değerinin doğru olduğunu kontrol edin
3. Ana ürünün `product_type` = `variable` olduğundan emin olun

### "SKU zaten mevcut"

**Neden:** Aynı SKU'ya sahip ürün var.

**Çözüm:**
1. "Mevcut ürünleri güncelle" seçeneğini işaretleyin
2. Veya "Mevcut ürünleri atla" seçeneğini kullanın
3. SKU'ları benzersiz yapın

### Türkçe karakterler bozuk

**Neden:** Dosya encoding sorunu.

**Çözüm:**
1. Dosyayı UTF-8 olarak kaydedin
2. Excel'de: Farklı Kaydet > CSV UTF-8
3. Eklentinin şablonunu kullanın (UTF-8 BOM içerir)

---

## Görsel Sorunları

### Görseller yüklenmiyor

**Kontrol Listesi:**
1. URL'nin erişilebilir olduğunu kontrol edin
2. HTTPS kullanın
3. Görsel formatının desteklendiğinden emin olun (JPG, PNG, GIF, WebP)
4. Sunucunun dış bağlantılara izin verdiğini kontrol edin

**Test:**
```php
// URL'yi test edin
$response = wp_remote_get('https://example.com/image.jpg');
var_dump(wp_remote_retrieve_response_code($response));
```

### Görseller çok yavaş yükleniyor

**Çözümler:**
1. Görsel boyutlarını optimize edin
2. CDN kullanın
3. Batch boyutunu azaltın
4. Görselleri ayrı bir işlemde yükleyin

---

## Performans Sorunları

### İçe aktarma çok yavaş

**Optimizasyon Önerileri:**

1. **Batch Boyutunu Ayarlayın**
```php
// Varsayılan: 10, artırabilirsiniz
add_filter('epi_batch_size', function() { return 20; });
```

2. **Gereksiz İşlemleri Devre Dışı Bırakın**
```php
// wp-config.php
define('WP_DEBUG', false);
define('SAVEQUERIES', false);
```

3. **Object Cache Kullanın**
Redis veya Memcached kurulumu önerilir.

### Zaman aşımı hatası

**Çözümler:**

1. **PHP Timeout Artırın**
```php
// .htaccess
php_value max_execution_time 600
```

2. **WordPress Timeout Artırın**
```php
// wp-config.php
set_time_limit(600);
```

3. **Daha Küçük Dosyalar Kullanın**
Büyük dosyaları parçalara bölün.

### Bellek hatası

**Çözümler:**
```php
// wp-config.php
define('WP_MEMORY_LIMIT', '512M');

// php.ini
memory_limit = 512M
```

---

## Veritabanı Sorunları

### Geçmiş tablosu oluşturulmamış

**Çözüm:**
1. Eklentiyi devre dışı bırakın
2. Tekrar etkinleştirin
3. Veya manuel oluşturun:

```sql
CREATE TABLE wp_epi_import_history (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    filename varchar(255) NOT NULL,
    file_size bigint(20) DEFAULT 0,
    total_rows int(11) DEFAULT 0,
    success_count int(11) DEFAULT 0,
    error_count int(11) DEFAULT 0,
    skipped_count int(11) DEFAULT 0,
    product_ids longtext,
    mapping longtext,
    options longtext,
    status varchar(20) DEFAULT 'completed',
    error_log longtext,
    user_id bigint(20) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

---

## JavaScript Sorunları

### Butonlar çalışmıyor

**Çözümler:**
1. Tarayıcı konsolunu kontrol edin (F12)
2. JavaScript hatalarını arayın
3. Diğer eklentilerle çakışma olabilir - tek tek devre dışı bırakın
4. Tarayıcı önbelleğini temizleyin

### Sürükle-bırak çalışmıyor

**Çözümler:**
1. Farklı tarayıcı deneyin
2. JavaScript'in etkin olduğundan emin olun
3. "Dosya seçmek için tıklayın" seçeneğini kullanın

---

## Zamanlama Sorunları

### Zamanlanmış içe aktarma çalışmıyor

**Kontrol Listesi:**
1. WordPress Cron'un çalıştığını kontrol edin
2. Zamanlamanın "Aktif" olduğundan emin olun
3. Kaynak URL'nin erişilebilir olduğunu test edin

**Cron Test:**
```php
// Zamanlanmış görevleri listele
$crons = _get_cron_array();
foreach ($crons as $timestamp => $cron) {
    if (isset($cron['epi_scheduled_import'])) {
        echo date('Y-m-d H:i:s', $timestamp);
    }
}
```

### FTP bağlantı hatası

**Kontrol Listesi:**
1. FTP bilgilerini doğrulayın
2. Pasif mod gerekebilir (eklenti otomatik kullanır)
3. Firewall ayarlarını kontrol edin
4. FTP portunu kontrol edin (genellikle 21)

---

## Hata Ayıklama

### Debug Modu

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Loglar: `/wp-content/debug.log`

### Eklenti Logları

İçe aktarma geçmişi **Geçmiş** sekmesinde görüntülenebilir.

### Destek İsteme

Sorun devam ediyorsa [GitHub Issues](https://github.com/estelessar/excel-product-importer/issues) sayfasında yeni bir issue açın:

1. WordPress ve WooCommerce sürümü
2. PHP sürümü
3. Hata mesajı (tam metin)
4. Örnek CSV dosyası (hassas veriler olmadan)
5. Yapılan adımlar
