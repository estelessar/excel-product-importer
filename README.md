# Excel Product Importer for WooCommerce

Excel dosyalarından WooCommerce'e toplu ürün yükleme eklentisi. Varyasyonlu ürün desteği ile.

![WordPress](https://img.shields.io/badge/WordPress-5.8+-blue.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0+-purple.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)
![License](https://img.shields.io/badge/License-GPLv2-red.svg)

## Özellikler

- **Kolay Dosya Yükleme**: Sürükle-bırak ile dosya yükleme
- **Akıllı Sütun Eşleştirme**: Excel sütunlarını WooCommerce alanlarıyla otomatik eşleştirme
- **Varyasyonlu Ürün Desteği**: Renk, beden gibi varyasyonlu ürünleri kolayca içe aktarın
- **WooCommerce Export Desteği**: WooCommerce'den dışa aktarılan dosyaları doğrudan içe aktarın (TR/EN)
- **Gerçek Zamanlı İlerleme**: İçe aktarma sürecini canlı takip edin
- **Hata Raporlama**: Detaylı hata ve uyarı mesajları
- **Şablon Desteği**: Farklı tedarikçiler için eşleştirme şablonları
- **Zamanlanmış İçe Aktarma**: URL, FTP veya Google Sheets'ten otomatik içe aktarma
- **Stok/Fiyat Güncelleme**: Sadece stok ve fiyat güncellemesi için hızlı mod
- **Dışa Aktarma**: Mevcut ürünleri CSV olarak dışa aktarın
- **Geri Alma**: İçe aktarılan ürünleri tek tıkla geri alın

## Desteklenen Dosya Formatları

- Microsoft Excel (.xlsx)
- Microsoft Excel 97-2003 (.xls)
- CSV (Comma/Semicolon Separated Values)

## Kurulum

1. Eklenti dosyalarını `/wp-content/plugins/excel-product-importer` dizinine yükleyin
2. WordPress admin panelinden 'Eklentiler' menüsüne gidin
3. 'Excel Product Importer' eklentisini etkinleştirin
4. Sol menüden 'Excel İçe Aktar' seçeneğine tıklayın

## Kullanım

### Basit Ürün İçe Aktarma

1. Excel dosyanızı hazırlayın (örnek şablonu indirin)
2. Dosyayı sürükle-bırak ile yükleyin
3. Sütunları WooCommerce alanlarıyla eşleştirin
4. Önizleme yapın ve içe aktarmayı başlatın

### Varyasyonlu Ürün İçe Aktarma

1. Önce ana ürünü `variable` tipi ile ekleyin
2. Varyasyonları `variation` tipi ve `parent_sku` ile ekleyin
3. Her varyasyonun kendi SKU, stok ve fiyatı olabilir

### WooCommerce Export Dosyası Kullanma

WooCommerce'den dışa aktardığınız CSV dosyasını doğrudan içe aktarabilirsiniz. Türkçe ve İngilizce başlıklar otomatik tanınır.

## Şablon Formatları

Eklenti 4 farklı şablon formatı sunar:

1. **Özel Şablon**: Basit format, noktalı virgül ayraçlı
2. **WooCommerce Türkçe**: WooCommerce export formatı (TR)
3. **WooCommerce English**: WooCommerce export format (EN)
4. **Stok Güncelleme**: Sadece SKU, stok ve fiyat

## Gereksinimler

- WordPress 5.8+
- WooCommerce 6.0+
- PHP 7.4+

## Geliştirici

[ADN Bilişim Teknolojileri LTD. ŞTİ.](https://www.adnbilisim.com.tr)

## Lisans

GPLv2 or later
