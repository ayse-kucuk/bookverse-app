# STAJ DEFTERİ — TAV BİLİŞİM / TAV TECHNOLOGIES
## Yazılım Geliştirme & Web Teknolojileri (20 İş Günü)

---

## FAZ 1: ORTAM, HAVACILIK YAZILIMI VE SERVİS ALTYAPISI (Gün 1 – Gün 5)

---

### Gün 1: Yazılım Geliştirme Ortamı Kurulumu, Git ve Kurumsal Erişim Yönetimi

- **Teorik Bilgi ve Amaç:** Staj sürecinin ilk gününde, kurumsal yazılım geliştirme süreçlerinin temelini oluşturan yerel geliştirme ortamının (local development environment) kurulması hedeflenmiştir. Modern yazılım mühendisliğinde bir geliştiricinin üretkenliği; IDE seçimi, sürüm kontrol disiplini ve ortam değişkenlerinin güvenli yönetimiyle doğrudan ilişkilidir. Bu bağlamda VS Code / WebStorm gibi entegre geliştirme ortamları, Git versiyon kontrol sistemi ve Node.js çalışma zamanı (runtime) birlikte ele alınmıştır. Kurumsal ortamlarda kod depolarına (repository) erişimin VPN ve SSH anahtarları üzerinden kimlik doğrulamaya bağlanması, hem güvenlik hem de denetlenebilirlik açısından kritik bir gereksinimdir. Ayrıca `.env` dosyalarıyla yapılandırma değerlerinin kaynak koddan ayrılması, Twelve-Factor App metodolojisinin "config" ilkesinin pratik bir uygulaması olarak değerlendirilmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** Geliştirme makinesine Node.js LTS sürümü ve Git kurulmuş; SSH anahtar çifti (`ssh-keygen`) üretilerek kurumsal Git sunucusuna kaydedilmiştir. VPN bağlantısı sonrası ilgili takım depolarına okuma/yazma yetkileri doğrulanmış, `git clone` ile yerel çalışma kopyaları alınmıştır. Proje kökündeki `.env.example` dosyası referans alınarak `.env` oluşturulmuş; API anahtarları, veritabanı bağlantı dizeleri ve ortam adı (`development`) bu dosyada tanımlanmıştır. IDE tarafında ESLint/Prettier eklentileri etkinleştirilerek kod biçimlendirme standartlarının otomatik uygulanması sağlanmıştır.

```bash
ssh-keygen -t ed25519 -C "stajyer@tav"
git clone git@gitlab.tav.local:web/holding-cms.git
cp .env.example .env
```

Bu adımlarla yerel ortam, kurumsal erişim ve temel güvenlik yapılandırması tamamlanmış; sonraki günlerde incelenecek BHS/BRS ve web projelerine teknik olarak hazır hale gelinmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** İlk Git klonlama denemesinde SSH bağlantısının zaman aşımına uğradığı görülmüştür. Sorunun VPN oturumunun tam kurulmadan başlatılmasından kaynaklandığı tespit edilmiş; VPN bağlantısı doğrulandıktan ve `ssh -T` ile kimlik doğrulama test edildikten sonra depo başarıyla klonlanmıştır. Bu deneyim, kurumsal ağ erişiminin yazılım araçlarından önce doğrulanması gerektiğini göstermiştir.

---

### Gün 2: BHS / BRS Yazılım Mimarisi ve Veritabanı Veri Akış Analizi

- **Teorik Bilgi ve Amaç:** İkinci gün, havaalanı bagaj süreçlerinin yazılım omurgasını oluşturan Bagaj İşleme Sistemi (BHS) ve Bagaj Eşleştirme Sistemi (BRS) mimarisinin incelenmesine ayrılmıştır. BRS yazılımı; yolcu kaydı ile bagaj barkod verisini ilişkilendirerek “doğru bagajın doğru uçağa/kapıya yönlendirilmesi” iş kuralını veri düzeyinde uygular. Bu gereksinim, ilişkisel veritabanı tasarımında bire-çok ve çoktan-çoğa ilişkiler, yabancı anahtar bütünlüğü (referential integrity) ve gerçek zamanlı eşleşme algoritmalarının birlikte çalışmasını zorunlu kılar. Veri tutarlılığı (data integrity); eksik, gecikmeli veya çakışan kayıtların operasyonel hataya dönüşmesini engelleyen temel kalite ölçütü olarak ele alınmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** BRS arka plan veritabanındaki yolcu, uçuş, bagaj ve barkod tablolarının ilişki şeması incelenmiş; bagaj barkodunun benzersiz tanımlayıcı olarak kullanıldığı sorgular analiz edilmiştir. Örnek bir eşleşme sorgusunda yolcu PNR/bilete bağlı bagaj kayıtlarının uçuş numarası ve kapı bilgisiyle birleştirildiği görülmüştür. Veri akış diyagramları üzerinden “okuma → doğrulama → eşleştirme → yönlendirme kararı” zinciri takip edilmiştir.

