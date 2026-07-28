# STAJ DEFTERİ — BOOKVERSE PROJESİ

## FAZ 1: GİRİŞ & ALTYAPI (Gün 1 – Gün 5)

---

### Gün 1: Proje Tanımı, Geliştirme Ortamının Kurulumu ve MVC Mimarisinin Planlanması

- **Teorik Bilgi ve Amaç:** Staj sürecinin ilk gününde, geliştirilecek olan "Bookverse" adlı sosyal okuma platformunun kapsamı belirlenmiş ve proje gereksinim analizi yapılmıştır. Bookverse; kullanıcıların kitap keşfedebildiği, okuma raflarını yönetebildiği ve düşüncelerini paylaşabildiği Goodreads benzeri bir web uygulaması olarak tasarlanmıştır. Projenin sunucu taraflı (backend) geliştirmesi için PHP dilinin modern bir framework'ü olan Laravel 12 tercih edilmiştir. Bu tercihin temel gerekçesi, Laravel'in sunduğu Model-View-Controller (MVC) mimari desenidir; bu desen, iş mantığı (Model), kullanıcı arayüzü (View) ve akış kontrolü (Controller) katmanlarını birbirinden ayırarak kodun okunabilirliğini, test edilebilirliğini ve sürdürülebilirliğini artırmaktadır. Ayrıca Laravel'in Eloquent ORM (Object-Relational Mapping) katmanı sayesinde veritabanı işlemlerinin nesne yönelimli biçimde, SQL sorgusu yazmaya gerek kalmadan yürütülebileceği öngörülmüştür. Bu ilk gün, projenin geri kalanı boyunca uyulacak dizin ve isimlendirme standartlarının belirlendiği bir planlama aşaması olarak da değerlendirilmiştir; zira büyüyecek bir kod tabanında baştan tutarlı bir mimari kurmanın, ilerleyen fazlarda ortaya çıkabilecek teknik borcu (technical debt) en aza indireceği öngörülmüştür.

- **Teknik Uygulama ve Mimari Kararlar:** Geliştirme ortamı olarak Windows üzerinde çalışan Laravel Herd kullanılmış, `composer create-project laravel/laravel` komutu ile proje iskeleti oluşturulmuştur. Ardından `.env` dosyası `.env.example` referans alınarak yapılandırılmış ve `php artisan key:generate` komutu ile uygulamanın oturum ve şifreleme işlemlerinde kullanılacak `APP_KEY` değeri üretilmiştir. Proje kimliği `APP_NAME=Bookverse` olarak güncellenmiş, `APP_LOCALE=tr` ve `APP_FALLBACK_LOCALE=tr` ayarları ile arayüzün Türkçe temel alınacağı, çeviri dosyalarının ise `lang` dizininde tutulacağı belirlenmiştir. `composer.json` dosyasında `laravel/framework: ^12.0` ve `php: ^8.2` bağımlılıkları tanımlanarak proje standardı sabitlenmiştir.

```env
APP_NAME=Bookverse
APP_LOCALE=tr
DB_CONNECTION=sqlite
```

Bu aşamada klasör yapısı incelenmiş; `app/Models`, `app/Http/Controllers` ve `resources/views` dizinlerinin projenin ana iskeletini, `routes/web.php` dosyasının ise dış dünyaya açılan URL haritasını oluşturacağı planlanmıştır. Sürüm kontrolü için Git deposu başlatılarak ilk commit atılmış, böylece projenin gelişim süreci en baştan itibaren izlenebilir hale getirilmiştir. Ayrıca ön yüz derleme aracı olarak Vite'ın seçilmiş olması, Laravel Mix'e kıyasla çok daha hızlı geliştirme sunucusu başlatma ve anlık modül yenileme (Hot Module Replacement) sunması nedeniyle tercih edilmiş; `vite.config.js` dosyası üzerinden `resources/css/app.css` ve `resources/js/app.js` giriş noktaları tanımlanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Yerel ortamda PHP sürüm uyumsuzluğu nedeniyle bazı Composer paketlerinin kurulumunda bağımlılık çakışması (dependency conflict) yaşanmıştır. Sorun, `composer.json` içerisindeki `php` gereksinim aralığının `^8.2` olarak sabitlenmesi ve Herd üzerinden ilgili PHP sürümünün projeye atanmasıyla giderilmiştir. Bu deneyim, bağımlılık yönetiminin proje başında doğru kurgulanmasının ileride oluşabilecek versiyon çatışmalarını önlediğini somut biçimde göstermiştir.

---

### Gün 2: Kimlik Doğrulama Altyapısının Kurulması ve Kullanıcılar Tablosunun Tasarımı

- **Teorik Bilgi ve Amaç:** İkinci günün amacı, uygulamanın temel taşı olan kullanıcı kimlik doğrulama (authentication) sisteminin kurulmasıdır. Bir sosyal platformda oturum yönetimi, güvenlik açısından en kritik bileşendir; zira takip sistemi, yorum yazma ve rafa kitap ekleme gibi neredeyse tüm işlevler doğrudan "kimliği doğrulanmış kullanıcı" kavramına bağımlıdır. Bu nedenle Laravel'in resmî kimlik doğrulama iskeleti olan Laravel Breeze paketi entegre edilmiştir. Breeze; kayıt, giriş, şifre sıfırlama ve e-posta doğrulama akışlarını, Blade şablon motoru ile hazır biçimde sunmaktadır. Ayrıca Laravel'in `Authenticatable` sözleşmesi (contract) incelenmiş; parola bilgisinin veritabanında düz metin yerine `bcrypt` algoritmasıyla tek yönlü hash'lenerek saklanmasının, olası bir veri sızıntısında kullanıcı güvenliğini koruyan kriptografik bir önlem olduğu kavranmıştır. Bu katman, ilerleyen fazlarda eklenecek olan takip sistemi, bildirimler ve iki faktörlü doğrulama gibi özelliklerin üzerine inşa edileceği temel kimlik altyapısını oluşturmuştur.

- **Teknik Uygulama ve Mimari Kararlar:** `php artisan breeze:install` komutu çalıştırılarak `resources/views/auth` altında giriş (`login.blade.php`) ve kayıt (`register.blade.php`) görünümleri, `app/Http/Controllers/Auth` altında ise `RegisteredUserController` ve `AuthenticatedSessionController` sınıfları otomatik üretilmiştir. `0001_01_01_000000_create_users_table.php` migration dosyası düzenlenerek `users` tablosuna `name`, `email` (unique kısıtıyla), `password` ve `remember_token` sütunları tanımlanmıştır. `routes/auth.php` dosyasında ilgili kontrolörlere bağlanan `GET`/`POST` rotaları oluşturularak kimlik doğrulama akışının uç noktaları (endpoints) belirlenmiştir. `App\Models\User` sınıfı `Illuminate\Foundation\Auth\User` sınıfından türetilerek `Notifiable` ve `HasApiTokens` özellikleri (trait) sisteme dahil edilmiştir.

```php
class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;
}
```

Böylece kullanıcı modeli, ileride REST API katmanında Sanctum token üretebilecek ve bildirim gönderebilecek şekilde baştan hazırlanmıştır. Oturum verilerinin saklanması için `sessions` tablosu da aynı migration içinde `session` sürücüsüyle uyumlu biçimde oluşturulmuştur. Ayrıca şifre sıfırlama akışının ihtiyaç duyduğu `password_reset_tokens` tablosu da bu aşamada tanımlanarak, `ForgotPasswordController` ve `ResetPasswordController` mantığının veri katmanı önceden hazırlanmıştır. Middleware katmanında `auth` ve `guest` ara katmanlarının rotalara nasıl uygulanacağı da bu gün incelenmiş, korumalı sayfalara erişimin merkezi biçimde denetlenmesi hedeflenmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Breeze kurulumu sonrasında Vite ile derlenen ön yüz varlıklarının (assets) tarayıcıda yüklenmediği ve sayfaların biçimlendirmeden yoksun göründüğü tespit edilmiştir. Sorunun `npm install` sonrasında geliştirme sunucusunun (`npm run dev`) çalıştırılmamasından kaynaklandığı anlaşılmış, Vite yeniden başlatılarak Hot Module Replacement mekanizmasının aktif hale gelmesi ve Tailwind sınıflarının doğru biçimde derlenmesi sağlanmıştır.

---

### Gün 3: Veritabanı İlişkisel Modelinin Tasarımı — Kategori ve Kitap Varlıkları

- **Teorik Bilgi ve Amaç:** Üçüncü gün, uygulamanın merkezi varlığı olan "kitap" kavramının veritabanı düzeyinde modellenmesine ayrılmıştır. İlişkisel veritabanı tasarımı prensipleri doğrultusunda, bir kitabın mutlaka bir kategoriye ait olması gerektiği öngörülmüş ve bu durum bire-çok (one-to-many) ilişki olarak kurgulanmıştır. Bu kararın teorik dayanağı, veri normalizasyonudur: kategori bilgisinin her kitap satırında tekrar edilmesi yerine ayrı bir `categories` tablosunda tutulup yabancı anahtar (foreign key) ile ilişkilendirilmesi, veri tutarlılığını ve güncelleme kolaylığını artırmaktadır. Ayrıca referans bütünlüğünü (referential integrity) korumak amacıyla `onDelete('cascade')` kısıtı kullanılması gerektiği değerlendirilmiştir; böylece bir kategori silindiğinde ona bağlı kitapların veritabanında tutarsız (yetim) kayıt olarak kalması engellenmektedir. Bu gün ayrıca Eloquent'in "Convention over Configuration" (yapılandırma yerine kural) felsefesinin uygulamaya nasıl yansıdığı da gözlemlenmiştir; sınıf isimlerinden tablo isimlerinin, ilişki metot isimlerinden ise yabancı anahtar sütunlarının otomatik olarak çıkarsanması, geliştiriciyi tekrarlayan yapılandırma yükünden kurtarmaktadır.

