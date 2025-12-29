# Sık Sorulan Sorular (SSS)

## Genel Sorular

### Hangi dosya formatları destekleniyor?

- Microsoft Excel (.xlsx)
- Microsoft Excel 97-2003 (.xls)
- CSV (Comma/Semicolon Separated Values)

### Maksimum kaç ürün içe aktarabilirim?

Teknik bir limit yoktur. Ancak performans için:
- 1000+ ürün için batch işleme kullanılır
- Sunucu kaynaklarına bağlı olarak zaman aşımı olabilir
- Büyük dosyalar için zamanlanmış içe aktarma önerilir

### WooCommerce'den dışa aktardığım dosyayı kullanabilir miyim?

Evet! Eklenti WooCommerce'in Türkçe ve İngilizce export formatlarını tam destekler.

---

## İçe Aktarma Sorunları

### "Ürün adı zorunludur" hatası alıyorum

**Neden:** `product_name` veya `İsim` sütunu eşleştirilmemiş.

**Çözüm:** Eşleştirme adımında ürün adı sütununu seçin.

### Türkçe karakterler bozuk görünüyor

**Neden:** Dosya UTF-8 formatında değil.

**Çözüm:**
1. Excel'de "Farklı Kaydet" seçin
2. Format olarak "CSV UTF-8" seçin
3. Veya eklentinin şablonunu indirip kullanın (UTF-8 BOM ile)

### Varyasyonlar oluşturulmuyor

**Neden:** Ana ürün bulunamıyor veya `parent_sku` eksik.

**Çözüm:**
1. Önce ana ürünü (`variable`) içe aktarın
2. Varyasyonlarda `parent_sku` alanını doldurun
3. `product_type` = `variation` olduğundan emin olun

### Görseller yüklenmiyor

**Neden:** URL erişilebilir değil veya format desteklenmiyor.

**Çözüm:**
1. URL'nin tarayıcıda açıldığını kontrol edin
2. HTTPS kullanın
3. Desteklenen formatlar: JPG, PNG, GIF, WebP

### Kategoriler oluşturulmuyor

**Neden:** Kategori formatı hatalı.

**Çözüm:**
- Hiyerarşi için `>` kullanın: `Giyim > Erkek > T-Shirt`
- Birden fazla kategori için `,` kullanın
- Boşlukları kontrol edin

---

## Varyasyon Soruları

### Her varyasyonun farklı fiyatı olabilir mi?

Evet! Her varyasyon satırında `regular_price` ve `sale_price` belirtebilirsiniz.

### Varyasyona özel görsel ekleyebilir miyim?

Evet, varyasyon satırında `image_url` alanını kullanın.

### Mevcut varyasyonları güncelleyebilir miyim?

Evet, "Mevcut ürünleri güncelle" seçeneğini işaretleyin. SKU bazlı eşleşme yapılır.

---

## Stok ve Fiyat

### Sadece stok güncellemesi yapabilir miyim?

Evet! **Stok/Fiyat** sekmesini kullanın. Sadece SKU, stok ve fiyat içeren dosya yeterli.

### Fiyat formatı nasıl olmalı?

- Ondalık ayracı: `.` veya `,`
- Para birimi simgesi kullanmayın
- Örnekler: `299.90`, `1500`, `99,90`

### Stok 0 olan ürünler "stok dışı" olur mu?

Evet, stok miktarı 0 veya negatif ise ürün otomatik olarak "stok dışı" olarak işaretlenir.

---

## Şablon ve Eşleştirme

### Eşleştirmeyi kaydedebilir miyim?

Evet! Eşleştirme adımında "Kaydet" butonuna tıklayın ve şablon adı verin.

### Farklı tedarikçiler için farklı şablon kullanabilir miyim?

Evet, her tedarikçi için ayrı şablon oluşturabilirsiniz.

### Şablonları dışa/içe aktarabilir miyim?

Şu an için bu özellik mevcut değil, ancak gelecek sürümlerde planlanıyor.

---

## Performans

### İçe aktarma çok yavaş

**Çözümler:**
1. Batch boyutunu artırın (varsayılan: 10)
2. Görsel indirmeyi devre dışı bırakın
3. Sunucu kaynaklarını kontrol edin
4. WP_MEMORY_LIMIT değerini artırın

### Zaman aşımı hatası alıyorum

**Çözümler:**
1. PHP `max_execution_time` değerini artırın
2. Daha küçük dosyalarla deneyin
3. Zamanlanmış içe aktarma kullanın

---

## Güvenlik

### Yüklenen dosyalar nerede saklanıyor?

`/wp-content/uploads/epi-imports/` dizininde. Bu dizin `.htaccess` ile korunur.

### Kim içe aktarma yapabilir?

Sadece `manage_woocommerce` yetkisine sahip kullanıcılar (genellikle Shop Manager ve Admin).

### Dosyalar ne zaman siliniyor?

- Başarılı içe aktarma sonrası otomatik silinir
- Eklenti devre dışı bırakıldığında tüm geçici dosyalar silinir

---

## Diğer

### Eklentiyi kaldırırsam verilerim silinir mi?

- Ürünler silinmez
- Ayarlar ve geçmiş silinir (uninstall.php)
- Geçici dosyalar silinir

### Çoklu dil desteği var mı?

Eklenti Türkçe ve İngilizce WooCommerce export formatlarını destekler. Arayüz şu an Türkçe'dir.

### HPOS (High-Performance Order Storage) uyumlu mu?

Evet, eklenti WooCommerce HPOS ile tam uyumludur.
