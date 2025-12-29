# Hızlı Başlangıç

Bu kılavuz, eklentiyi ilk kez kullananlar için temel adımları açıklar.

## 1. Şablon İndirin

1. **Excel İçe Aktar** menüsüne gidin
2. Sağ üstteki **Şablon İndir** butonuna tıklayın
3. İhtiyacınıza uygun formatı seçin:
   - **Özel Şablon**: Basit format (önerilen)
   - **WooCommerce Türkçe**: WooCommerce uyumlu TR
   - **WooCommerce English**: WooCommerce uyumlu EN
   - **Stok Güncelleme**: Sadece SKU, stok, fiyat

## 2. Excel Dosyanızı Hazırlayın

### Basit Ürün Örneği

| product_name | sku | regular_price | stock_quantity | category |
|--------------|-----|---------------|----------------|----------|
| Deri Cüzdan | WALLET-001 | 299.90 | 30 | Aksesuar |
| Slim Pantolon | PANT-001 | 199.90 | 50 | Giyim |

### Zorunlu Alanlar

- `product_name` veya `İsim`: Ürün adı
- `sku` veya `Stok kodu (SKU)`: Benzersiz stok kodu
- `regular_price` veya `Normal fiyat`: Ürün fiyatı

## 3. Dosyayı Yükleyin

1. Excel dosyanızı sürükle-bırak ile yükleyin
2. Veya "dosya seçmek için tıklayın" yazısına tıklayın
3. Desteklenen formatlar: `.xlsx`, `.xls`, `.csv`

## 4. Sütunları Eşleştirin

Eklenti sütunları otomatik eşleştirmeye çalışır. Eşleşmeyen sütunları manuel olarak seçin:

- Sol tarafta Excel sütun adı
- Sağ tarafta WooCommerce alanı

## 5. Seçenekleri Ayarlayın

- **Mevcut ürünleri atla**: Aynı SKU'ya sahip ürünler atlanır
- **Mevcut ürünleri güncelle**: Aynı SKU'ya sahip ürünler güncellenir

## 6. Önizleme ve İçe Aktarma

1. **Önizleme** butonuna tıklayın
2. İlk 5 satırı kontrol edin
3. **İçe Aktarmayı Başlat** butonuna tıklayın
4. İlerlemeyi takip edin

## 7. Sonuçları Kontrol Edin

- ✅ Başarılı: Oluşturulan ürün sayısı
- ⚠️ Atlanan: Mevcut olan ürün sayısı
- ❌ Hata: Hatalı satır sayısı

## Sonraki Adımlar

- [[Varyasyonlu Ürün İçe Aktarma]]
- [[Şablon Yönetimi]]
- [[Zamanlanmış İçe Aktarma]]