```sql
SELECT b.bag_tag, p.passenger_name, f.flight_no, f.gate
FROM baggage b
JOIN passengers p ON p.id = b.passenger_id
JOIN flights f ON f.id = b.flight_id
WHERE b.bag_tag = :tag AND b.status = 'ACTIVE';
```

Bu inceleme, havalimanı yazılımında operasyonel doğruluğun doğrudan SQL şema ve sorgu kalitesine bağlı olduğunu somutlaştırmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Aynı bagaj etiketinin kısa süreli yeniden kullanımı nedeniyle eşleşme sorgusunda birden fazla aktif kayıt dönmesi riski gözlemlenmiştir. Çözüm olarak sorguya zaman penceresi ve durum filtresi (`status = 'ACTIVE'`, `created_at` aralığı) eklenmesinin veri tutarlılığını koruduğu değerlendirilmiştir.

---

### Gün 3: IATA BSM/BTM Mesajlaşma Protokolleri ve Servis Tetikleme Mekanizmaları

- **Teorik Bilgi ve Amaç:** Üçüncü günün odağı, IATA standartlarındaki BSM (Baggage Service Message) ve BTM mesaj biçimlerinin yazılımsal ayrıştırılması (parsing) ve olay tabanlı (event-driven) servis tetikleme mimarisidir. Havalimanı sistemleri; bagajın check-in, transfer veya yükleme gibi yaşam döngüsü olaylarını standart mesajlarla duyurur. Bu mesajlar çoğu zaman kuyruk (message queue) altyapısı üzerinden asenkron işlenir; böylece üretici ve tüketici servisler birbirinden gevşek bağlı (loosely coupled) kalır. İstisna (exception) yönetimi ve yapılandırılmış loglama, mesaj kaybı veya bozuk yük (payload) durumunda kök neden analizini mümkün kılan zorunlu katmanlardır.

- **Teknik Uygulama ve Mimari Kararlar:** Örnek BSM/BTM yüklerinin alan yapısı (bagaj etiketi, uçuş, olay tipi, zaman damgası) incelenmiş; ayrıştırıcı katmanın ham metni/JSON’u domain nesnelerine dönüştürdüğü görülmüştür. Event-driven akışta bir BSM olayının kuyruğa yazılması, tüketici servisin abone olup iş kuralını çalıştırması ve sonucu loglaması adım adım takip edilmiştir.

```js
function parseBsm(raw) {
  const fields = Object.fromEntries(
    raw.split('/').map((p) => p.split('='))
  );
  return { tag: fields.TAG, flight: fields.FLT, event: fields.EVT };
}
```

İstisna senaryolarında (eksik alan, bilinmeyen olay tipi) servisin hatayı yutmak yerine ölçülebilir biçimde logladığı ve gerekirse yeniden deneme (retry) kuyruğuna aldığı gözlemlenmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Bozuk bir mesaj yükünde ayrıştırıcının tüm tüketici sürecini çökerttiği bir örnek incelenmiştir. Çözüm olarak ayrıştırma işleminin try/catch ile sarmalanması, hatalı mesajın ölü mektup kuyruğuna (dead-letter queue) alınması ve alarm üretilmesi önerilmiş; böylece tek bir bozuk kayıt tüm hattı durdurmamıştır.

---

### Gün 4: Kurumsal Yazılım Portföyü, SDLC ve 3. Parti API Entegrasyonları

- **Teorik Bilgi ve Amaç:** Dördüncü günde kurum bünyesinde aktif web ve servis projelerinin Yazılım Yaşam Döngüsü (SDLC) prensipleri ile üçüncü parti API entegrasyonları incelenmiştir. SDLC; gereksinim, tasarım, geliştirme, test, dağıtım ve bakım aşamalarının disiplinli yürütülmesini sağlar. Dış tedarikçi yazılımlarıyla entegrasyonda ise API uç noktaları, kimlik doğrulama ve veri dönüştürme (data mapping) katmanı kritik hale gelir: kurum içi veri modeli ile tedarikçi şeması nadiren birebir örtüşür. Ayrıca çok dilli (i18n) mimari; aynı uygulamanın farklı dil/bölge kullanıcılarına tutarlı deneyim sunmasını sağlayan yazılım kalitesi ölçütüdür.

- **Teknik Uygulama ve Mimari Kararlar:** Holding web projelerinde sprint tabanlı geliştirme, kod incelemesi (code review) ve ortamlar arası terfi (dev → staging → prod) akışı gözlemlenmiştir. Üçüncü parti API’lerden gelen yanıtların kurumsal DTO’lara map edildiği adaptör katmanı incelenmiş; tarih, para birimi ve durum kodu gibi alanların dönüşüm kuralları dokümante edilmiştir. i18n tarafında çeviri anahtarlarının kaynak koddan ayrıldığı ve dil paketleriyle yüklendiği görülmüştür.

