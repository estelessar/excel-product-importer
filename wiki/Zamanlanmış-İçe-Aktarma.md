# Zamanlanmış İçe Aktarma

Tedarikçi dosyalarını otomatik olarak belirli aralıklarla içe aktarın.

## Desteklenen Kaynaklar

### 1. URL
Doğrudan erişilebilir bir URL'den dosya çekin.

```
https://tedarikci.com/urunler.csv
https://example.com/exports/products.xlsx
```

### 2. FTP
FTP sunucusundan dosya çekin.

- **Host**: ftp.tedarikci.com
- **Kullanıcı**: kullanici_adi
- **Şifre**: ******
- **Dosya Yolu**: /exports/products.csv

### 3. Google Sheets
Google Sheets'ten otomatik CSV export.

1. Google Sheets'i "Bağlantıya sahip olan herkes görüntüleyebilir" olarak paylaşın
2. Paylaşım linkini kopyalayın
3. Eklenti otomatik olarak CSV formatına dönüştürür

## Zamanlama Oluşturma

1. **Zamanlama** sekmesine gidin
2. **Yeni Zamanlama** butonuna tıklayın
3. Formu doldurun:

### Temel Ayarlar

| Alan | Açıklama |
|------|----------|
| Zamanlama Adı | Tanımlayıcı isim (örn: "Tedarikçi A Günlük") |
| Kaynak Tipi | URL, FTP veya Google Sheets |
| Dosya URL/Yolu | Kaynak dosyanın adresi |

### Zamanlama Ayarları

| Sıklık | Açıklama |
|--------|----------|
| Saatlik | Her saat başı çalışır |
| Günlük | Belirtilen saatte günde bir çalışır |
| Haftalık | Belirtilen gün ve saatte haftada bir çalışır |

### İçe Aktarma Ayarları

| Alan | Açıklama |
|------|----------|
| Eşleştirme Şablonu | Önceden kaydedilmiş şablon |
| Mod | Tam içe aktarma veya sadece stok/fiyat |
| Aktif | Zamanlamayı etkinleştir/devre dışı bırak |

## Örnek Senaryolar

### Senaryo 1: Günlük Stok Güncelleme

```
Kaynak: https://tedarikci.com/stok.csv
Sıklık: Günlük
Saat: 06:00
Mod: Sadece Stok/Fiyat
```

### Senaryo 2: Haftalık Tam Senkronizasyon

```
Kaynak: FTP - /exports/full_catalog.xlsx
Sıklık: Haftalık (Pazartesi)
Saat: 03:00
Mod: Tam İçe Aktarma
```

### Senaryo 3: Saatlik Fiyat Kontrolü

```
Kaynak: Google Sheets
Sıklık: Saatlik
Mod: Sadece Stok/Fiyat
```

## Zamanlama Yönetimi

### Durum Kontrolü

Her zamanlama için görüntülenen bilgiler:
- **Durum**: Aktif / Pasif
- **Son Çalışma**: Tarih ve saat
- **Son Sonuç**: Başarılı / Hata

### Manuel Çalıştırma

**Şimdi Çalıştır** butonu ile zamanlamayı beklemeden çalıştırabilirsiniz.

### Durdurma/Başlatma

**Durdur/Başlat** butonu ile zamanlamayı geçici olarak devre dışı bırakabilirsiniz.

## Şablon Kullanımı

Zamanlanmış içe aktarmalarda şablon kullanmak önemlidir:

1. Manuel içe aktarma yapın
2. Eşleştirmeyi kaydedin (Şablon olarak)
3. Zamanlamada bu şablonu seçin

Bu sayede her seferinde aynı eşleştirme kullanılır.

## Hata Yönetimi

### Bağlantı Hatası

Kaynak dosyaya erişilemezse:
- Hata loglanır
- Bir sonraki zamanlamada tekrar denenir
- Admin'e e-posta gönderilebilir (opsiyonel)

### Dosya Format Hatası

Dosya okunamazsa:
- Hata detayları kaydedilir
- İçe aktarma iptal edilir

### Kısmi Hatalar

Bazı satırlar hatalıysa:
- Başarılı satırlar işlenir
- Hatalı satırlar loglanır
- Özet rapor oluşturulur

## WordPress Cron

Zamanlamalar WordPress Cron sistemi üzerinden çalışır.

### Cron Kontrolü

```php
// Zamanlanmış görevleri listele
wp_get_scheduled_event('epi_scheduled_import');
```

### Gerçek Cron Kullanımı

Daha güvenilir zamanlama için sunucu cron'u kullanın:

```bash
# crontab -e
*/15 * * * * wget -q -O - https://siteniz.com/wp-cron.php?doing_wp_cron > /dev/null 2>&1
```

Ve `wp-config.php`'ye ekleyin:

```php
define('DISABLE_WP_CRON', true);
```

## Güvenlik Notları

- FTP şifreleri veritabanında şifreli saklanır
- Sadece admin kullanıcılar zamanlama oluşturabilir
- Tüm işlemler loglanır
