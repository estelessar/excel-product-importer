=== Excel Product Importer for WooCommerce ===
Contributors: adnbilisim
Tags: woocommerce, import, excel, products, bulk import, csv, xlsx
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Excel dosyalarından WooCommerce'e toplu ürün yükleme eklentisi. Varyasyonlu ürün desteği ile.

== Description ==

Excel Product Importer, WooCommerce mağazanıza Excel (.xlsx, .xls) veya CSV dosyalarından toplu ürün yüklemenizi sağlayan güçlü bir eklentidir.

= Özellikler =

* **Kolay Dosya Yükleme**: Sürükle-bırak ile dosya yükleme
* **Akıllı Sütun Eşleştirme**: Excel sütunlarını WooCommerce alanlarıyla otomatik eşleştirme
* **Varyasyonlu Ürün Desteği**: Renk, beden gibi varyasyonlu ürünleri kolayca içe aktarın
* **Gerçek Zamanlı İlerleme**: İçe aktarma sürecini canlı takip edin
* **Hata Raporlama**: Detaylı hata ve uyarı mesajları
* **Şablon Desteği**: Hazır Excel şablonu ile hızlı başlangıç
* **Modern Arayüz**: Koyu tema ile şık ve kullanıcı dostu tasarım

= Desteklenen Dosya Formatları =

* Microsoft Excel (.xlsx)
* Microsoft Excel 97-2003 (.xls)
* CSV (Comma Separated Values)

= Desteklenen Ürün Alanları =

* Ürün Adı
* SKU (Stok Kodu)
* Açıklama ve Kısa Açıklama
* Normal Fiyat ve İndirimli Fiyat
* Stok Miktarı
* Kategoriler ve Etiketler
* Ürün Görselleri (URL)
* Ağırlık ve Boyutlar
* Öznitelikler (Varyasyonlar için)

== Installation ==

1. Eklenti dosyalarını `/wp-content/plugins/excel-product-importer` dizinine yükleyin
2. WordPress admin panelinden 'Eklentiler' menüsüne gidin
3. 'Excel Product Importer' eklentisini etkinleştirin
4. Sol menüden 'Excel İçe Aktar' seçeneğine tıklayın

== Frequently Asked Questions ==

= Hangi Excel formatları destekleniyor? =

.xlsx, .xls ve .csv formatları desteklenmektedir.

= Varyasyonlu ürünleri nasıl içe aktarabilirim? =

Önce ana ürünü (variable) oluşturun, ardından varyasyonları (variation) parent_sku alanını kullanarak ekleyin. Detaylı bilgi için örnek şablonu indirin.

= Mevcut ürünleri güncelleyebilir miyim? =

Evet, "Mevcut ürünleri güncelle" seçeneğini işaretleyerek SKU bazlı güncelleme yapabilirsiniz.

= Görsel yüklemesi nasıl çalışıyor? =

Görsel URL'lerini Excel'e ekleyin, eklenti görselleri otomatik olarak medya kütüphanesine indirecektir.

== Screenshots ==

1. Dosya yükleme ekranı
2. Sütun eşleştirme arayüzü
3. Veri önizleme
4. İçe aktarma ilerleme ekranı

== Changelog ==

= 1.0.0 =
* İlk sürüm
* Excel ve CSV dosya desteği
* Varyasyonlu ürün desteği
* Sürükle-bırak dosya yükleme
* Otomatik sütun eşleştirme
* Gerçek zamanlı ilerleme takibi
* Modern koyu tema arayüz

== Upgrade Notice ==

= 1.0.0 =
İlk sürüm.

== Credits ==

Geliştirici: [ADN Bilişim Teknolojileri LTD. ŞTİ.](https://www.adnbilisim.com.tr)