```js
const mapVendorFlight = (v) => ({
  flightNo: v.flt_num,
  status: STATUS_MAP[v.st] ?? 'UNKNOWN',
  std: new Date(v.sched_dep).toISOString(),
});
```

Bu desen, tedarikçi API değiştiğinde yalnızca adaptör katmanının güncellenmesini sağlayarak çekirdek iş mantığını korumuştur.

- **Karşılaşılan Zorluk ve Çözüm:** Tedarikçi API’sinin aynı alan için farklı ortamlarında (test/prod) farklı enum değerleri döndürdüğü tespit edilmiştir. Mapping tablosuna ortam bazlı varsayılanlar ve bilinmeyen değerler için güvenli düşüş (fallback) eklenerek üretimde beklenmeyen durum kodlarının arayüzü bozması engellenmiştir.

---

### Gün 5: Web Servis Yük Analizi, Asenkron İstek İşleme ve Performans Testleri

- **Teorik Bilgi ve Amaç:** Beşinci gün, yüksek trafikli web servislerinin performansı; saniyedeki istek sayısı (RPS), gecikme (latency), asenkron işleme ve önbellekleme (caching) kavramları üzerinden ele alınmıştır. Yoğun havalimanı ve holding trafiklerinde senkron, bloklayan mimariler darboğaz oluşturur; bu nedenle kuyruk tabanlı asenkron işlem ve Redis gibi bellek içi önbellek katmanları tercih edilir. Yük dengeleme (load balancing) ise tek bir düğümün aşırı yüklenmesini önleyerek yatay ölçeklemeyi mümkün kılar.

- **Teknik Uygulama ve Mimari Kararlar:** Örnek REST/mikroservis uç noktalarının yanıt süreleri izlenmiş; sık okunan referans verilerin Redis üzerinde TTL ile önbelleğe alındığı görülmüştür. Asenkron işler (rapor üretimi, toplu bildirim) HTTP isteğinin dışında worker süreçlerine bırakılarak kullanıcıya hızlı ACK dönülmesi sağlanmıştır. Yük testlerinde ortalama gecikme ve hata oranı eşikleriyle performans bütçesi tanımlanmıştır.

```js
async function getFlightStatus(id) {
  const cached = await redis.get(`flight:${id}`);
  if (cached) return JSON.parse(cached);
  const data = await db.flights.findById(id);
  await redis.set(`flight:${id}`, JSON.stringify(data), 'EX', 30);
  return data;
}
```

Bu yaklaşım, tekrarlayan okuma yükünü veritabanından uzaklaştırarak gecikmeyi düşürmüştür.

- **Karşılaşılan Zorluk ve Çözüm:** Önbellekte kalan eski uçuş durumu, gerçek zamanlı güncellemenin gecikmeli yansımasına yol açmıştır. Çözüm olarak kısa TTL (ör. 30 sn) ve durum değişikliğinde ilgili anahtarın bilinçli invalidasyonu (`DEL flight:{id}`) birlikte kullanılarak tutarlılık-performans dengesi kurulmuştur.

---

## FAZ 2: GÖZLEMLENEBİLİRLİK, GERÇEK ZAMANLI VERİ VE CMS (Gün 6 – Gün 10)

---

### Gün 6: Sistem Yazılımları Yönetimi, API ve Uygulama Log Analizleri

- **Teorik Bilgi ve Amaç:** Altıncı günde uç nokta yazılımlarının uzaktan konfigürasyonu ile uygulama loglarının (application logs) analizi ele alınmıştır. Gözlemlenebilirlik (observability); metrik, log ve izleme (tracing) ile sistem davranışının dışarıdan anlaşılabilmesidir. HTTP 4xx hataları istemci/istek sorununa, 5xx hataları ise sunucu veya bağımlılık sorununa işaret eder. Merkezi loglama ve hata izleme (error tracking) araçları, dağıtık servislerde kök neden analizini hızlandırır.

- **Teknik Uygulama ve Mimari Kararlar:** API servis güncellemelerinin yapılandırma bayrakları ve sürüm etiketleriyle yönetildiği gözlemlenmiştir. Uygulama sunucularındaki log dosyalarında 4xx/5xx örnekleri filtrelenmiş; korelasyon kimliği (correlation id) üzerinden bir isteğin servisler arası izi sürülmüştür. Hata izleme aracında stack trace, ortam ve sürüm bilgisiyle birlikte kayıt tutulduğu görülmüştür.

```bash
grep -E "HTTP/(1\\.1|2)\" (4|5)[0-9]{2}" access.log | tail -n 50
```

