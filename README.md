# Bookverse

Sosyal okuma platformu — kitap keşfet, rafına ekle, düşüncelerini paylaş, arkadaşlarını takip et. Goodreads tarzı bir deneyim; AI destekli kişiselleştirilmiş kitap önerileri ile.

**Canlı demo:** [bookverse-app.onrender.com](https://bookverse-app.onrender.com)  
**Repo:** [github.com/ayse-kucuk/bookverse-app](https://github.com/ayse-kucuk/bookverse-app)

## Özellikler

- **Akış** — Alıntı ve düşünce paylaşımları, beğeni, yorum
- **Raflar** — Okuyorum / Okuyacağım / Okudum
- **İncelemeler** — Kitap detayında yıldız + kısa inceleme (kullanıcı başına bir)
- **Keşfet & Ara** — Kitap keşfi, gelişmiş arama filtreleri (kategori, puan, sıralama)
- **Profil & takip** — Kullanıcı profilleri, takip sistemi, yıllık okuma hedefi
- **Bildirimler** — Beğeni, yorum ve takip bildirimleri
- **AI öneriler** — Gemini ile ruh hali / tür / serbest istek; off-topic istek koruması
- **2FA** — Google Authenticator ile çift aşamalı doğrulama
- **Admin paneli** — Kitap, kategori, kullanıcı ve yorum yönetimi; Google Books ile kitap arama
- **API** — Sanctum ile REST API + Swagger dokümantasyonu
- **Çoklu dil** — Türkçe / English (navbar’da TR | EN); EN modunda paylaşım, yorum, kitap açıklaması ve kategori adları Grok API ile otomatik çevrilir (cache’lenir)
- **Karanlık mod** — Açık / koyu tema seçeneği (localStorage)
- **SEO** — Meta/OG, JSON-LD, slug URL, sitemap, robots.txt
- **Cache** — Keşfet kategorileri ve AI önerileri (10 dk)

## Demo hesapları

`php artisan migrate --seed` sonrası kullanılabilir:

| E-posta | Şifre | Açıklama |
|---------|-------|----------|
| `demo@bookverse.app` | `password` | Demo okuyucu (akış, raflar, incelemeler) |
| `ayse@example.com` | `password123` | Örnek kullanıcı |

## Teknolojiler

| Katman | Teknoloji |
|--------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Blade, Tailwind CSS, Vite, Alpine.js (mobil uyumlu) |
| Auth | Laravel Breeze, Sanctum, Google2FA |
| Veritabanı | PostgreSQL (Supabase) / SQLite (lokal) |
| Depolama | Supabase Storage / local public disk |
| AI | Google Gemini API |
| Kitap verisi | Google Books API |
| Deploy | Render (canlı), Laravel Herd (lokal) |
| CI | GitHub Actions (`php artisan test`) |

## Kurulum (lokal)

```bash
git clone https://github.com/ayse-kucuk/bookverse-app.git
cd bookverse-app
composer install
cp .env.example .env
php artisan key:generate
```

`.env` içinde en az şunları ayarla:

```env
APP_NAME=Bookverse
DB_CONNECTION=pgsql   # veya sqlite
GOOGLE_BOOKS_API_KEY=
APP_LOCALE=tr
APP_FALLBACK_LOCALE=tr
GEMINI_API_KEY=
GEMINI_MODEL=gemini-flash-latest
```

```bash
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Herd kullanıyorsan `php artisan serve` yerine siteyi doğrudan Herd üzerinden açabilirsin.

## Ortam değişkenleri (özet)

| Değişken | Açıklama |
|----------|----------|
| `GOOGLE_BOOKS_API_KEY` | Admin kitap arama / import |
| `GEMINI_API_KEY` | AI kitap önerileri (**Render’da da tanımlanmalı**) |
| `GEMINI_MODEL` | Varsayılan: `gemini-flash-latest` |
| `XAI_API_KEY` | Grok ile dinamik içerik çevirisi (EN modu) |
| `GROK_MODEL` | Varsayılan: `grok-4.5` |
| `PROFILE_PHOTOS_DISK` | `public` (lokal) veya `supabase` (canlı) |
| `AWS_*` | Supabase S3 uyumlu profil fotoğrafı ayarları |

## SEO

- Meta description + Open Graph / Twitter Card (`partials/head`)
- Kitap sayfalarında JSON-LD (`Book` schema)
- Slug URL: `/books/kitap-adi-yazar` (eski `/books/{id}` 301 yönlendirilir)
- `/sitemap.xml` ve `/robots.txt` (admin / özel sayfalar engelli)

## Testler

```bash
php artisan test
```

## Lisans

MIT