- **Teknik Uygulama ve Mimari Kararlar:** `2026_06_01_054342_create_categories_table.php` migration dosyası ile `categories` tablosu, `2026_06_30_062618_create_books_table.php` ile de `books` tablosu oluşturulmuştur. `categories` tablosuna `name` ve isteğe bağlı bir `description` sütunu eklenmiş, `books` tablosuna ise `title`, `author`, `page_count` ve `cover_image` sütunlarının yanı sıra `category_id` sütunu `foreignId()->constrained()->onDelete('cascade')` ifadesiyle eklenmiştir. Model katmanında `App\Models\Category` sınıfına `books(): HasMany` ilişkisi, `App\Models\Book` sınıfına ise `category(): belongsTo` ilişkisi tanımlanmıştır. Her iki modelde de toplu veri atamasına (mass assignment) karşı koruma sağlamak amacıyla `$fillable` dizileri açıkça tanımlanmıştır.

```php
$table->foreignId('category_id')->constrained()->onDelete('cascade');
```

Bu yapı sayesinde `Book::with('category')` gibi Eloquent sorgularıyla N+1 sorgu problemi önlenerek, kitap listeleme ekranlarında performanslı veri çekimi hedeflenmiştir. Ayrıca `page_count` sütununun tamsayı (integer) tipinde, `cover_image` ve `description` sütunlarının ise `nullable()` olarak tanımlanması, veri girişinde esneklik sağlanması amacıyla bilinçli olarak tercih edilmiştir; bu sayede kapak görseli henüz temin edilmemiş bir kitap kaydının da sisteme girilebilmesi mümkün kılınmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Migration dosyalarının çalıştırılma sırasının önemi bu aşamada net biçimde deneyimlenmiştir; `books` tablosu migration'ı `categories` tablosundan önce çalıştırılmaya çalışıldığında "foreign key constraint" hatası alınmıştır, çünkü var olmayan bir tabloya referans verilmesi veritabanı motoru tarafından reddedilmektedir. Sorun, migration dosya adlarındaki tarih damgalarının (timestamp) kronolojik sıraya göre düzenlenmesiyle, yani `categories` migration'ının `books` migration'ından önce çalışacak şekilde adlandırılmasıyla çözülmüştür.

---

### Gün 4: Kullanıcı-Kitap Çoktan-Çoğa İlişkisi ve Okuma Rafı Sisteminin Temeli

- **Teorik Bilgi ve Amaç:** Dördüncü günün odağı, bir kullanıcının birden fazla kitabı, bir kitabın da birden fazla kullanıcı tarafından rafa eklenebilmesi gereksinimidir. Bu gereksinim, "Raflar" özelliğinin (Okuyorum / Okuyacağım / Okudum) veri modeli düzeyindeki karşılığıdır ve projenin sosyal okuma platformu olma vizyonunun teknik omurgasını oluşturmaktadır. Bu senaryo, ilişkisel veritabanı teorisinde çoktan-çoğa (many-to-many) ilişki olarak tanımlanır ve doğrudan iki tablo arasında kurulamayacağı için ara (pivot/junction) tablo kullanımını zorunlu kılar. Bu mimari karar, Eloquent ORM'in `belongsToMany()` metodunun teorik temelini oluşturmaktadır. Ayrıca pivot tabloya yalnızca ilişkiyi kuran anahtarların değil, ilişkiye özgü ek verilerin (örneğin okuma durumu ve puan) de eklenebileceği öngörülmüş, bu ihtiyaç Eloquent'in `withPivot()` mekanizması ile karşılanmıştır. Böylece "raf" kavramı, salt bir ilişki değil, durum (state) taşıyan bağımsız bir varlık olarak modellenmiştir; bu yaklaşım, ilerleyen fazlarda eklenecek "okundu/okunuyor/okunacak" filtrelemesinin veritabanı seviyesinde temiz biçimde sorgulanabilmesinin de zeminini hazırlamıştır.

- **Teknik Uygulama ve Mimari Kararlar:** `2026_07_01_070946_create_book_users_table.php` migration'ı ile pivot yapı ilk kez oluşturulmuş, `user_id` ve `book_id` sütunları yabancı anahtar olarak `constrained()->onDelete('cascade')` ile tanımlanmıştır. `status` sütununa `plan_to_read` varsayılan değeri atanarak yeni eklenen her kitabın başlangıçta "okuyacağım" rafında konumlanması sağlanmıştır. Model tarafında `User::books()` ve `Book::users()` ilişkileri karşılıklı olarak `belongsToMany` ile kurulmuş, `withTimestamps()` çağrısı sayesinde pivot satırının ne zaman oluşturulduğu bilgisi de otomatik tutulmuştur. Bu tasarım, `App\Models\BookUser` adında ayrı bir ara model sınıfının da ileride pivot üzerinde doğrudan sorgu yapılabilmesi için hazırlanmasına zemin oluşturmuştur. Aynı çoktan-çoğa deseni, ilerleyen fazlarda kullanıcıdan kullanıcıya kendine referanslı bir ilişki olan `follows` pivot tablosunda da tekrar kullanılmış; `User::followers()` ve `User::following()` ilişkileri aynı `belongsToMany` deseniyle inşa edilmiştir.

```php
public function books() {
    return $this->belongsToMany(Book::class, 'book_user')
        ->withPivot(['status', 'rating'])->withTimestamps();
}
```

Bu ilişki üzerinden `$user->books()->attach()` çağrısıyla bir kitabın kullanıcı rafına eklenmesi, `syncWithoutDetaching()` gibi metotlarla da mevcut ilişkinin bozulmadan güncellenmesi mümkün kılınmıştır. Bu tasarım tercihi, aynı kullanıcı-kitap çiftinin rafta yalnızca tek bir satır olarak tutulmasını, fakat bu satırın durumunun zaman içinde "okuyacağım" değerinden "okuyorum" ve "okudum" değerlerine evrilebilmesini sağlamıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Laravel'in çoktan-çoğa ilişkilerde varsayılan olarak tabloları alfabetik sırayla birleştirip tekil (singular) isimlendirme beklediği (`book_user`) fark edilmiş, ilk oluşturulan `book_users` (çoğul) tablo adının Eloquent kuralıyla örtüşmediği ve isim çakışmasına yol açtığı görülmüştür. Bu problem, Eloquent kuralına uygun `book_user` adında yeni bir pivot tablosu tanımlanarak ve ilişki metotlarında tablo adının ikinci parametre olarak açıkça belirtilmesiyle giderilmiştir. Bu deneyim, framework kurallarına (convention) uyumun yalnızca kod kısalığı için değil, ekip içi okunabilirlik ve gelecekteki bakım kolaylığı için de kritik olduğunu göstermiştir. Bugün kurulan `status = 'okundu'` alanı, ilerleyen fazlarda `reading_goal` sütunuyla birleşerek yıllık okuma hedefi hesaplamasının veri temelini oluşturmuştur.

---

### Gün 5: Yorum/İnceleme Modülünün Temellendirilmesi ve Bulut Veritabanına (Supabase/PostgreSQL) Geçiş

- **Teorik Bilgi ve Amaç:** Altyapı fazının son gününde, kullanıcıların kitaplar hakkında görüş bildirebileceği inceleme sisteminin veri modeli kurgulanmış ve geliştirme ortamının kalıcı, ölçeklenebilir bir veritabanı sistemine taşınması planlanmıştır. Yorum verisi de kitap-kullanıcı ilişkisine benzer biçimde iki varlığa bağımlı olduğundan, `comments` tablosu hem `books` hem `users` tablolarına yabancı anahtarla bağlanan bağımlı (dependent) bir varlık olarak tasarlanmıştır. Ayrıca yerel geliştirmede kullanılan dosya tabanlı SQLite veritabanının, çoklu eşzamanlı bağlantı ve yüksek erişilebilirlik gerektiren üretim (production) ortamı için yetersiz kalacağı değerlendirilmiş; bu nedenle nesne-ilişkisel bir veritabanı yönetim sistemi olan PostgreSQL'in, Supabase altyapısı üzerinden yönetilen bir bulut servisi (managed database) biçiminde projeye entegre edilmesine karar verilmiştir. Bu geçiş, projenin "geliştirme ortamı" ile "canlı ortam" arasındaki farkı ilk kez somut biçimde deneyimlediği aşama olmuştur.

- **Teknik Uygulama ve Mimari Kararlar:** `2026_07_01_060919_create_comments_table.php` migration'ı ile `comments` tablosu oluşturulmuş; `content` (metin), `book_id`, `user_id` ve `rating` sütunları tanımlanmıştır. `App\Models\Comment` sınıfında `book(): belongsTo` ve `user(): belongsTo` ilişkileri kurularak bir yorumun tam olarak bir kitaba ve bir kullanıcıya ait olacağı garanti altına alınmıştır. Yorumun kendisinin de sosyal bir etkileşim nesnesi olabileceği öngörülerek `Comment::likes(): HasMany` ilişkisi tanımlanmış, ileride bu ilişkiyi somutlaştıracak ayrı bir `comment_likes` tablosu için zemin bırakılmıştır. Ardından `config/database.php` içerisindeki `pgsql` bağlantı bloğu incelenmiş, Supabase'in sağladığı `DB_HOST`, `DB_PORT`, `DB_DATABASE` ve `sslmode=require` bilgileri `.env` dosyası üzerinden tanımlanarak `DB_CONNECTION=pgsql` değeriyle etkinleştirilmiştir. Bu sayede kaynak kod hiç değişmeden, yalnızca ortam değişkenleri güncellenerek uygulamanın SQLite yerine PostgreSQL üzerinde çalışması sağlanmış; bu durum Laravel'in veritabanı sürücüsünü soyutlayan (abstraction) mimarisinin pratikteki faydasını göstermiştir.