Pratikte, tekrarlayan 502/504 kayıtlarının belirli bir bağımlı servisin zaman aşımıyla ilişkili olduğu tespit edilerek ilgili ekibe iletilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Farklı servislerin log formatlarının tutarsız olması korelasyonu zorlaştırmıştır. Ortak alanların (timestamp, level, service, correlationId, message) JSON log standardına çekilmesi ve merkezi toplama hattına bağlanmasıyla arama ve filtreleme kolaylaşmıştır.

---

### Gün 7: FIDS ve GPF Gerçek Zamanlı Veri Akış Mimarisi (WebSockets / Data Sync)

- **Teorik Bilgi ve Amaç:** Yedinci gün, Uçuş Bilgi Sistemleri (FIDS) ve Gate-Plane-Flight (GPF) yazılımlarının gerçek zamanlı veri yayın altyapısına odaklanmıştır. Klasik istek-yanıt (request-response) modeli, sık değişen uçuş durumu için yetersiz kalır; bu nedenle WebSocket ve Pub/Sub mimarileriyle sunucudan istemciye anlık push tercih edilir. Change Data Capture (CDC) yaklaşımı ise veritabanı değişikliklerinin olay olarak yakalanıp ön yüze yansıtılmasını sağlar.

- **Teknik Uygulama ve Mimari Kararlar:** FIDS istemcilerinin WebSocket kanalına abone olduğu, uçuş durumu güncellemelerinin Pub/Sub üzerinden yayınlandığı akış incelenmiştir. CDC ile `flights` tablosundaki güncellemelerin olay kuyruğuna düşüp ekran bileşenlerini beslediği gözlemlenmiştir.

```js
socket.on('flight.update', (payload) => {
  updateBoardRow(payload.flightNo, {
    status: payload.status,
    gate: payload.gate,
    etd: payload.etd,
  });
});
```

Bu mimari, yüzlerce ekranın aynı anda tutarlı bilgi göstermesini mümkün kılmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Ağ kesintisinden sonra istemcinin eski durumda kalması riski görülmüştür. Yeniden bağlanmada son bilinen sürüm/zaman damgasıyla snapshot senkronizasyonu yapılması ve ardından canlı olay akışına devam edilmesiyle tutarlılık sağlanmıştır.

---

### Gün 8: Holding Web Projeleri, CMS Yönetimi ve Rol Bazlı Erişim Kontrolü (RBAC)

- **Teorik Bilgi ve Amaç:** Sekizinci günde holding web sitelerinin içerik yönetim sistemleri (CMS, WordPress) ve Rol Bazlı Erişim Kontrolü (RBAC) incelenmiştir. RBAC; kullanıcılara doğrudan izin vermek yerine roller üzerinden yetki atayarak yönetim karmaşıklığını azaltır. Çoklu site (multi-site) yapılarında içerik yayınlama workflow’u; taslak, onay ve yayın aşamalarıyla içerik kalitesi ve kurumsal iletişim riskini kontrol altında tutar. Yetkilendirme (authorization), kimlik doğrulamadan (authentication) ayrı bir güvenlik katmanıdır.

- **Teknik Uygulama ve Mimari Kararlar:** WordPress yönetim panelinde editör, onaycı ve yönetici rollerinin izin matrisleri incelenmiş; menü, eklenti ve içerik tipi bazlı kısıtlar test edilmiştir. Multi-site panelinden bir sitenin içeriği güncellenirken diğer sitelerin etkilenmemesi doğrulanmıştır. Yayın workflow’unda yetkisiz kullanıcının “yayınla” aksiyonuna erişemediği görülmüştür.

```php
if (!current_user_can('publish_pages')) {
    wp_die('Bu işlem için yetkiniz yok.', 403);
}
```

Bu kontroller, içerik güvenliği ile operasyonel hız arasında denge kurmuştur.

- **Karşılaşılan Zorluk ve Çözüm:** Bir kullanıcının birden fazla sitede farklı roller taşıması karışıklık yaratmıştır. Site bağlamına göre rol çözümlemesi yapılması ve yönetim arayüzünde aktif site/rol bilgisinin açık gösterilmesiyle hatalı yetki kullanımı azaltılmıştır.

---

### Gün 9: Strapi Headless CMS Mimari Eğitimi ve Frontend - API Entegrasyonu

- **Teorik Bilgi ve Amaç:** Dokuzuncu gün, Headless CMS mimarisi ve Strapi platformunun veri modeli (Content Types, Single/Collection Types) üzerine yoğunlaşmıştır. Headless yaklaşımda içerik, sunum katmanından bağımsız olarak API üzerinden sunulur; böylece aynı içerik web, mobil veya dijital ekran gibi birden fazla kanala beslenebilir. Strapi’nin otomatik ürettiği RESTful uç noktalar ve JWT tabanlı yetkilendirme, hızlı prototipleme ile güvenli erişim arasında pragmatik bir denge sağlar.

