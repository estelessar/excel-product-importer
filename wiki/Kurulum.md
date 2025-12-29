# Kurulum

## Gereksinimler

- WordPress 5.8 veya üzeri
- WooCommerce 6.0 veya üzeri
- PHP 7.4 veya üzeri

## Kurulum Adımları

### Yöntem 1: WordPress Admin Paneli

1. WordPress admin paneline giriş yapın
2. **Eklentiler > Yeni Ekle** menüsüne gidin
3. **Eklenti Yükle** butonuna tıklayın
4. `excel-product-importer.zip` dosyasını seçin
5. **Şimdi Yükle** butonuna tıklayın
6. Yükleme tamamlandıktan sonra **Eklentiyi Etkinleştir** butonuna tıklayın

### Yöntem 2: FTP ile Manuel Kurulum

1. `excel-product-importer` klasörünü `/wp-content/plugins/` dizinine yükleyin
2. WordPress admin panelinden **Eklentiler** menüsüne gidin
3. **Excel Product Importer** eklentisini bulun ve **Etkinleştir** butonuna tıklayın

### Yöntem 3: Composer (Geliştiriciler için)

```bash
composer require adnbilisim/excel-product-importer
```

## Kurulum Sonrası

Eklenti etkinleştirildikten sonra:

1. Sol menüde **Excel İçe Aktar** seçeneği görünecektir
2. İlk kullanımda gerekli veritabanı tabloları otomatik oluşturulur
3. Upload dizini (`/wp-content/uploads/epi-imports/`) otomatik oluşturulur

## Güvenlik

- Upload dizini `.htaccess` ile korunur
- Tüm AJAX istekleri nonce doğrulaması gerektirir
- Sadece `manage_woocommerce` yetkisine sahip kullanıcılar erişebilir

## Sorun Giderme

### WooCommerce Uyarısı

Eğer "WooCommerce eklentisinin yüklü ve aktif olması gerekmektedir" uyarısı alıyorsanız:

1. WooCommerce eklentisinin yüklü olduğundan emin olun
2. WooCommerce'in aktif olduğunu kontrol edin
3. WordPress'i yeniden yükleyin

### Dosya Yükleme Hatası

Eğer dosya yükleyemiyorsanız:

1. PHP `upload_max_filesize` değerini kontrol edin
2. PHP `post_max_size` değerini kontrol edin
3. WordPress `WP_MEMORY_LIMIT` değerini kontrol edin

```php
// wp-config.php dosyasına ekleyin
define('WP_MEMORY_LIMIT', '256M');
```