```php
$table->foreignId('book_id')->constrained()->onDelete('cascade');
$table->foreignId('user_id')->constrained()->onDelete('cascade');
```

`php artisan migrate` komutu bulut veritabanına karşı başarıyla çalıştırılarak Faz 1'in altyapı hedefleri tamamlanmış ve `users`, `categories`, `books`, `book_user`, `comments` tabloları canlı ortamda hazır hale getirilmiştir. Bu aşamada ayrıca `rating` sütununun `nullable()` tanımlanması, bir kullanıcının sadece yorum yazıp puan vermeden de görüş bildirebilmesine imkân tanımış; ileride yorumların kitap detay sayfasında hem metin hem yıldız gösterimiyle listelenebilmesi için gerekli veri zemini bu günde hazırlanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Supabase'e ilk bağlantı denemesinde SSL sertifika doğrulaması nedeniyle bağlantı reddedilmiştir. Bu durum, PostgreSQL bağlantı dizisine `sslmode=require` parametresinin eksik eklenmesinden kaynaklanmıştır; `config/database.php` içerisindeki `pgsql` bağlantısına bu parametrenin tanımlanmasıyla güvenli (encrypted) bağlantı sağlanmış ve migration işlemleri hatasız biçimde tamamlanmıştır. Bu deneyim, bulut veritabanı servislerinin varsayılan olarak şifreli bağlantı zorunlu kıldığının öğrenilmesini sağlamıştır.

---

## FAZ 2: BACKEND & TEMEL SERVİSLER (Gün 6 – Gün 10)

---

### Gün 6: Controller Katmanının Derinleştirilmesi ve Kitap Kaynağının CRUD Mantığı

- **Teorik Bilgi ve Amaç:** Altıncı günde, MVC mimarisinin "Controller" katmanının sorumluluk sınırları netleştirilmiş ve bu katmanın istemciden (client) gelen HTTP isteğini nasıl işleyip bir yanıta (response) dönüştürdüğü uygulamalı biçimde ele alınmıştır. Bu aşamada "ince controller, şişman model" (skinny controller, fat model) prensibi teorik referans olarak benimsenmiştir; buna göre iş kurallarının mümkün olduğunca Eloquent modelleri içindeki kapsam (scope) ve yardımcı metotlarda toplanması, controller'ın ise yalnızca istek doğrulama, model çağırma ve görünüm/veri döndürme görevini üstlenmesi hedeflenmiştir. Ayrıca Laravel'in Route Model Binding mekanizması incelenmiş; URL parametresinin doğrudan bir Eloquent model örneğine dönüştürülmesinin, tekrar eden `findOrFail()` çağrılarını ortadan kaldıran zarif bir soyutlama olduğu değerlendirilmiştir. Bu gün ayrıca HTTP protokolünün durumsuz (stateless) doğası ile Laravel'in oturum tabanlı "durum" simülasyonu arasındaki ilişki de tartışılmış; her isteğin bağımsız bir yaşam döngüsüne (request lifecycle) sahip olduğu, `$request` nesnesinin bu döngü boyunca tüm girdi verisini taşıyan merkezi bir taşıyıcı olduğu vurgulanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** `app/Http/Controllers/BookController.php` içerisinde `index()` metodu; arama (`q`), kategori filtresi ve sıralama (`sort`) parametrelerini `Request` nesnesinden okuyarak `Book::query()->with('category')->withRatingStats()` sorgusunu koşullu biçimde zenginleştirecek şekilde yazılmıştır. `show()` metodunda ise `Route::get('/books/{book:slug}', ...)` tanımıyla slug tabanlı Route Model Binding kurulmuş, eski sayısal id ile gelen istekler `showLegacy()` üzerinden 301 yönlendirmesiyle yeni adrese taşınmıştır. `storeComment()` ve `updateStatus()` metotlarında `$request->validate()` ile giriş doğrulaması yapılmış, ardından `$user->books()->syncWithoutDetaching()` çağrısıyla pivot tablo güncellenmiştir.

```php
Route::get('/books/{book:slug}', [BookController::class, 'show'])
    ->name('books.show');
```

Bu yaklaşım, SEO dostu ve okunabilir URL yapısı (`/books/kitap-adi-yazar`) ile teknik gereksinim olan veritabanı ilişkisini tek satırda birleştirmiştir. Ayrıca `profile()` metodu, kullanıcının rafındaki kitapları `pivot.status` alanına göre `reading`, `read` ve `willRead` koleksiyonlarına ayırarak görünüme aktarmış; bu sayede aynı veri kümesi üzerinde tekrar sorgu atmadan, PHP koleksiyon metotlarıyla (`where()`) bellek içinde filtreleme yapılmıştır. `destroyComment()` metodunda ise `abort_unless()` yardımcı fonksiyonuyla, yalnızca yorumun sahibinin veya admin yetkisine sahip bir kullanıcının silme işlemi yapabilmesi güvence altına alınmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Bir kullanıcının aynı kitaba hem durum (status) hem puan (rating) bilgisini farklı uç noktalardan güncelleyebilmesi, pivot satırının parçalı biçimde üzerine yazılıp veri kaybına yol açması riskini doğurmuştur. Sorun, her güncelleme öncesinde mevcut pivot kaydının (`$existing?->pivot`) okunup değişmeyen alanların yeni `syncWithoutDetaching()` çağrısına da dahil edilmesiyle, yani durum bilgisinin korunarak yalnızca ilgili alanın güncellenmesiyle çözülmüştür.

---

### Gün 7: Admin Paneli Backend Altyapısı ve Yetkilendirme (Authorization) Katmanı

- **Teorik Bilgi ve Amaç:** Yedinci günün konusu, sistemin normal kullanıcılardan ayrıcalıklı biçimde yönetilebileceği bir admin panelinin backend altyapısının kurulmasıdır. Bu iş, yazılım güvenliğinde rol tabanlı erişim kontrolü (Role-Based Access Control) kavramının basitleştirilmiş bir uygulamasıdır. Laravel'in middleware (ara katman) mimarisi bu noktada teorik temel oluşturmuştur: bir HTTP isteği controller'a ulaşmadan önce bir dizi ara katmandan (soğan kabuğu / onion modeli) geçirilir ve her katman isteği ya bir sonrakine iletir ya da erişimi keserek doğrudan bir yanıt döner. Bu yapı, kimlik doğrulama ve yetkilendirme gibi çapraz kesen (cross-cutting) endişelerin iş mantığından tamamen ayrıştırılmasını sağlayan temiz bir mimari örnek olarak değerlendirilmiştir. Ayrıca yetkilendirmenin (authorization) kimlik doğrulamadan (authentication) kavramsal olarak farklı bir katman olduğu, birinin "kimsin" sorusuna, diğerinin ise "neler yapabilirsin" sorusuna yanıt verdiği ayrımı bu gün netleştirilmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** `users` tablosuna `add_is_admin_to_users_table` migration'ı ile `is_admin` boole sütunu eklenmiş, ardından `app/Http/Middleware/AdminMiddleware.php` sınıfı yazılarak `auth()->check() && auth()->user()->is_admin` koşulu sağlanmayan isteklere `abort(403, ...)` ile erişim reddedilmiştir. Bu ara katman `bootstrap/app.php` dosyasında `admin` takma adıyla (`alias`) kaydedilmiş ve `routes/web.php` içinde `Route::middleware(['auth', 'admin'])->prefix('admin')` grubuna dahil edilmiştir. `App\Http\Controllers\Admin\BookController` sınıfında `store()`, `update()` ve `destroy()` metotları yazılarak tam CRUD döngüsü tamamlanmış; `bookRules()` özel metodunda ise başlık tekrarını önleyen özel bir kapanış (closure) tabanlı doğrulama kuralı tanımlanmıştır. Aynı `admin` ara katmanının koruması altında, `Admin\CategoryController` kategori CRUD'unu, `Admin\UserController` kullanıcı listeleme ve `toggleAdmin()` ile yetki yükseltmesini, `Admin\CommentController` ise uygunsuz yorumların moderasyon amaçlı silinmesini üstlenerek tek bir yetkilendirme deseninin dört ayrı kaynağa tutarlı biçimde uygulandığını göstermiştir.

```php
if (auth()->check() && auth()->user()->is_admin) {
    return $next($request);
}
abort(403, 'Bu alana erişim yetkiniz bulunmamaktadır.');
```

Bu tasarım sayesinde yetkilendirme mantığı tek bir noktadan yönetilebilir hale getirilmiştir. Ek olarak `App\Http\Controllers\Admin\DashboardController` sınıfında `User::count()`, `Book::count()` ve `Comment::count()` gibi toplu (aggregate) sorgularla basit bir yönetim paneli istatistik ekranı hazırlanarak, sistemin genel durumunun tek bakışta görülebilmesi sağlanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Admin panelinden kitap ekleme denemesinde `MassAssignmentException` hatası alınmış, yeni eklenen `image_url` ve `is_protected` alanlarının `App\Models\Book` sınıfındaki `$fillable` dizisinde tanımlı olmadığı tespit edilmiştir. Sorun, ilgili sütun adlarının `$fillable` dizisine eklenmesiyle giderilmiş; bu deneyim, Eloquent'in toplu atama korumasının güvenlik açısından faydalı olsa da yeni sütun eklendikçe model tanımının da güncellenmesi gerektiğini göstermiştir. Ayrıca `destroy()` metodunda kitap silinmeden önce ilişkili yorumların ve kullanıcı rafı bağlantılarının da temizlenmesi gerektiği fark edilmiş, bu işlem `Book::withoutEvents()` sarmalayıcısı içinde yapılarak gereksiz model olaylarının (event) tetiklenmesi engellenmiştir.

