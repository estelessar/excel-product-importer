# Desteklenen Alanlar

Bu sayfa, eklentinin desteklediği tüm alanları listeler.

## Temel Ürün Alanları

| Alan Adı (Özel) | Alan Adı (WC TR) | Alan Adı (WC EN) | Açıklama |
|-----------------|------------------|------------------|----------|
| `product_name` | `İsim` | `Name` | Ürün adı (zorunlu) |
| `sku` | `Stok kodu (SKU)` | `SKU` | Benzersiz stok kodu |
| `description` | `Açıklama` | `Description` | Uzun açıklama |
| `short_description` | `Kısa açıklama` | `Short description` | Kısa açıklama |
| `regular_price` | `Normal fiyat` | `Regular price` | Normal fiyat |
| `sale_price` | `İndirimli satış fiyatı` | `Sale price` | İndirimli fiyat |
| `stock_quantity` | `Stok` | `Stock` | Stok miktarı |
| `category` | `Kategoriler` | `Categories` | Kategori (virgülle ayrılmış) |
| `tags` | `Etiketler` | `Tags` | Etiketler (virgülle ayrılmış) |

## Görsel Alanları

| Alan Adı | Açıklama |
|----------|----------|
| `image_url` | Ana ürün görseli URL'si |
| `gallery_urls` | Galeri görselleri (virgülle ayrılmış URL'ler) |
| `Görseller` / `Images` | WooCommerce formatı (tüm görseller virgülle) |

## Boyut ve Ağırlık

| Alan Adı (Özel) | Alan Adı (WC TR) | Alan Adı (WC EN) |
|-----------------|------------------|------------------|
| `weight` | `Ağırlık (kg)` | `Weight (kg)` |
| `length` | `Uzunluk (cm)` | `Length (cm)` |
| `width` | `Genişlik (cm)` | `Width (cm)` |
| `height` | `Yükseklik (cm)` | `Height (cm)` |

## Stok Yönetimi

| Alan Adı (Özel) | Alan Adı (WC TR) | Alan Adı (WC EN) | Değerler |
|-----------------|------------------|------------------|----------|
| `stock_quantity` | `Stok` | `Stock` | Sayı |
| `stock_status` | `Stokta?` | `In stock?` | `1` / `0` veya `instock` / `outofstock` |
| `backorders_allowed` | `Yok satmaya izin?` | `Backorders allowed?` | `1` / `0` |
| `low_stock_amount` | `Düşük stok miktarı` | `Low stock amount` | Sayı |

## Vergi Ayarları

| Alan Adı (WC TR) | Alan Adı (WC EN) | Değerler |
|------------------|------------------|----------|
| `Vergi durumu` | `Tax status` | `taxable`, `shipping`, `none` |
| `Vergi sınıfı` | `Tax class` | Vergi sınıfı adı |

## Ürün Tipi ve İlişkiler

| Alan Adı | Değerler | Açıklama |
|----------|----------|----------|
| `product_type` / `Tür` / `Type` | `simple`, `variable`, `variation`, `grouped`, `external` | Ürün tipi |
| `parent_sku` / `Ebeveyn` / `Parent` | SKU | Varyasyon için ana ürün SKU'su |

## Öznitelikler (Attributes)

### Özel Format
```
attribute_1_name, attribute_1_values
attribute_2_name, attribute_2_values
attribute_3_name, attribute_3_values
```

### WooCommerce Türkçe Format
```
Nitelik 1 ismi, Nitelik 1 değer(ler)i, Nitelik 1 görünür, Nitelik 1 genel
Nitelik 2 ismi, Nitelik 2 değer(ler)i, Nitelik 2 görünür, Nitelik 2 genel
```

### WooCommerce İngilizce Format
```
Attribute 1 name, Attribute 1 value(s), Attribute 1 visible, Attribute 1 global
Attribute 2 name, Attribute 2 value(s), Attribute 2 visible, Attribute 2 global
```

**Notlar:**
- Birden fazla değer için `|` (özel) veya `,` (WC) kullanın
- `visible`: Ürün sayfasında göster (`1` / `0`)
- `global`: Genel öznitelik kullan (`1` / `0`)

## Diğer Alanlar

| Alan Adı (WC TR) | Alan Adı (WC EN) | Açıklama |
|------------------|------------------|----------|
| `Kimlik` | `ID` | Mevcut ürün ID'si (güncelleme için) |
| `Yayımlanmış` | `Published` | `1` = yayında, `0` = taslak |
| `Öne çıkan?` | `Is featured?` | `1` / `0` |
| `Katalogda görünürlük` | `Visibility in catalog` | `visible`, `catalog`, `search`, `hidden` |
| `Satın alma notu` | `Purchase note` | Satın alma sonrası not |
| `Gönderim sınıfı` | `Shipping class` | Kargo sınıfı |
| `Konum` | `Position` | Menü sırası |

## İndirim Tarihleri

| Alan Adı (WC TR) | Alan Adı (WC EN) | Format |
|------------------|------------------|--------|
| `İndirimli fiyatın başladığı tarih` | `Date sale price starts` | `YYYY-MM-DD` |
| `İndirimli fiyatın bittiği tarih` | `Date sale price ends` | `YYYY-MM-DD` |

## İlişkili Ürünler

| Alan Adı (WC TR) | Alan Adı (WC EN) | Açıklama |
|------------------|------------------|----------|
| `Yukarı satışlar` | `Upsells` | SKU'lar (virgülle ayrılmış) |
| `Çapraz satışlar` | `Cross-sells` | SKU'lar (virgülle ayrılmış) |
| `Gruplanmış ürünler` | `Grouped products` | SKU'lar (virgülle ayrılmış) |

## External/Affiliate Ürünler

| Alan Adı (WC TR) | Alan Adı (WC EN) | Açıklama |
|------------------|------------------|----------|
| `Harici URL` | `External URL` | Ürün linki |
| `Düğme metni` | `Button text` | Buton yazısı |

## Meta Alanları

WooCommerce export formatında `Meta:` öneki ile özel meta alanları desteklenir:

```csv
Meta:_custom_field,Meta:brand_name
değer1,değer2
```

## Hiyerarşik Kategoriler

Kategoriler `>` ile hiyerarşik olarak belirtilebilir:

```
Giyim > Erkek > T-Shirt
```

Birden fazla kategori virgülle ayrılır:

```
Giyim > Erkek > T-Shirt, Yeni Gelenler, İndirimli Ürünler
```
