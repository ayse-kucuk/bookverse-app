# Bookverse

Sosyal okuma platformu — kitap keşfet, rafına ekle, düşüncelerini paylaş, arkadaşlarını takip et. Goodreads tarzı bir deneyim; AI destekli kişiselleştirilmiş kitap önerileri ile.

## Özellikler

- **Akış** — Alıntı ve düşünce paylaşımları, beğeni, yorum
- **Raflar** — Okuyorum / Okuyacağım / Okudum
- **Keşfet & Ara** — Kitap keşfi ve arama
- **Profil & takip** — Kullanıcı profilleri, takip sistemi, yıllık okuma hedefi
- **Bildirimler** — Beğeni, yorum ve takip bildirimleri
- **AI öneriler** — Gemini ile ruh hali / tür / serbest istek üzerinden kitap önerisi
- **2FA** — Google Authenticator ile çift aşamalı doğrulama
- **Admin paneli** — Kitap, kategori, kullanıcı ve yorum yönetimi; Google Books ile kitap arama
- **API** — Sanctum ile REST API + Swagger dokümantasyonu

## Teknolojiler

| Katman | Teknoloji |
|--------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Blade, Tailwind CSS, Vite, Alpine.js |
| Auth | Laravel Breeze, Sanctum, Google2FA |
| Veritabanı | PostgreSQL (Supabase) / SQLite (lokal) |
| Depolama | Supabase Storage / local public disk |
| AI | Google Gemini API |
| Kitap verisi | Google Books API |
| Deploy | Render (canlı), Laravel Herd (lokal) |

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
|----------|---------|
| `GOOGLE_BOOKS_API_KEY` | Admin kitap arama / import |
| `GEMINI_API_KEY` | AI kitap önerileri |
| `GEMINI_MODEL` | Varsayılan: `gemini-flash-latest` |
| `PROFILE_PHOTOS_DISK` | `public` (lokal) veya `supabase` (canlı) |
| `AWS_*` | Supabase S3 uyumlu profil fotoğrafı ayarları |

## API dokümantasyonu

Swagger UI (ortamda aktifse): `/api/documentation`

## Lisans

MIT