---

### Gün 8: Laravel Sanctum ile Token Tabanlı Kimlik Doğrulama Altyapısı

- **Teorik Bilgi ve Amaç:** Sekizinci günde, uygulamanın yalnızca tarayıcı tabanlı oturumlarla değil, mobil istemciler veya harici servislerle de haberleşebilmesi için stateless (durumsuz) bir kimlik doğrulama katmanı planlanmıştır. Geleneksel session tabanlı kimlik doğrulama sunucu hafızasında veya veritabanında oturum durumu tutarken, API tabanlı erişimde her istek kendi kimliğini bir belirteç (token) aracılığıyla taşımalıdır. Bu teorik ihtiyaç, Laravel Sanctum paketinin "personal access token" modeliyle karşılanmıştır: her token, veritabanında hash'lenmiş biçimde saklanan, belirli yeteneklere (abilities) sahip olabilen ve istemci tarafından `Authorization: Bearer` başlığıyla gönderilen bir kimlik kanıtıdır. Sanctum'un tercih edilmesinin bir diğer nedeni, hem SPA (Single Page Application) çerez tabanlı kimlik doğrulamasını hem de klasik API token modelini tek bir pakette birleştiren hafif ve düşük yapılandırma gerektiren mimarisidir; bu da projeye Passport gibi daha ağır bir OAuth2 sunucusu kurmadan yeterli güvenlik seviyesi sağlamıştır.

- **Teknik Uygulama ve Mimari Kararlar:** `composer.json` içine `laravel/sanctum: ^4.0` bağımlılığı eklenmiş, `create_personal_access_tokens_table` migration'ı ile ilgili tablo `morphs('tokenable')` sütunuyla polimorfik biçimde oluşturulmuştur. `App\Models\User` sınıfına `HasApiTokens` trait'i eklenerek her kullanıcı örneğinin `createToken()` metoduna sahip olması sağlanmıştır. `App\Http\Controllers\Api\AuthController` sınıfında `register()` ve `login()` metotları yazılmış; başarılı girişte `$user->createToken('api')->plainTextToken` çağrısıyla üretilen düz metin token istemciye JSON yanıt olarak dönülmüştür. `routes/api.php` dosyasında `auth:sanctum` middleware grubu tanımlanarak `/me`, `/logout` gibi korumalı uç noktalar bu zarfın içine alınmıştır.

```php
$token = $user->createToken('api')->plainTextToken;
return response()->json(['token' => $token]);
```

Bu yapı, web ve API katmanlarının aynı `User` modelini paylaşarak iki farklı kimlik doğrulama stratejisini tek çatı altında yürütmesini sağlamıştır. `login()` metodu ayrıca `hasTwoFactorEnabled()` kontrolüyle zenginleştirilmiş; iki faktörlü doğrulaması açık kullanıcılar için token üretimi ertelenerek `two_factor_required: true` bilgisi döndürülmüştür. Aynı akışın tarayıcı tarafı karşılığı `App\Http\Controllers\Auth\TwoFactorChallengeController` ile kurulmuş, `Auth\TwoFactorSetupController` ise `two-factor-setup.blade.php` görünümünde `TwoFactorService::qrCodeSvg()` ile üretilen SVG karekodu (QR) kullanıcıya göstererek kurulum adımını web oturumu için de mümkün kılmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Çıkış (logout) işleminde yalnızca oturumun sonlandırılmasının yetersiz kaldığı, mevcut token'ın veritabanında geçerli kalmaya devam ettiği fark edilmiştir. Bu durum, `logout()` metodunda `$request->user()->currentAccessToken()->delete()` çağrısı eklenerek çözülmüş; böylece yalnızca o oturuma ait token açıkça iptal edilmiş, kullanıcının diğer cihazlardaki oturumları etkilenmemiştir. Bu deneyim, token tabanlı sistemlerde "çıkış yapma" işleminin, session tabanlı sistemlerdeki gibi örtük değil, açıkça veritabanı seviyesinde gerçekleştirilmesi gereken bir işlem olduğunu göstermiştir.

---

### Gün 9: RESTful API Tasarımı ve OpenAPI/Swagger Dokümantasyonu

- **Teorik Bilgi ve Amaç:** Dokuzuncu günün amacı, uygulamanın iş mantığını dış dünyaya standart ve öngörülebilir bir arayüzle açan RESTful API katmanının tasarlanmasıdır. REST (Representational State Transfer) mimari stili; kaynakların (resource) URI'lerle temsil edilmesini, işlemlerin ise HTTP fiilleriyle (`GET`, `POST`, `DELETE`) ifade edilmesini ve her isteğin durumsuz (stateless) olmasını öngörür. Bu prensipler doğrultusunda `/api/books`, `/api/posts`, `/api/users/{user}` gibi kaynak odaklı uç noktalar tasarlanmıştır. Ayrıca API'lerin insan tarafından da okunabilir, makine tarafından da işlenebilir biçimde belgelenmesi gerektiği değerlendirilmiş; bu amaçla OpenAPI (Swagger) standardının PHP öznitelikleri (attributes) üzerinden otomatik üretilmesi tercih edilmiştir. Bu yaklaşımın klasik, elle yazılan Markdown API dokümantasyonuna kıyasla en büyük avantajı, dokümantasyonun doğrudan kod içinde tanımlanması sayesinde kod ile belge arasında zamanla oluşabilecek tutarsızlığın önlenmesidir.

- **Teknik Uygulama ve Mimari Kararlar:** `darkaonline/l5-swagger` paketi entegre edilmiş, `App\Http\Controllers\Api\BookController` ve `Api\AuthController` sınıflarındaki her metoda `#[OA\Get(...)]` ve `#[OA\Post(...)]` öznitelikleri eklenerek uç nokta, parametre ve olası yanıt kodları (`200`, `404`, `422`) belgelenmiştir. `app/OpenApi/OpenApiSpec.php` dosyasında genel API başlığı ve `sanctum` güvenlik şeması tanımlanmıştır. `Api\BookController::index()` metodunda `match($request->input('sort', 'latest'))` yapısıyla sıralama mantığı, web tarafındaki `BookController` ile tutarlı biçimde tekrar uygulanmıştır. Sayfalama (pagination) için Laravel'in yerleşik `paginate(12)` metodu tercih edilmiş, böylece hem toplam kayıt sayısı hem de sayfa bağlantıları otomatik olarak JSON yanıtın `meta` ve `links` alanlarına eklenmiştir.

```php
#[OA\Get(path: '/books', tags: ['Books'])]
public function index(Request $request): JsonResponse
```

`php artisan l5-swagger:generate` komutuyla üretilen belge, `/api/documentation` adresinde canlı olarak sunulmuş ve `resources/views/vendor/l5-swagger/index.blade.php` üzerinden Swagger UI arayüzü servis edilmiştir. `Api\UserController`, `Api\PostController` ve `Api\NotificationController` sınıfları da benzer şekilde belgelenerek API yüzeyinin bütünlüğü sağlanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Web (`BookController`) ve API (`Api\BookController`) katmanlarında arama, filtreleme ve sıralama mantığının neredeyse birebir tekrar edildiği fark edilmiş, bu durumun kod tekrarına (duplication) yol açtığı değerlendirilmiştir. Kısa vadeli çözüm olarak ortak sorgu mantığı `Book` modelindeki `scopeMatchingSearchTerm()` ve `scopeWithRatingStats()` kapsamlarına taşınmış, böylece her iki controller da aynı Eloquent kapsamlarını çağırarak tutarlılık sağlamıştır. Bu tecrübe, "Don't Repeat Yourself" (DRY) ilkesinin model katmanında sorgu kapsamları (query scopes) aracılığıyla nasıl hayata geçirilebileceğini somut biçimde göstermiştir.

---

### Gün 10: Servis Katmanı (Service Layer) Mimarisi ile İş Mantığının Soyutlanması

- **Teorik Bilgi ve Amaç:** Backend fazının son gününde, harici sistemlerle haberleşen veya karmaşık iş mantığı barındıran kodların controller'lardan ayrıştırılarak bağımsız servis sınıflarına taşınması planlanmıştır. Bu yaklaşım, yazılım mühendisliğindeki Tek Sorumluluk İlkesi'nin (Single Responsibility Principle) doğrudan bir uygulamasıdır: bir sınıfın değişmesi için yalnızca tek bir nedeni olmalıdır. Ayrıca Laravel'in Servis Konteyneri (Service Container) ve bağımlılık enjeksiyonu (Dependency Injection) mekanizması incelenmiş; bir servis sınıfının constructor veya metot parametresi olarak talep edilmesi durumunda framework'ün bu bağımlılığı otomatik olarak çözümleyip (resolve) enjekte ettiği kavranmıştır. Bu mimari desen, ilerleyen fazlarda eklenecek `AiRecommendationService` ve `GoogleBooksCoverResolver` gibi ek servislerin de aynı standarda oturmasını sağlayan bir şablon niteliği taşımıştır.

