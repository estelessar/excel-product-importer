# Varyasyonlu Ürün İçe Aktarma

Bu kılavuz, renk, beden gibi varyasyonlara sahip ürünlerin nasıl içe aktarılacağını açıklar.

## Temel Kavramlar

### Variable (Ana Ürün)
Varyasyonların bağlı olduğu ana üründür. Örneğin "Basic T-Shirt".

### Variation (Varyasyon)
Ana ürünün bir çeşididir. Örneğin "Basic T-Shirt - Kırmızı M".

## CSV Formatı

### 1. Önce Ana Ürünü Ekleyin

```csv
product_name;sku;regular_price;product_type;attribute_1_name;attribute_1_values;attribute_2_name;attribute_2_values
Basic T-Shirt;TSHIRT-001;99.90;variable;Renk;Kırmızı|Mavi|Siyah;Beden;S|M|L|XL
```

**Önemli Noktalar:**
- `product_type` = `variable` olmalı
- Tüm varyasyon seçenekleri `|` ile ayrılmalı
- Her öznitelik için `name` ve `values` gerekli

### 2. Sonra Varyasyonları Ekleyin

```csv
product_name;sku;regular_price;sale_price;stock_quantity;product_type;parent_sku;attribute_1_name;attribute_1_values;attribute_2_name;attribute_2_values
Basic T-Shirt - Kırmızı S;TSHIRT-001-RED-S;99.90;79.90;25;variation;TSHIRT-001;Renk;Kırmızı;Beden;S
Basic T-Shirt - Kırmızı M;TSHIRT-001-RED-M;109.90;89.90;30;variation;TSHIRT-001;Renk;Kırmızı;Beden;M
Basic T-Shirt - Mavi S;TSHIRT-001-BLUE-S;99.90;79.90;18;variation;TSHIRT-001;Renk;Mavi;Beden;S
```

**Önemli Noktalar:**
- `product_type` = `variation` olmalı
- `parent_sku` = Ana ürünün SKU'su
- Her varyasyonun kendi SKU, fiyat ve stok değeri olabilir
- Öznitelik değerleri tek değer olmalı (Kırmızı, S gibi)

## Tam Örnek

```csv
product_name;sku;description;regular_price;sale_price;stock_quantity;category;product_type;parent_sku;attribute_1_name;attribute_1_values;attribute_2_name;attribute_2_values
# Ana Ürün
Basic T-Shirt;TSHIRT-001;Pamuklu basic t-shirt;99.90;;100;Giyim;variable;;Renk;Kırmızı|Mavi|Siyah;Beden;S|M|L|XL
# Varyasyonlar
Basic T-Shirt - Kırmızı S;TSHIRT-001-RED-S;;99.90;79.90;25;;variation;TSHIRT-001;Renk;Kırmızı;Beden;S
Basic T-Shirt - Kırmızı M;TSHIRT-001-RED-M;;109.90;89.90;30;;variation;TSHIRT-001;Renk;Kırmızı;Beden;M
Basic T-Shirt - Kırmızı L;TSHIRT-001-RED-L;;119.90;99.90;20;;variation;TSHIRT-001;Renk;Kırmızı;Beden;L
Basic T-Shirt - Mavi S;TSHIRT-001-BLUE-S;;99.90;79.90;18;;variation;TSHIRT-001;Renk;Mavi;Beden;S
Basic T-Shirt - Mavi M;TSHIRT-001-BLUE-M;;109.90;89.90;22;;variation;TSHIRT-001;Renk;Mavi;Beden;M
Basic T-Shirt - Siyah S;TSHIRT-001-BLK-S;;99.90;79.90;35;;variation;TSHIRT-001;Renk;Siyah;Beden;S
```

## Tek Öznitelikli Varyasyon

Sadece beden gibi tek öznitelik için:

```csv
product_name;sku;regular_price;product_type;parent_sku;attribute_1_name;attribute_1_values
Bebek Tulumu;BABY-001;349.90;variable;;Beden;0-6 Ay|6-12 Ay|12-18 Ay
Bebek Tulumu - 0-6 Ay;BABY-001-0-6;349.90;variation;BABY-001;Beden;0-6 Ay
Bebek Tulumu - 6-12 Ay;BABY-001-6-12;349.90;variation;BABY-001;Beden;6-12 Ay
Bebek Tulumu - 12-18 Ay;BABY-001-12-18;369.90;variation;BABY-001;Beden;12-18 Ay
```

## Varyasyona Özel Fiyatlandırma

Her varyasyonun farklı fiyatı olabilir:

| SKU | Regular Price | Sale Price | Stock |
|-----|---------------|------------|-------|
| TSHIRT-001-RED-S | 99.90 | 79.90 | 25 |
| TSHIRT-001-RED-M | 109.90 | 89.90 | 30 |
| TSHIRT-001-RED-L | 119.90 | 99.90 | 20 |
| TSHIRT-001-RED-XL | 129.90 | 109.90 | 15 |

## WooCommerce Export Formatı

WooCommerce'den dışa aktarılan dosyalarda:

```csv
Kimlik,Tür,Stok kodu (SKU),İsim,Normal fiyat,İndirimli satış fiyatı,Stok,Ebeveyn,Nitelik 1 ismi,Nitelik 1 değer(ler)i
,variable,TSHIRT-001,Basic T-Shirt,,,100,,Renk,"Kırmızı, Mavi"
,variation,TSHIRT-001-RED,Basic T-Shirt - Kırmızı,99.90,79.90,50,TSHIRT-001,Renk,Kırmızı
```

## Sık Yapılan Hatalar

### ❌ Parent SKU Eksik
```csv
Basic T-Shirt - Kırmızı;TSHIRT-RED;99.90;variation;;Renk;Kırmızı
```
**Çözüm:** `parent_sku` alanını ekleyin.

### ❌ Ana Ürün Yok
Varyasyonları eklemeden önce ana ürünü eklemelisiniz.

### ❌ Öznitelik Uyumsuzluğu
Ana üründeki öznitelik adları ile varyasyondakiler aynı olmalı.

## İpuçları

1. **Sıralama**: Önce tüm ana ürünleri, sonra varyasyonları ekleyin
2. **SKU Formatı**: Tutarlı bir SKU formatı kullanın (örn: URUN-RENK-BEDEN)
3. **Stok**: Her varyasyonun stok miktarını ayrı belirtin
4. **Fiyat**: Varyasyona özel fiyat yoksa boş bırakın, ana ürün fiyatı kullanılır