- **Teknik Uygulama ve Mimari Kararlar:** Strapi’de collection type’lar (ör. sayfa, duyuru, medya) tanımlanmış; alan tipleri, ilişkiler ve zorunluluk kuralları ayarlanmıştır. Public/Authenticated rolleri için endpoint izinleri yapılandırılmış; frontend’in `Authorization: Bearer <token>` başlığıyla veri çektiği entegrasyon test edilmiştir.

```js
const res = await fetch(`${STRAPI_URL}/api/pages?populate=*`, {
  headers: { Authorization: `Bearer ${token}` },
});
const { data } = await res.json();
```

`populate` parametresiyle ilişkili medya ve bileşenlerin tek istekte alınması, N+1 benzeri fazla istek sorununu azaltmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** İlk entegrasyonda ilişkili alanların boş gelmesi, `populate` parametresinin eksik bırakılmasından kaynaklanmıştır. Sorgunun açıkça populate edilmesi ve gerekli alanların API yanıtında doğrulanmasıyla frontend’in beklediği veri şekli sağlanmıştır.

---

### Gün 10: Web Güvenliği, OWASP Zafiyet Taramaları ve DLP Yazılım Politikaları

- **Teorik Bilgi ve Amaç:** Onuncu gün web güvenliğinin temel çerçevesi olan OWASP Top 10 (SQL Injection, XSS, CSRF vb.) ve kurumsal DLP (Data Loss Prevention) politikaları incelenmiştir. Güvenli yazılım geliştirme; yalnızca penetrasyon testi sonrası yama değil, tasarım ve kodlama aşamasında tehdit modelleme disiplinidir. DLP kuralları ise hassas verinin kod deposuna, loglara veya web formlarına sızmasını engelleyerek bilgi güvenliği gereksinimlerini yazılım yaşam döngüsüne bağlar.

- **Teknik Uygulama ve Mimari Kararlar:** Örnek zafiyet senaryolarında parametreli sorgular, çıktı kaçışı (output encoding) ve CSRF token kullanımı gözlemlenmiştir. Sızma testi simülasyonlarında tespit edilen bulguların önceliklendirilmesi (severity/risk) ve kapatma adımları takip edilmiştir. DLP kurallarının commit/CI aşamasında gizli anahtar ve kimlik bilgisi desenlerini taradığı görülmüştür.

```js
// XSS'e karşı metni HTML'e yazmadan önce kaçır
el.textContent = userProvidedName; // innerHTML yerine
```

Bu pratikler, “güvenlik sonradan eklenir” yaklaşımı yerine “varsayılan olarak güvenli” geliştirmeyi desteklemiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Test ortamında bilerek bırakılmış zayıf bir sorgu örneğinin tarama raporunda yüksek risk olarak gelmesi ekibi yanıltmıştır. Bulguların ortam etiketine göre sınıflanması ve yalnızca üretim yoluna çıkan kod için zorunlu kapı (quality gate) uygulanmasıyla gürültü azaltılmıştır.

---

## FAZ 3: GÖRÜNTÜ İŞLEME, İÇERİK VE VERİ DOĞRULAMA (Gün 11 – Gün 15)

---

### Gün 11: Python & OpenCV Bilgisayarlı Görü Proje İncelemesi

- **Teorik Bilgi ve Amaç:** On birinci günde Python ve OpenCV ile geliştirilmiş bilgisayarlı görü (computer vision) projesinin kaynak kodları incelenmiştir. Görüntü ön işleme adımları (grayscale, thresholding, edge detection) nesne tespitinin başarısını doğrudan etkiler; aydınlatma, gürültü ve çözünürlük kısıtları algoritma seçimini belirler. Ayrıca finans bilişim süreçlerindeki e-fatura ve ERP entegrasyon API’leri, görüntüden/belgeden yapılandırılmış veriye geçişin kurumsal karşılığı olarak ele alınmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** OpenCV boru hattında görüntünün gri seviyeye çevrilmesi, eşikleme ve kenar bulma adımları adım adım çalıştırılmış; başarısız tespit örneklerinde ön işleme parametrelerinin etkisi analiz edilmiştir. ERP/e-fatura API’lerinin belge meta verisini JSON olarak aldığı entegrasyon noktaları incelenmiştir.

```python
import cv2
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
_, th = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
edges = cv2.Canny(th, 50, 150)
```

Parametrelerin veri kümesine göre ayarlanmasının, sabit eşik değerlerinden daha dayanıklı sonuç verdiği gözlemlenmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Düşük kontrastlı görüntülerde kenar tespitinin aşırı gürültülü çıktığı görülmüştür. Gauss bulanıklaştırma ve Otsu eşikleme kombinasyonuyla gürültü azaltılmış; yine de başarısız örneklerin manuel doğrulama kuyruğuna düşmesi gerektiği değerlendirilmiştir.

---

### Gün 12: Strapi CMS İçerik Yönetimi, UI/UX ve Responsive Web Testleri