- **Teknik Uygulama ve Mimari Kararlar:** `app/Services` dizini oluşturularak `TwoFactorService` ve `GoogleBooksService` sınıfları yazılmıştır. `TwoFactorService`, `pragmarx/google2fa` ve `bacon/bacon-qr-code` paketlerini sararak (wrap) `generateSecret()`, `verify()` ve `qrCodeSvg()` gibi yüksek seviyeli metotlar sunmuş; kurtarma kodlarının (`recovery codes`) `hash('sha256', ...)` ile tek yönlü saklanması bu servis içinde standartlaştırılmıştır. `Admin\BookController::searchGoogleBooks()` metodunda ise `GoogleBooksService $googleBooks` parametresi doğrudan metot imzasına eklenerek servis konteynerinin otomatik enjeksiyonundan yararlanılmıştır. Bu sayede aynı servis, ilerleyen fazlarda hem admin panelinden manuel kitap arama hem de otomatik veri zenginleştirme (`GoogleBooksCoverResolver` ile kapak görseli tamamlama) senaryolarında tekrar kullanılabilir hale gelmiştir.

```php
public function searchGoogleBooks(Request $request, GoogleBooksService $googleBooks)
{
    return response()->json($googleBooks->search($request->q));
}
```

Bu mimari sayede controller, dış API çağrısının detaylarından tamamen habersiz kalmış, servis sınıfı ise `GOOGLE_BOOKS_API_KEY` ortam değişkenini ve HTTP istemcisini kendi içinde yöneterek yeniden kullanılabilir hale gelmiştir. Aynı desen, kullanıcının okuma geçmişini ve tercih ettiği ruh hali/türü Gemini API'sine yapılandırılmış bir istem (prompt) olarak ileten `AiRecommendationService` sınıfında da tekrarlanmış; `AiRecommendationController::recommend()` bu servisi enjekte ederek yapay zekâ mantığını HTTP katmanından tamamen soyutlamıştır.

- **Karşılaşılan Zorluk ve Çözüm:** İki faktörlü doğrulama akışında, kullanıcının kimliği henüz tam doğrulanmadan (2FA kodu bekleniyorken) oturum açık bırakılmasının güvenlik açığı oluşturacağı fark edilmiştir. Bu sorun, `TwoFactorService::issuePendingLoginToken()` metoduyla geçici bir "bekleyen giriş" belirtecinin `Cache` üzerinde on dakikalığına saklanması ve asıl oturumun yalnızca doğru kod girildikten sonra `pullPendingLogin()` ile tamamlanmasıyla çözülmüştür. Böylece kimlik doğrulama süreci iki ayrı ve doğrulanabilir aşamaya bölünmüş, ara durumun (intermediate state) veritabanına değil geçici önbelleğe yazılması performans açısından da avantaj sağlamıştır.

---

## FAZ 3: FRONTEND & ARAYÜZ (Gün 11 – Gün 15)

---

### Gün 11: Blade Şablon Motoru ve Bileşen (Component) Tabanlı Arayüz Mimarisi

- **Teorik Bilgi ve Amaç:** On birinci günde, sunum katmanının (presentation layer) nasıl yapılandırılacağı ele alınmış ve Laravel'in Blade şablon motorunun sunduğu bileşen tabanlı mimari benimsenmiştir. Blade, PHP'nin üzerine ince bir katman ekleyerek `{{ }}` kaçış (escaping) sözdizimi, `@if`/`@foreach` direktifleri ve şablon miras alma (`@extends`, `@section`) gibi araçlar sunar; ancak projede tercih edilen yaklaşım, sayfaları büyük tek parça dosyalar yerine küçük, tekrar kullanılabilir bileşenlere (component) ve kısmi görünümlere (partial) bölmektir. Bu, ön yüz dünyasındaki bileşen tabanlı mimarinin (React/Vue benzeri) sunucu taraflı (server-side) bir yorumu olarak değerlendirilmiş; her bileşenin kendi sorumluluğunu taşıması, arayüzün bakımını ve tutarlılığını kolaylaştırmıştır. Bu yaklaşımın bir diğer avantajı, Blade dosyalarının derleme (compile) aşamasında salt PHP koduna dönüştürülüp `storage/framework/views` altında önbelleklenmesi sayesinde, çalışma zamanında ek bir yorumlama (interpretation) maliyeti getirmemesidir.

- **Teknik Uygulama ve Mimari Kararlar:** `resources/views/layouts/app.blade.php` ve `layouts/guest.blade.php` dosyaları iskelet (skeleton) şablonlar olarak tanımlanmış, `{{ $slot }}` değişkeni aracılığıyla alt sayfaların içeriğini kabul etmiştir. `resources/views/components` dizininde `x-input-label`, `x-primary-button`, `x-text-input` ve `x-modal` gibi anonim Blade bileşenleri `@props` direktifiyle parametrize edilmiştir. `resources/views/partials` dizini ise `site-nav`, `post-card`, `stars-input` gibi sayfalar arası paylaşılan ama bağımsız bir bileşen olmayan parçaları barındırmıştır. Ana sayfanın kendisi olan sosyal akış, `FeedController::index()` tarafından takip edilen kullanıcıların `Post` kayıtlarıyla beslenmiş ve `feed.blade.php` görünümünde `@foreach` döngüsüyle art arda dizilen `post-card` bileşenlerine dönüştürülmüştür.

```blade
@props(['name', 'show' => false, 'maxWidth' => '2xl'])
<div x-data="{ show: @js($show) }">{{ $slot }}</div>
```

Bu ayrım, "bileşen" (state ve davranış taşıyan) ile "partial" (yalnızca görünüm parçası) kavramları arasındaki sınırı netleştirmiştir. `layouts/guest.blade.php` dosyası ise kimlik doğrulama sayfaları için ayrı, navigasyon içermeyen sade bir kabuk (shell) sunarak, giriş/kayıt akışının kullanıcı dikkatini dağıtmayan izole bir görsel bağlamda sunulmasını sağlamıştır.

- **Karşılaşılan Zorluk ve Çözüm:** `post-card.blade.php` gibi partial'lerin hem akış (feed) sayfasında hem de kullanıcı profil sayfasında tekrar kullanılması sırasında, `$post` değişkenine bağımlı olan beğeni ve yorum sayaçlarının her bağlamda doğru veri ile beslenmesi gerektiği görülmüştür. Sorun, controller katmanında `Post::withLikeMeta($user)` gibi model kapsamlarıyla gerekli meta verinin (liked_by_viewer, likes_count) sorgu seviyesinde önceden hesaplanıp view'e hazır biçimde aktarılmasıyla çözülmüştür. Bu deneyim, view katmanının veri hazırlama sorumluluğu üstlenmemesi, yalnızca kendisine sunulan hazır veriyi biçimlendirmesi gerektiği ilkesini pekiştirmiştir.

---

### Gün 12: Tailwind CSS Tasarım Sistemi, Tasarım Belirteçleri (Design Tokens) ve Karanlık Mod

- **Teorik Bilgi ve Amaç:** On ikinci günde, arayüzün görsel tutarlılığını sağlayacak bir tasarım sistemi kurgulanmıştır. Tailwind CSS'in "utility-first" (yardımcı sınıf öncelikli) felsefesi benimsenmiş; bu yaklaşımda özel CSS yazmak yerine `flex`, `px-4`, `rounded-2xl` gibi küçük, tek amaçlı sınıflar doğrudan HTML içinde birleştirilir. Ancak markanın kendine özgü renk paletini (sıcak bej tonları, `--bv-accent` gibi) tekrar tekrar yazmamak için CSS özel özellikleri (custom properties / design tokens) katmanı eklenmesi gerektiği değerlendirilmiştir. Ayrıca karanlık mod (dark mode) desteğinin, Tailwind'in `dark:` varyantı yerine `data-theme` özniteliği ve CSS değişken geçersiz kılma (override) stratejisiyle uygulanmasının, tema geçişini tek bir DOM özniteliği üzerinden yönetilebilir kıldığı öngörülmüştür. Bu tercih, onlarca bileşende tek tek `dark:bg-...` sınıfı tekrarlamak yerine, tek bir yerde tanımlanan renk değişkenlerinin otomatik olarak tüm arayüze yayılmasını sağlayan merkezi (centralized) bir tasarım stratejisi olarak benimsenmiştir. Ayrıca `Cormorant Garamond` ve `DM Sans` gibi iki farklı yazı tipinin birlikte kullanılması, başlıklarda edebi/sıcak bir ton, gövde metninde ise okunabilirlik önceliği taşıyan bilinçli bir tipografik hiyerarşi kararı olmuştur.

- **Teknik Uygulama ve Mimari Kararlar:** `resources/views/partials/head.blade.php` içinde `:root` seçicisinde `--bv-bg`, `--bv-accent`, `--bv-charcoal` gibi belirteçler tanımlanmış, `html[data-theme="dark"]` seçicisi altında aynı değişkenler koyu tema değerleriyle yeniden atanmıştır. `bv-card`, `bv-btn`, `bv-input` gibi bileşen sınıfları bu değişkenleri kullanarak tema bağımsız (theme-agnostic) biçimde yazılmıştır. `tailwind.config.js` dosyasında `content` dizisi `resources/views/**/*.blade.php` yoluna genişletilerek Tailwind'in kullanılmayan sınıfları ayıklama (purge) mekanizmasının tüm Blade dosyalarını taraması sağlanmıştır.

```css
html[data-theme="dark"] { --bv-bg: #121110; --bv-accent: #c4a574; }
```

