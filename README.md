# thumb.php v2.5

PHP 8.5 versiyonuna göre güncellendi. artık php 8.5.x için sorunsuz olarak çalışır.

Gelişmiş dinamik görsel işleme betiği. PHP GD eklentisiyle çalışır. Cache, watermark, transparan destek, border-radius ve object-fit özellikleriyle optimize edilmiştir.

---
##  Özellikler
- PNG & WebP transparan desteği  
- Gün bazlı cache süresi (`&cache=7`)  
- Object-fit desteği (`cover`, `contain`, `fill`, `none`, `scale-down`)  
- Watermark (yazı ekleme, renk, opaklık, font boyutu)  
- Border-radius (tek tek köşe desteği)  
- Güvenli cache dizini (`.htaccess`, `index.html`)


##  Parametreler

| Parametre | Açıklama                                  | Örnek                 |
|-----------|-------------------------------------------|-----------------------|
| `src`     | Görsel yolu                               | `upload/image.jpg`    |
| `w`       | Genişlik                                  | `400`                 |
| `h`       | Yükseklik                                 | `300`                 |
| `q`       | Kalite (1–100)                            | `90`                  |
| `text`    | Watermark metni                           | `karademirmakina.com` |
| `pos`     | Yazı konumu (`tl`, `tr`, `bl`, `br`, `c`) | `br`                  |
| `size`    | Font boyutu                               | `24`                  |
| `opacity` | Saydamlık (0–1)                           | `0.5`                 |
| `color`   | Yazı rengi (HEX)                          | `#BA2600`             |
| `cache`   | Cache süresi (gün)                        | `7`                   |
| `of`      | Object-fit tipi                           | `cover`               |
| `br`      | Border-radius (tek veya 4 değer)          | `12` veya `12,12,0,0` |

---

##  Kullanım Örnekleri

### Basit boyutlandırma
```html
<img src="thumb.php?src=upload/image.jpg&w=400&h=300">
```

### Watermark ekleme
```html
<img src="thumb.php?src=upload/image.jpg&w=400&h=300&text=karademirmakina.com&pos=br&opacity=0.6&color=#BA2600">
```

### Object-fit kullanımı
```html
<img src="thumb.php?src=upload/image.jpg&w=400&h=300&of=cover">
```

### Cache süresi belirleme (gün)
```html
<img src="thumb.php?src=upload/image.jpg&w=400&h=300&cache=7">
```

### Border-radius
```html
<img src="thumb.php?src=upload/image.jpg&w=400&h=300&br=12,12,0,0">
```

---

##  Güvenlik

Cache klasörü otomatik oluşturulur ve aşağıdaki dosyalar eklenir:

```
/thumb_cache/
 ├── .htaccess
 ├── index.html
 ├── 9d1c0a9a8.webp
```

**.htaccess içeriği:**
```
Options -Indexes
<FilesMatch "\.(php|phtml|php3|php4|php5|phps)$">
Deny from all
</FilesMatch>
```

---

##  Gereksinimler

- PHP 8.0+  
- GD kütüphanesi etkin olmalı  
- WebP desteği aktif olmalı (`imagewebp()`)

---

##  Versiyon Bilgisi

**thumb.php v2.0**
- Transparan destek (PNG/WebP)
- Gün bazlı cache
- Border-radius köşe desteği
- Object-fit genişletilmiş
- Watermark iyileştirmeleri
- Güvenli cache dizini

---

Yazar: turanhalil541@gmail.com
PHP sürümü: 8.0 ve üzeri
Lisans: MIT