- **Teorik Bilgi ve Amaç:** On ikinci gün Strapi yönetim paneli üzerinden dinamik içerik girişi ile duyarlı (responsive) web tasarım testlerine ayrılmıştır. UI/UX uyumu; bilginin farklı ekran boyutlarında okunabilir ve erişilebilir sunulmasını gerektirir. API’den gelen verinin grafik arayüz öğeleriyle doğru eşleşmesi, headless mimaride “içerik-şema-görünüm” sözleşmesinin korunması anlamına gelir.

- **Teknik Uygulama ve Mimari Kararlar:** Strapi’de sayfa/duyuru içerikleri güncellenmiş; draft/publish akışı denenmiştir. Desktop, tablet ve mobil kırılım noktalarında (breakpoints) düzen, tipografi ve navigasyon kontrol edilmiştir. API alanlarının (başlık, kapak, gövde) ilgili UI bileşenlerine bind edildiği doğrulanmıştır.

```css
@media (max-width: 768px) {
  .hero-title { font-size: 1.5rem; }
  .nav-links { display: none; }
}
```

İçerik uzunluğundaki değişimlerin taşma (overflow) yaratıp yaratmadığı özellikle mobil görünümde test edilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Uzun başlık metinlerinin mobil kart bileşeninde taştığı tespit edilmiştir. CSS `line-clamp` ve esnek kutu düzeniyle taşma engellenmiş; içerik editörlerine karakter rehberi paylaşılmıştır.

---

### Gün 13: Dijital Haritalama Yazılımları ve Veritabanı Varlık Güncellemeleri

- **Teorik Bilgi ve Amaç:** On üçüncü günde şematik altyapı verilerinin dijital haritalama / GIS tabanlı yazılımlara aktarımı incelenmiştir. Coğrafi ve mekânsal varlıklar; koordinat ve öznitelik (attribute) verisiyle veritabanında tutulur, web arayüzünde çoğu zaman GeoJSON biçiminde görselleştirilir. Bu model, operasyon ekiplerinin saha varlıklarını tek bir doğruluk kaynağından (single source of truth) izlemesini sağlar.

- **Teknik Uygulama ve Mimari Kararlar:** Varlık kayıtlarının koordinat ve öznitelik alanları güncellenmiş; web harita bileşenine GeoJSON olarak beslenmesi test edilmiştir. Katman görünürlüğü ve öznitelik popup’ları doğrulanmıştır.

```json
{
  "type": "Feature",
  "geometry": { "type": "Point", "coordinates": [28.82, 41.28] },
  "properties": { "name": "Gate A12", "status": "active" }
}
```

Koordinat sisteminin (ör. WGS84) tutarlı kullanımı, katmanların doğru konumda çizilmesi için zorunlu görülmüştür.

- **Karşılaşılan Zorluk ve Çözüm:** Boylam/enlem sırasının (`lng,lat` vs `lat,lng`) karıştırılması noktaların denizde görünmesine yol açmıştır. GeoJSON standardına uygun `[longitude, latitude]` sırası zorunlu kılınarak ve örnek fixture’larla regresyon kontrolü eklenerek hata tekrarının önüne geçilmiştir.

---

### Gün 14: Web Uygulamaları Veri Doğrulama ve Staging/Prod Testleri

- **Teorik Bilgi ve Amaç:** On dördüncü gün form/schema doğrulama (data validation) ile staging–production terfi senaryolarına odaklanmıştır. Kullanıcı girdilerinin sunucu tarafında doğrulanması güvenliğin ve veri kalitesinin temelidir; yalnızca istemci tarafı kontrolü yetersizdir. Staging ortamı, üretim verisine benzer koşullarda risk almadan doğrulama imkânı sunar. API’lerin hatalı isteklerde doğru HTTP durum kodlarını döndürmesi, istemci davranışını öngörülebilir kılar.

- **Teknik Uygulama ve Mimari Kararlar:** Web formlarında zorunlu alan, format ve uzunluk kuralları test edilmiş; API katmanında 400/422 yanıtları doğrulanmıştır. Staging’de yapılan veri değişikliklerinin production’a kontrollü aktarım checklist’i simüle edilmiştir.

```js
if (!email || !/^[^@]+@[^@]+\.[^@]+$/.test(email)) {
  return res.status(422).json({ error: 'Geçersiz e-posta' });
}
```

Başarılı senaryolarda 200/201, yetkisiz erişimde 401/403 kodlarının tutarlılığı kontrol edilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Staging’de kabul edilen bir alanın production şemasında henüz olmaması terfi sonrası hataya yol açma riski yaratmıştır. Şema migrasyonunun uygulama sürümünden önce (veya atomik birlikte) uygulanması kuralı netleştirilerek sıra bağımlılığı yönetilmiştir.

---

### Gün 15: Web Frontend Regresyon Testleri ve Kullanıcı Arayüzü Kontrolleri