Tema tercihi `localStorage` üzerinde `bv-theme` anahtarıyla kalıcı hale getirilmiş, `partials/theme-switcher.blade.php` bileşeni ise yalnızca `data-theme-set` özniteliği taşıyan iki düğmeden oluşarak durumun tamamen CSS ve küçük bir JavaScript yardımcı nesnesi (`window.BookverseTheme`) üzerinden yönetilmesini sağlamıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Sayfa ilk yüklendiğinde, tarayıcının önce açık temayı çizip ardından JavaScript çalıştıktan sonra koyu temaya geçmesi sonucu rahatsız edici bir "flash of unstyled content" (FOUC benzeri tema titremesi) gözlemlenmiştir. Sorun, tema belirleme betiğinin `<head>` içine, sayfanın geri kalanından önce çalışacak şekilde satır içi (inline) `<script>` olarak yerleştirilmesi ve `document.documentElement`e `data-theme` özniteliğinin DOM çizilmeden önce uygulanmasıyla giderilmiştir. Bu deneyim, performans odaklı çözümlerin bazen "en az kod" değil, "doğru zamanda çalışan az kod" ilkesine dayandığını göstermiştir.

---

### Gün 13: Alpine.js ile Bildirimsel (Declarative) Durum Yönetimi

- **Teorik Bilgi ve Amaç:** On üçüncü günde, tam bir SPA (Single Page Application) çerçevesi kurmadan arayüze reaktiflik kazandırmak amacıyla Alpine.js kütüphanesinin kullanım alanları incelenmiştir. Alpine.js, Vue.js'in söz dizimine benzeyen ancak çok daha hafif bir çalışma zamanına (runtime) sahip, HTML özniteliklerine gömülü bildirimsel bir durum yönetimi sunar. `x-data` direktifiyle bir DOM elemanına yerel durum (local state) tanımlanabilir, `x-show` ve `x-transition` ile bu duruma bağlı görünürlük ve geçiş animasyonları yönetilebilir. Bu yaklaşımın Bookverse için uygunluğu, sayfanın büyük kısmının sunucu tarafında (Blade) render edilmesi, yalnızca modal, açılır menü gibi izole etkileşimlerin istemci tarafında yönetilmesi gerekliliğinden kaynaklanmıştır. Bu, "Hotwire/Turbo" ve "HTMX" gibi modern çözümlerle aynı felsefeyi paylaşan, sunucu merkezli (server-centric) fakat noktasal olarak reaktif bir mimari tercihidir.

- **Teknik Uygulama ve Mimari Kararlar:** `resources/js/app.js` dosyasında `import Alpine from 'alpinejs'` ve `Alpine.start()` çağrısıyla kütüphane global olarak başlatılmıştır. `resources/views/components/modal.blade.php` bileşeni, Breeze'den miras alınan bir örnek olarak, `x-data` içinde `show` durumunu ve klavye ile odak (focus) yönetimi için `focusables()` yardımcı fonksiyonlarını tanımlamıştır. `x-on:keydown.escape.window="show = false"` gibi olay dinleyicileri, klavye erişilebilirliği (accessibility) gereksinimlerini bildirimsel biçimde karşılamıştır.

```html
<p x-data="{ show: true }" x-show="show" x-transition
   x-init="setTimeout(() => show = false, 2000)">{{ __('Saved.') }}</p>
```

Bu tür kısa ömürlü geri bildirim mesajları, sunucu tarafı `session('status')` verisiyle istemci tarafı zamanlayıcısını birleştirmiştir. Benzer biçimde `components/dropdown.blade.php` bileşeni, kullanıcı menüsünün açık/kapalı durumunu `x-data="{ open: false }"` ile tutmuş ve `x-on:click.away="open = false"` direktifiyle bileşen dışına tıklandığında menünün otomatik kapanmasını sağlamıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Projenin geri kalanında etkileşimlerin çoğunun `data-*` öznitelikleri ve saf JavaScript ile yönetildiği, Alpine.js'in yalnızca Breeze kaynaklı bileşenlerde kaldığı fark edilmiş; bu durum iki farklı etkileşim paradigmasının bir arada bulunmasına yol açmıştır. Kısa vadede bu tutarsızlık kabul edilebilir görülmüş, uzun vadeli plan olarak yeni bileşenlerde hangi yaklaşımın kullanılacağına dair bir iç standart (yalnızca durum içeren küçük bileşenlerde Alpine, sunucu ile sık haberleşen bileşenlerde fetch tabanlı JS) belirlenmiştir. Bu karar, her yeni özellik için sıfırdan bir teknoloji tartışması yapılmasının önüne geçen pragmatik bir mimari sözleşme niteliği taşımıştır.

---

### Gün 14: AJAX Tabanlı Sayfa İçi Etkileşimler ve Backend API Entegrasyonunun Mühendislik Mantığı

- **Teorik Bilgi ve Amaç:** On dördüncü günde, kullanıcı deneyimini kesintiye uğratmadan (sayfa yenilemeden) backend ile veri alışverişi yapılmasını sağlayan AJAX (Asynchronous JavaScript and XML, günümüzde `fetch` API) mimarisi uygulanmıştır. Bu yaklaşımın teorik temeli, tarayıcının arka planda bir HTTP isteği gönderip yalnızca ilgili DOM parçasını güncellemesi, böylece tam sayfa yeniden çiziminin (full page reload) getirdiği görsel kesintiyi ve gereksiz veri transferini ortadan kaldırmasıdır. Ayrıca Laravel'in oturum tabanlı CSRF (Cross-Site Request Forgery) koruması ile istemci taraflı `fetch` çağrılarının nasıl uyumlu çalışacağı incelenmiş; her isteğe `X-CSRF-TOKEN` başlığının eklenmesi gerekliliği güvenlik açısından kritik bir gereksinim olarak belirlenmiştir. Bu gün ayrıca "olay temsili" (event delegation) deseninin teorik gerekçesi de tartışılmış; her etkileşimli elemana ayrı ayrı dinleyici (listener) bağlamak yerine ortak bir üst elemana tek bir dinleyici bağlanmasının, sayfa performansını ve dinamik olarak eklenen elemanlarla uyumluluğu artırdığı vurgulanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** `resources/views/partials/bv-ajax.blade.php` dosyasında merkezi bir etkileşim betiği yazılmış; `document.addEventListener('click', ...)` ile olay temsili (event delegation) deseni kullanılarak `data-like-toggle`, `data-rating-star` ve `data-notification-read` gibi öznitelik taşıyan elemanlara tek bir dinleyiciden hizmet verilmiştir. `postJson()` yardımcı fonksiyonu, `meta[name="csrf-token"]` etiketinden okunan belirteci her isteğe otomatik ekleyerek `PostController::toggleLike` ve `BookController::updateRating` gibi backend uç noktalarıyla haberleşmiştir.

```js
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body });
```

Sunucudan dönen JSON yanıtı (`liked`, `likes_count`) doğrudan ilgili DOM elemanlarını güncellemek için kullanılmıştır. Bildirim (notification) sistemi için yazılan `updateNotificationBadge()` fonksiyonu da benzer şekilde, `NotificationController::readAll()` uç noktasından dönen `unread_count` değerini okuyarak zil ikonundaki rozeti gerçek zamanlı güncellemiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Ağ gecikmesi sırasında kullanıcının aynı beğeni butonuna art arda tıklayarak çakışan (race condition) istekler göndermesi riski fark edilmiştir. Bu durum, istek gönderilir gönderilmez butonun `disabled = true` yapılıp görsel olarak soluklaştırılması (`opacity-60`), yanıt geldikten sonra tekrar etkinleştirilmesiyle önlenmiştir; böylece iyimser arayüz güncellemesi (optimistic UI) ile sunucu tutarlılığı arasında güvenli bir denge kurulmuştur. Hata durumunda ise butona `shake` sınıfı eklenerek işlemin başarısız olduğu kullanıcıya görsel bir titreşim animasyonuyla anlatılmıştır.

---

### Gün 15: Canlı Arama (Live Search) ve Yapay Zekâ Öneri Modalinin Uçtan Uca Entegrasyonu

- **Teorik Bilgi ve Amaç:** Frontend fazının son gününde, kullanıcı yazarken sonuç öneren "canlı arama" (live search / autocomplete) özelliği ile yapay zekâ destekli kitap öneri modalinin uçtan uca (end-to-end) entegrasyonu tamamlanmıştır. Canlı arama senaryosunda, her tuş vuruşunda sunucuya istek atmanın performans ve maliyet açısından sürdürülemez olduğu değerlendirilmiş; bu nedenle "debounce" (geciktirme) tekniğinin, kullanıcı yazmayı belirli bir süre (200ms) durdurduktan sonra isteği tetikleyerek gereksiz ağ trafiğini engelleyen standart bir mühendislik çözümü olduğu benimsenmiştir. Ayrıca önceki isteklerin geç gelip arayüzü yanlış veriyle güncellemesini (stale response) önlemek amacıyla `AbortController` API'sinin teorik işlevi incelenmiştir. Bu iki teknik, birlikte ele alındığında, istemci tarafı performans optimizasyonunun sunucu yükünü azaltmakla kalmayıp kullanıcı arayüzünün tutarlılığını da garanti altına alan tamamlayıcı bir çift oluşturmuştur.

- **Teknik Uygulama ve Mimari Kararlar:** `layouts/site-nav.blade.php` içindeki arama kutusuna `data-suggest-url="{{ route('search.suggest') }}"` bağlanmış, `bv-ajax.blade.php` içindeki `initLiveSearch()` fonksiyonu her girdi olayında `setTimeout` ile debounce uygulamış ve önceki `fetch` isteğini `activeController.abort()` ile iptal ettikten sonra yenisini başlatmıştır. Backend tarafında `SearchController::suggest()` metodu `performSearch()` özel metodunu düşük limitlerle (`booksLimit: 6`) çağırarak hafif bir JSON yanıtı üretmiştir. `partials/ai-recommendations-modal.blade.php` bileşeni ise `route('ai.recommend')` uç noktasına `mood`, `genre_id` ve `free_text` alanlarını gönderip dönen önerileri dinamik olarak kart (card) listesine dönüştürmüştür. Her öneri kartındaki "Rafına Ekle" butonu, kullanıcıyı sayfadan hiç ayırmadan doğrudan `BookController::updateStatus()` uç noktasına ikinci bir arka plan isteği göndererek zincirleme bir AJAX entegrasyonu örneği sunmuştur.

