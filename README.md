
# thumb.php - Dinamik Görsel İşleme Sistemi

thumb.php, PHP 8+ sürümleriyle tam uyumlu, güvenli ve hafif bir görsel yeniden boyutlandırma aracıdır.  
Web sitelerinde görselleri dinamik olarak boyutlandırmak, optimize etmek ve watermark eklemek için tasarlanmıştır.

## Özellikler

- PHP 8+ uyumlu ve güvenli (hata oluştuğunda php_error.log dosyası oluşturur)
- Otomatik orantılı kırpma (object-fit: cover mantığı)
- Akıllı cache sistemi (7 gün sonra otomatik temizleme)
- Saydamlık, renk ve yazı konumu desteği
- WebP formatında çıktı (yüksek performans)
- TrueType/OpenType font desteği (TTF, OTF)

## Gereksinimler

- PHP 8.0 veya üzeri
- `gd` eklentisi etkin olmalı (`php.ini` içinde `extension=gd` satırı açık)
- `cache/` klasörü yazılabilir olmalı

---

## Kullanım Örnekleri

### Basit kullanım
Sadece görseli yeniden boyutlandırır.  
```html
<img src="thumb.php?src=upload/image/ornek.jpg&w=600&h=400" alt="resminiz">
```

---

### Watermark ekleme
Resmin üzerine yazı (watermark) ekler.  
Yazı, otomatik olarak görsele sığacak şekilde boyutlandırılır.
```html
<img src="thumb.php?src=upload/image/ornek.jpg&w=800&h=500&text=Siberp Bilisim" alt="resminiz">
```

---

### Renkli watermark
Varsayılan renk beyazdır. `color` parametresiyle istenen renk ayarlanabilir.
```html
<img src="thumb.php?src=upload/image/ornek.jpg&w=800&h=500&text=Siberp Bilisim&color=BA2600" alt="resminiz">
```

---

### Konum ve saydamlık
`pos` watermark konumunu belirler, `opacity` saydamlık oranını ayarlar.  
`opacity=100` tamamen görünür, `opacity=0` görünmez.
```html
<img src="thumb.php?src=upload/image/ornek.jpg&w=800&h=500&text=Siberp Bilisim&pos=tr&opacity=40" alt="resminiz">
```

---

### Özel font boyutu
Yazı boyutunu manuel olarak belirlemek istersen `size` parametresini kullanabilirsin.
```html
<img src="thumb.php?src=upload/image/ornek.jpg&w=800&h=500&text=Siberp Bilisim&size=50" alt="resminiz">
```

---

### Tam ortalanmış watermark
Watermark’ı tam ortada göstermek için `pos=c` kullanılır.
```html
<img src="thumb.php?src=upload/image/ornek.jpg&w=800&h=500&text=Siberp Bilisim&pos=c&color=ffffff&opacity=70" alt="resminiz">
```

---

## Cache Temizleme Mantığı

Her istek sonrası sistem `cache/` klasörünü kontrol eder ve:
- `.webp` veya `.php` uzantılı dosyalardan,  
- **7 günden eski** olanları otomatik siler.

Bu işlem sunucu performansını etkilemez, çünkü yalnızca dosya zaman damgası ile kontrol edilir.  
Yani cache sürekli taze kalır ve gereksiz dosya birikmez.

---

## Font Ayarları

- Varsayılan font: **Exo-Black-Italic.otf**  
- Font dosyası `thumb.php` ile aynı dizinde bulunmalıdır.  
- Kendi fontunu kullanmak istersen:
  ```php
  $fontFile = __DIR__ . '/fonts/MyFont.ttf';
  ```

---

## Geliştirme Önerileri (keyfime bağlı)

İlerde eklenebilecek bazı parametre fikirleri:

| Parametre          | Açıklama                                                             |
|--------------------|----------------------------------------------------------------------|
| `fit=contain`      | Görseli kırpmadan orantılı şekilde küçültür                          |
| `bg=#FFFFFF`       | Arka plan rengi belirler (özellikle PNG → JPG geçişlerinde)          |
| `rotate` / `flip`  | Görseli döndürür veya yansıtır                                       |
| `cacheclear=1`     | Manuel cache temizleme                                               |
| `filter=grayscale` | Filtre uygulayarak görsel efetk tanımlama (grayscale, sepia, invert) |

---

## Sonuç

**thumb.php**, tek bir PHP dosyasıyla çok güçlü bir görsel yönetim altyapısı sunar:

- Görseller dinamik olarak boyutlandırılır  
- WebP formatı sayesinde sayfa yükleme hızı artar  
- Tasarımlar CSS ve JS karmaşasından kurtulur  
- Watermark işlemi güvenli, esnek ve profesyoneldir  

---

**Sürüm:** 1.2  
**Yazar:** turanhalil541@gmail.com

**PHP sürümü:** 8.0 ve üzeri  
**Lisans:** MIT