- **Teorik Bilgi ve Amaç:** On beşinci günde regresyon testi (regression testing) ile frontend durum yönetimi (state management) denetlenmiştir. Regresyon testinin amacı, yeni bir değişikliğin mevcut işlevleri bozmadığını kanıtlamaktır. Cross-browser testler ise motor farklılıklarının (Chromium, Gecko, WebKit) arayüz davranışını etkilemediğini doğrular. Bileşen durumunun veri güncellemesi sonrası tutarlı kalması, kullanıcı güveni için kritiktir.

- **Teknik Uygulama ve Mimari Kararlar:** Kritik kullanıcı akışları (giriş, içerik listeleme, form gönderimi) güncelleme sonrası yeniden koşturulmuştur. Durum yönetimi açısından yükleme/hata/başarı durumlarının UI’da doğru yansıdığı kontrol edilmiştir. Chrome, Firefox ve Safari/Edge üzerinde duman (smoke) testleri yapılmıştır.

```js
// Basit UI durum makinesi
const ui = { status: 'idle' }; // idle | loading | success | error
async function loadPages() {
  ui.status = 'loading';
  try {
    ui.data = await api.getPages();
    ui.status = 'success';
  } catch (e) {
    ui.status = 'error';
  }
}
```

Bu disiplin, “çalışıyor sanılan” özelliklerin sessizce kırılmasını erken yakalamıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Bir tarayıcıda flex düzeninin farklı kırıldığı görülmüştür. Ortak bir reset/normalize katmanı ve kritik sayfalar için görsel kontrol listesi ile cross-browser sapmalar azaltılmıştır.

---

## FAZ 4: API TESTİ, GÜVENLİ KOD VE KAPANIŞ (Gün 16 – Gün 20)

---

### Gün 16: Postman ile Strapi REST API Endpoint Testleri ve JSON Schema Doğrulama

- **Teorik Bilgi ve Amaç:** On altıncı gün Strapi REST API’lerinin Postman koleksiyonlarıyla test edilmesine ayrılmıştır. API testi; işlevsel doğruluğun yanı sıra yanıt süresi, yetkilendirme başlıkları ve JSON şema uyumunu da kapsar. OpenAPI/Swagger standartları, uç nokta sözleşmesinin ekip içinde tek referans olmasını sağlar. Bearer token ile korunan kaynaklarda yetkisiz isteklerin reddedilmesi güvenlik testinin parçasıdır.

- **Teknik Uygulama ve Mimari Kararlar:** GET/POST/PUT/DELETE senaryoları için Postman istekleri hazırlanmış; `Authorization` başlığı ve örnek gövdeler tanımlanmıştır. Yanıtların beklenen alanları içerdiği, hata kodlarının dokümantasyonla uyumlu olduğu doğrulanmıştır.

```http
GET /api/pages?pagination[pageSize]=10 HTTP/1.1
Host: strapi.local
Authorization: Bearer {{token}}
```

Koleksiyon runner ile temel mutasyon akışı (oluştur → oku → güncelle → sil) otomatik tekrarlanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Token süresinin dolması ardışık testleri kırıyordu. Pre-request script ile token yenileme veya ortam değişkenine güncel token yazma adımı eklenerek suite kararlı hale getirilmiştir.

---

### Gün 17: Güvenli Kod Geliştirme, Input Sanitization ve WAF İncelemesi

- **Teorik Bilgi ve Amaç:** On yedinci günde güvenli kod geliştirme (secure coding), girdi temizleme (input sanitization) ve Web Uygulama Güvenlik Duvarı (WAF) kuralları incelenmiştir. SQLi/XSS gibi saldırılar çoğunlukla doğrulanmamış girdiden beslenir; savunma derinliği (defense in depth) istemci, uygulama ve WAF katmanlarının birlikte çalışmasını gerektirir. DLP ile WAF logları, hem veri sızıntısı hem de saldırı girişimlerinin görünürlüğünü artırır.

- **Teknik Uygulama ve Mimari Kararlar:** Form girdilerinde allowlist tabanlı doğrulama ve çıktı kaçışı uygulanmıştır. WAF üzerinde engellenen şüpheli istek logları incelenmiş; yanlış pozitif (false positive) örnekleri ayrıştırılmıştır. DLP’nin hassas desenleri (kart no, anahtar) engellemesi test edilmiştir.

```js
function sanitize(input) {
  return String(input)
    .replace(/[<>]/g, '')
    .trim()
    .slice(0, 500);
}
```

Uygulama katmanı koruması ile WAF’ın birbirinin yerine değil, tamamlayıcısı olduğu vurgulanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Meşru bir arama sorgusunun WAF tarafından engellenmesi yaşanmıştır. Kural istisnası yerine sorgunun kodlanması/yeniden yazılması ve kuralın daha seçici hale getirilmesiyle hem güvenlik hem kullanılabilirlik korunmuştur.

---