```js
if (activeController) activeController.abort();
activeController = new AbortController();
fetch(url, { signal: activeController.signal });
```

- **Karşılaşılan Zorluk ve Çözüm:** Yapay zekâ öneri modalinde, kullanıcının konu dışı (off-topic) bir metin göndermesi durumunda hem gereksiz API maliyeti oluşması hem de kötü bir kullanıcı deneyimi yaşanması riski görülmüştür. Bu sorun, backend'deki doğrulamaya ek olarak istemci tarafında `looksOffTopicLocally()` adlı basit bir anahtar kelime denetimi eklenerek, açıkça alakasız isteklerin sunucuya gitmeden önce yakalanıp kullanıcıya anında geri bildirim verilmesiyle hafifletilmiştir. Ayrıca modalin, sayfanın kenar çubuğu (sidebar) içinde `position: fixed` bir elemanın üst öğesinin dönüştürme (transform) bağlamına hapsolması sorunu, modal DOM elemanının JavaScript ile doğrudan `document.body`'nin sonuna taşınmasıyla (`appendChild`) çözülmüştür.

---

## FAZ 4: SEO, GÜVENLİK, DAĞITIM & KALİTE GÜVENCESİ (Gün 16 – Gün 20)

---

### Gün 16: Arama Motoru Optimizasyonu (SEO) Mühendisliği ve Çoklu Dil (i18n) Altyapısı

- **Teorik Bilgi ve Amaç:** On altıncı günde, uygulamanın yalnızca kullanıcılar için değil, arama motoru tarayıcıları (crawler) için de doğru biçimde yapılandırılması hedeflenmiştir. SEO mühendisliğinin teorik temelini; anlamlı ve kalıcı URL yapıları, yapılandırılmış veri (structured data) ile arama motoruna içeriğin anlamını açıklama ve tarayıcıların hangi sayfaları indeksleyip hangilerini indekslememesi gerektiğini bildiren protokoller oluşturmaktadır. Ayrıca uluslararasılaştırma (internationalization / i18n) kavramı ele alınmış; arayüz metinlerinin doğrudan koda gömülmesi yerine anahtar-değer (key-value) çeviri dosyalarında tutulmasının, yeni bir dil eklerken kaynak koda dokunulmasını gereksiz kıldığı değerlendirilmiştir. Bu iki konunun aynı gün ele alınmasının nedeni, ikisinin de "içeriğin doğru muhataba doğru biçimde sunulması" ortak paydasında buluşmasıdır; biri makine (arama motoru), diğeri insan (farklı dildeki kullanıcı) muhatap alır.

- **Teknik Uygulama ve Mimari Kararlar:** `App\Http\Controllers\SitemapController` sınıfı, `Book` ve `User` (yalnızca `account_visibility` alanı `public` olanlar) kayıtlarını gezerek `sitemap.blade.php` görünümüne XML formatında aktarmıştır. `RobotsController` ise `/admin`, `/api/` gibi özel rotaları `Disallow` direktifiyle kapatmıştır. Kitap sayfalarında `Book::seoDescription()` ve JSON-LD (`@type: Book`) şeması `partials/head.blade.php` üzerinden enjekte edilmiştir. Çoklu dil için `lang/tr/ui.php` ve `lang/en/ui.php` dosyaları oluşturulmuş, `App\Http\Middleware\SetLocale` ara katmanı her istekte `session('locale')` değerini okuyup `App::setLocale()` ile aktif dili belirlemiştir.

```php
$locale = session('locale', config('app.locale', 'tr'));
App::setLocale($locale);
```

`LocaleController::switch()` metodu ise dil değişimini oturuma yazıp kullanıcıyı bulunduğu sayfaya geri yönlendirmiştir. `partials/head.blade.php` içinde ayrıca `og:locale` ve `og:locale:alternate` meta etiketleri eklenerek, sosyal medya paylaşım kartlarının da doğru dilde önizleme göstermesi sağlanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Eski sayısal id tabanlı kitap URL'lerinin (`/books/{id}`) slug tabanlı yapıya (`/books/{slug}`) geçişte arama motoru indeksinden düşme riski taşıdığı fark edilmiştir. Bu sorun, `BookController::showLegacy()` metodunda `redirect()->to(..., 301)` ile kalıcı yönlendirme uygulanarak, arama motorlarının eski bağlantının SEO değerini (link equity) yeni adrese aktarması sağlanmıştır. `tests/Feature/SeoTest.php` içindeki `test_numeric_book_url_redirects_to_slug()` testi ise bu davranışın gelecekte yanlışlıkla bozulmasını (regresyon) önleyen otomatik bir güvence olarak yazılmıştır.

---

### Gün 17: Girdi Doğrulama Derinleştirmesi ve Yapay Zekâ Katmanında Güvenlik Sertleştirmesi

- **Teorik Bilgi ve Amaç:** On yedinci günde, uygulamanın dış kaynaklardan (kullanıcı girdisi, harici API) gelen veriye karşı savunma katmanları güçlendirilmiştir. Bu çalışmanın teorik dayanağı "derinlemesine savunma" (defense in depth) ilkesidir: tek bir doğrulama noktasına güvenmek yerine, hem istemci hem sunucu tarafında bağımsız kontrol katmanları kurulması önerilir. Google Gemini gibi büyük dil modeli (LLM) tabanlı servislerin entegrasyonunda ayrıca "prompt injection" ve amaç dışı kullanım (misuse) riskleri değerlendirilmiş; kullanıcının modele kitap önerisi dışında bir görev (kod yazdırma, sohbet ettirme) yaptırmaya çalışmasının hem maliyet hem kötüye kullanım açısından engellenmesi gerektiği sonucuna varılmıştır. Bu bağlamda, girdi doğrulamanın yalnızca veri biçimini (format) değil, isteğin "niyetini" (intent) de değerlendirmesi gerektiği, klasik form doğrulamasının ötesinde bir güvenlik anlayışı olarak benimsenmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** `App\Services\AiBookRequestValidator` sınıfı yazılmış; `isClearlyOffTopic()` metodu regex tabanlı desenlerle (spor, yemek tarifi, kod yazdırma gibi) açıkça alakasız istekleri reddetmiş, `isBookRelated()` metodu ise kitap alanına özgü anahtar kelime ve niyet (intent) kalıplarını tanıyarak meşru istekleri geçirmiştir. Bu servis, `AiRecommendationController::recommend()` içinde Gemini API çağrısından önce devreye girerek gereksiz dış API maliyetini engellemiştir. Ayrıca `Admin\BookController::bookRules()` içindeki özel doğrulama kapanışı (closure) ve tüm form isteklerinde uygulanan `$request->validate()` çağrıları, SQL enjeksiyonu ve kütlesel atama saldırılarına karşı ikinci bir savunma hattı oluşturmuştur. Kaba kuvvet (brute-force) saldırılarına karşı da `app/Http/Requests/Auth/LoginRequest.php` içinde `RateLimiter::tooManyAttempts()` ile beş başarısız girişten sonra hesabın geçici olarak kilitlenmesi sağlanmış, `routes/auth.php` üzerindeki `throttle:6,1` ara katmanı ise şifre sıfırlama gibi hassas uç noktalara dakikada altı istekle sınır getirmiştir.

```php
if ($this->isClearlyOffTopic($freeText)) {
    return ['valid' => false, 'message' => __('ui.ai.invalid_request')];
}
```

- **Karşılaşılan Zorluk ve Çözüm:** Yalnızca anahtar kelime listesine dayalı bir filtrenin, meşru ama listede yer almayan kitap isteklerini yanlışlıkla reddetme (false positive) riski taşıdığı görülmüştür. Bu sorun, "ruh hali" veya "tür" alanlarından biri seçiliyse serbest metnin daha esnek değerlendirilmesi kuralıyla dengelenmiş; böylece güvenlik ile kullanılabilirlik arasında kabul edilebilir bir denge kurulmuştur. `tests/Unit/AiBookRequestValidatorTest.php` içinde hem açıkça alakasız hem de sınırda kalan (edge case) örnek metinler test edilerek bu dengenin davranışsal olarak da doğrulanması sağlanmıştır.

---

### Gün 18: Konteynerleştirme (Docker) ve Render Üzerinde Bulut Dağıtımı (Deployment)

- **Teorik Bilgi ve Amaç:** On sekizinci günde, uygulamanın geliştirici makinesinden bağımsız, tutarlı bir ortamda çalıştırılabilmesi için konteynerleştirme (containerization) teknolojisi uygulamaya alınmıştır. Docker'ın teorik değeri, "benim makinemde çalışıyordu" sorununu ortadan kaldıran, işletim sistemi, PHP sürümü, uzantılar (extensions) ve web sunucusu yapılandırmasını tek bir taşınabilir imaj (image) içinde donduran bir izolasyon katmanı sunmasıdır. Ayrıca "canlıya alma" (deployment) sürecinde, yerel dosya sisteminin geçici (ephemeral) olduğu bulut platformlarında kalıcı dosya depolamanın (profil fotoğrafları gibi) nasıl yönetileceği sorusu bu günün merkezinde yer almıştır. Render gibi Platform-as-a-Service (PaaS) sağlayıcılarının, her yeni dağıtımda konteyneri sıfırdan yeniden oluşturduğu ve disk üzerindeki değişikliklerin kalıcı olmadığı gerçeği, bu mimari kararların temel gerekçesini oluşturmuştur.