### Gün 18: Web Tabanlı Yönetim Panelleri, Active Directory Entegrasyonu ve LDAP Testleri

- **Teorik Bilgi ve Amaç:** On sekizinci gün kurumsal kimlik doğrulama servisleri (Active Directory / LDAP) ile web yönetim panellerinin entegrasyonu ve Single Sign-On (SSO) mekanizmalarına odaklanmıştır. Merkezi kimlik dizinleri; kullanıcı yaşam döngüsünü (işe giriş/çıkış) uygulamalara yansıtarak hesap kaosunu önler. Oturum yönetimi (session management); oturum süresi, yenileme ve yetkisiz erişim yönlendirmeleriyle tamamlanır.

- **Teknik Uygulama ve Mimari Kararlar:** LDAP üzerinden kullanıcı/grup bilgisinin uygulamaya map edildiği akış incelenmiş; SSO ile tek oturumun birden fazla panele taşınması test edilmiştir. Yetkisiz erişim denemelerinde giriş sayfasına yönlendirme ve uygun HTTP kodları doğrulanmıştır.

```js
// Pseudocode: grup -> rol eşlemesi
const roles = ldapGroups.includes('WebEditors') ? ['editor'] : ['viewer'];
```

Oturum çerezlerinin `HttpOnly` / `Secure` bayraklarıyla korunması güvenlik kontrol listesine alınmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Grup adlarındaki büyük/küçük harf veya OU yolu farklılıkları rol eşlemesini bozmuştur. Normalize edilmiş grup kimlikleri ve yapılandırılabilir mapping tablosu ile eşleme dayanıklı hale getirilmiştir.

---

### Gün 19: Web Frontend Geliştirme, CMS Güncellemeleri ve Kurumsal İletişim

- **Teorik Bilgi ve Amaç:** On dokuzuncu günde frontend revizyonları, Strapi içerik güncellemeleri ve ekipler arası gereksinim netleştirme süreçleri (e-posta / ticketing) birlikte yürütülmüştür. Yazılım geliştirme yalnızca kod üretmek değil; gereksinimin izlenebilir biçimde talep sistemine bağlanmasıdır. Refactoring ve küçük arayüz hata düzeltmeleri, teknik borcu kontrollü biçimde azaltır.

- **Teknik Uygulama ve Mimari Kararlar:** Frontend bileşenlerinde stil/erişilebilirlik düzeltmeleri yapılmış; Strapi’de ilgili içerik alanları güncellenmiştir. Ticketing sisteminde talep–değişiklik–doğrulama döngüsü takip edilmiş; tamamlanan işler ilgili paydaşlara bildirilmiştir.

```js
// Küçük refactor: tekrarlayan sınıf birleşimini yardımcıya taşı
const cx = (...parts) => parts.filter(Boolean).join(' ');
```

Bu gün, teknik iş ile kurumsal iletişim disiplininin aynı teslimatın parçası olduğunu pekiştirmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Sözlü iletilen bir gereksinimin ticket’a eksik yansıması nedeniyle kapsam kayması yaşanmıştır. Kabul kriterlerinin ticket’a yazılması ve “tanım of done” kontrol listesiyle geliştirme başlamadan netleştirme zorunlu hale getirilmiştir.

---

### Gün 20: Yazılım Proje Kodlarının Tamamlanması, Hata Ayıklama ve TAV Akademi Eğitimi

- **Teorik Bilgi ve Amaç:** Yirminci ve son günde staj boyunca üzerinde çalışılan web/CMS işlerinin paketleme, hata ayıklama (debugging) ve son doğrulama adımları tamamlanmıştır. Teslimat (handover); dokümantasyon, bilinen kısıtlar ve sonraki adımların açık bırakılmasını gerektirir. TAV Akademi kapsamında düzenlenen kariyer ve yazılım vizyonu eğitimi ise teknik staj deneyimini kurumsal gelişim perspektifiyle tamamlamıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Açık kalan hatalar yeniden üretilmiş, kök nedenleri log ve debugger ile izlenmiş, kritik olanlar kapatılmıştır. Kod/içerik paketleri ilgili depolara ve CMS ortamlarına aktarılmış; duman testleri tekrarlanmıştır. Staj faaliyetleri özetlenerek 20 günlük yazılım mühendisliği raporu tamamlanmıştır.

```bash
npm run build
npm run test -- --grep "smoke"
git status && git log -5 --oneline
```

Eğitim oturumunda yazılım ekiplerinde iletişim, sürekli öğrenme ve kalite kültürünün önemi vurgulanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Son gün derlemesinde ortam değişkeni eksikliğinin build’i kırması yaşanmıştır. `.env.example` ile zorunlu değişken listesinin güncellenmesi ve teslim checklist’ine “ortam doğrulama” maddesinin eklenmesiyle benzer aksaklıkların önüne geçilmesi hedeflenmiştir.