- **Teknik Uygulama ve Mimari Kararlar:** Proje kök dizinine bir `Dockerfile` yazılmış; `php:8.4-apache` temel imajı üzerine `pdo_pgsql`, `mbstring`, `bcmath` uzantıları kurulmuş, Apache'nin belge kökü (`APACHE_DOCUMENT_ROOT`) `public/` dizinine yönlendirilmiştir. `docker/entrypoint.sh` betiği, konteyner her başladığında `php artisan storage:link` ve `php artisan migrate --force` komutlarını otomatik çalıştırarak veritabanı şemasının canlı ortamda güncel kalmasını sağlamıştır. Kalıcı depolama sorunu ise `config/filesystems.php` içindeki `supabase` diskinin (S3 uyumlu) `User::profilePhotosDisk()` metoduyla ortam değişkenlerine göre otomatik seçilmesiyle çözülmüştür.

```sh
php artisan migrate --force
exec apache2-foreground
```

Render platformunda `PROFILE_PHOTOS_DISK=supabase` ortam değişkeni tanımlanarak üretim ortamında dosyaların Supabase Storage'a yazılması sağlanmıştır. `bootstrap/app.php` içindeki `$middleware->trustProxies(at: '*')` ayarı da, Render'ın ters vekil (reverse proxy) katmanı arkasında çalışan uygulamanın istemci IP'sini ve HTTPS bilgisini doğru algılaması için bu gün eklenmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** İlk Docker derlemesinde (`build`), `composer.lock` dosyasının PHP 8.4 platform gereksinimiyle üretildiği fakat imajın PHP sürümüyle uyuşmadığı bir hata alınmıştır. Sorun, `Dockerfile` içindeki temel imaj sürümünün `composer.lock` ile eşleşecek şekilde `php:8.4-apache` olarak güncellenmesiyle giderilmiştir. Bu deneyim, bağımlılık kilit dosyalarının (`lock file`) yalnızca paket sürümlerini değil, dolaylı olarak çalışma zamanı platform gereksinimini de sabitlediğini ve dağıtım ortamıyla mutlaka senkron tutulması gerektiğini göstermiştir.

---

### Gün 19: Otomatik Test Altyapısı (PHPUnit) ile Kalite Güvencesi

- **Teorik Bilgi ve Amaç:** On dokuzuncu günde, yazılımın her değişiklikten sonra manuel olarak yeniden kontrol edilmesi yerine, davranışın otomatik testlerle doğrulanmasını sağlayan bir kalite güvence (quality assurance) katmanı kurulmuştur. Laravel'in PHPUnit tabanlı test altyapısında "Feature" testleri, bir HTTP isteğinin uçtan uca (route → middleware → controller → veritabanı → yanıt) doğru çalıştığını sınarken "Unit" testleri tek bir sınıfın izole mantığını doğrular. `RefreshDatabase` özelliğinin (trait) her testten önce veritabanını sıfırlayıp migration'ları yeniden çalıştırması, testlerin birbirinden bağımsız ve tekrarlanabilir (idempotent) olmasını garanti eden kritik bir tasarım ilkesi olarak değerlendirilmiştir. Ayrıca test verisinin elle oluşturulması yerine `Database\Factories` sınıflarıyla programatik olarak üretilmesinin, testleri okunabilir kılarken veri hazırlama tekrarını da azalttığı gözlemlenmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** `tests/Feature` dizininde `BookProtectionTest`, `BookRatingTest`, `PostLikeTest`, `SeoTest` ve `AiRecommendationTest` gibi otuza yakın test sınıfı yazılmıştır. `AiRecommendationTest` içinde `Http::fake()` kullanılarak gerçek Gemini API'sine istek atılmadan sahte (mock) bir yanıt döndürülmüş, böylece testlerin hem hızlı hem de dış servise bağımsız çalışması sağlanmıştır. `BookProtectionTest::test_protected_books_cannot_be_deleted()` testi ise `Book` modelindeki `deleting` olayının (event) `is_protected` alanına göre silme işlemini gerçekten engellediğini doğrulamıştır. Kimlik doğrulama akışları için `tests/Feature/Auth` alt dizininde `AuthenticationTest`, `RegistrationTest` ve `TwoFactorAuthenticationTest` sınıfları yazılarak sekizinci günde kurulan güvenlik katmanının davranışı da otomatik güvence altına alınmıştır. Sosyal katman için ise `SocialFeedTest`, `ProfileFollowerCountTest`, `NotificationTest` ve `ReadingGoalTest` gibi sınıflar yazılmış; bu testler `follow()`/`unfollow()` metotlarının takipçi sayacını doğru güncellediğini ve `ReadingGoalController::update()` sonrası kullanıcı satırındaki `reading_goal` alanının beklenen değeri taşıdığını doğrulamıştır.

```php
Http::fake(['generativelanguage.googleapis.com/*' => Http::response([...])]);
$response->assertJsonStructure(['recommendations' => ['*' => ['title']]]);
```

- **Karşılaşılan Zorluk ve Çözüm:** Testlerin bir kısmının yerel SQLite veritabanıyla çalışırken PostgreSQL'e özgü bazı sorgu davranışlarını (örneğin `havingRaw` ifadelerindeki fonksiyon farklılıkları) yakalayamadığı fark edilmiştir. Bu risk, kritik sorguların her iki veritabanı sürücüsünde de test edilmesi gerektiği notuyla kayıt altına alınmış, CI ortamında SQLite'ın hız avantajı nedeniyle bilinçli olarak tercih edildiği belgelenmiştir. Bu, test hızı ile üretim ortamı sadakati (fidelity) arasındaki klasik mühendislik ödünleşiminin (trade-off) somut bir örneği olarak değerlendirilmiştir.

---

### Gün 20: Sürekli Entegrasyon (CI/CD) ile GitHub Actions ve Staj Sürecinin Genel Değerlendirmesi

- **Teorik Bilgi ve Amaç:** Yirminci ve altyapı-dışı son günde, yazılan testlerin her kod değişikliğinde insan müdahalesi olmadan otomatik çalıştırılmasını sağlayan Sürekli Entegrasyon (Continuous Integration) pratiği devreye alınmıştır. CI'ın teorik gerekçesi, hataların geliştirme sürecinin mümkün olduğunca erken bir aşamasında (kod ana dala birleşmeden önce) yakalanmasının, üretim ortamına yansıyan hataların maliyetinden çok daha düşük olmasıdır. Bu gün ayrıca yirmi günlük sürecin bütünsel bir değerlendirmesi yapılmış; Laravel'in MVC mimarisinden başlayıp veritabanı tasarımı, servis katmanı, API/Sanctum, Blade/Alpine/Tailwind ön yüzü ve son olarak dağıtım/test altyapısına uzanan katmanlı öğrenme sürecinin bir yazılım mühendisliği projesinin uçtan uca yaşam döngüsünü nasıl temsil ettiği tartışılmıştır. Sürekli Dağıtım (Continuous Deployment) kavramı da bu tartışmaya dahil edilmiş; testleri geçen kodun Render platformuna otomatik olarak yayınlanmasının, projenin gelecekteki olgunlaşma basamaklarından biri olduğu değerlendirilmiştir. Bu değerlendirme sürecinde, altyapı fazlarında kurulan `book_user` ve `follows` gibi ilişkisel desenlerin üzerine, staj süresince paralel olarak geliştirilen `Post`, `PostComment`, `PostLike`, `Notification` ve `ReadingGoalController` gibi sosyal katman bileşenlerinin de aynı mimari prensiplerle (ince controller, servis katmanı, pivot ilişkiler) inşa edildiği görülmüş; bu da öğrenilen desenlerin tek bir özelliğe özgü değil, projenin genelinde tutarlı biçimde uygulanabilir olduğunu doğrulamıştır.

- **Teknik Uygulama ve Mimari Kararlar:** `.github/workflows/tests.yml` dosyasında, `main` dalına yapılan her `push` ve `pull_request` olayında tetiklenen bir iş akışı (workflow) tanımlanmıştır. Bu akış, `shivammathur/setup-php` aksiyonuyla PHP 8.4 ortamını hazırlamış, `composer install` ile bağımlılıkları kurmuş, `.env.example` dosyasından geçici bir `.env` üretip `php artisan key:generate` çalıştırmış ve son olarak `php artisan test` komutuyla tüm test paketini (test suite) yürütmüştür.

```yaml
- name: Run tests
  run: php artisan test
```

Bu otomasyon, her katkının (`admin panel`, `2FA`, `AI önerileri` gibi) mevcut işlevselliği bozmadığının GitHub arayüzünde yeşil bir onay rozeti (badge) ile görünür kılınmasını sağlamıştır. `README.md` dosyasındaki "CI" satırı da bu iş akışına atıfla güncellenerek projeye katkı sağlayacak herkesin test çalıştırma beklentisinden haberdar olması sağlanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** CI ortamında ilk çalıştırmada, yerel `composer.lock` dosyasının PHP 8.4 platformuna kilitlendiği ancak iş akışının başlangıçta daha eski bir PHP sürümüyle yapılandırıldığı tespit edilmiş, bu da bağımlılık çözümleme hatalarına yol açmıştır. Sorun, `tests.yml` içindeki `php-version` değerinin `8.4` olarak güncellenmesiyle giderilmiş; bu deneyim, yerel geliştirme, konteyner ve CI ortamlarının sürüm tutarlılığının bir proje boyunca titizlikle korunması gereken kritik bir mühendislik disiplini olduğunu göstererek yirmi günlük altyapı ve geliştirme sürecinin öğretici bir kapanışını oluşturmuştur.
