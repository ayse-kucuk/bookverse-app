# DONANIM STAJI DEFTERİ

## HAFTA 1: İŞ İSTASYONU (WORKSTATION) VE ÇEVRE BİRİMLERİ DONANIMLARI (Gün 1 – Gün 5)

---

### Gün 1: İş İstasyonlarının Donanım Mimarisinin İncelenmesi — İşlemci Mimarileri ve Anakart Yonga Setleri

- **Teorik Bilgi ve Amaç:** Staj sürecinin ilk gününde, belediye personelinin kullandığı iş istasyonlarının (workstation) donanım mimarisi teorik çerçevede incelenmiştir. Bir iş istasyonu, tüketici sınıfı bilgisayarlardan farklı olarak sürekli yük altında kararlılık ve ECC destekli bileşenler gözetilerek tasarlanır; bu nedenle işlemci mikromimarisinin (Intel Core / AMD Ryzen nesil farkları) ve anakart üzerindeki yonga setinin (chipset) teknik şartnamesinin doğru okunması, donanım seçiminin ilk adımıdır. Yonga setinin, işlemciyi depolama denetleyicileri, USB kontrolcüleri ve genişleme yuvalarına (PCIe lane) bağlayan bir köprü görevi gördüğü, modern platformlarda ise bu görevin büyük ölçüde işlemci paketine entegre edildiği (PCH — Platform Controller Hub mimarisi) değerlendirilmiştir. Bu ilk gün, ilerleyen haftalarda ele alınacak sunucu ve ağ donanımlarının da aynı temel prensipler (veri yolu, önbellek hiyerarşisi, güç bütçesi) üzerine kurulu olduğunu görmek açısından bir zemin oluşturmuştur. Ayrıca işlemci performansının salt çekirdek sayısıyla değil, saat başına işlem (IPC — Instructions Per Cycle) verimliliği ve L1/L2/L3 önbellek hiyerarşisinin boyutuyla birlikte değerlendirilmesi gerektiği, bu nedenle iki işlemcinin yalnızca çekirdek sayısına bakılarak karşılaştırılmasının yanıltıcı olabileceği vurgulanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** İnceleme kapsamında belediye birimlerinde yaygın kullanılan Dell OptiPlex 7090 ve HP EliteDesk 800 G6 iş istasyonları ele alınmış; LGA1200 soket yapısındaki Intel Core i5-10500 işlemci ile Intel Q470 yonga setinin teknik şartname tablosu (TDP, önbellek boyutu, desteklenen bellek frekansı) üretici veri sayfasından karşılaştırılmıştır. Bu karşılaştırma, satın alma öncesi donanım uyumluluğunun (soket, BIOS mikrokod desteği) doğrulanmasının neden kritik olduğunu göstermiştir. İnceleme sırasında CPU-Z benzeri bir donanım tanılama aracıyla anakart, işlemci ve bellek modüllerinin gerçek çalışma değerleri (efektif saat hızı, zamanlama gecikmeleri) okunarak üreticinin beyan ettiği şartname değerleriyle karşılaştırılmış, aradaki tutarlılık teyit edilmiştir.

```
CPU: Intel i5-10500 | Socket: LGA1200 | Chipset: Q470 | TDP: 65W
```

Ayrıca anakart üzerindeki PCIe yuvalarının versiyon (Gen3/Gen4) ve lane sayısının, ileride takılacak ek genişleme kartlarının (ağ kartı, RAID denetleyicisi) performansını doğrudan sınırlayabileceği tespit edilmiştir. Teknik şartname karşılaştırması tamamlandıktan sonra, benzer görevleri yürüten iki farklı model arasındaki gerçek performans farkının PassMark benzeri bir kıyaslama (benchmark) aracıyla da doğrulanması gerektiği, çünkü kâğıt üzerindeki şartnamenin gerçek iş yükü altındaki davranışı tam olarak yansıtmayabileceği not edilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** İki farklı nesil işlemcinin aynı görünen soket adına (LGA1200) sahip olmasına rağmen yonga seti mikrokod desteği farkı nedeniyle bazı anakartlarda çalışmadığı görülmüştür. Sorun, üretici uyumluluk listesinin (QVL — Qualified Vendor List) ve BIOS sürüm notlarının satın alma öncesi kontrol edilmesi gerektiğinin standart prosedüre eklenmesiyle çözülmüştür. Bu vaka, teknik şartname belgelerinin yalnızca referans amaçlı değil, satın alma kararının doğrudan bir girdisi olarak ele alınması gerektiğini somut biçimde ortaya koymuştur.

---

### Gün 2: Bilgisayar Montajı — CPU, RAM, M.2 NVMe SSD Kurulumu ve BIOS/UEFI Yapılandırması

- **Teorik Bilgi ve Amaç:** İkinci günde, yeni işe başlayan personel için sıfırdan bilgisayar montajı ve ilk yapılandırma süreci uygulamalı olarak ele alınmıştır. Bu sürecin teorik temelini, önyükleme (boot) mimarisindeki BIOS ile UEFI (Unified Extensible Firmware Interface) arasındaki fark oluşturmaktadır; UEFI, eski BIOS'un 16-bit gerçek modundan farklı olarak grafik arayüzü, GPT disk bölümleme desteği ve Secure Boot gibi kriptografik önyükleme doğrulama mekanizmalarını sunmaktadır. Ayrıca RAM'in dual-channel mimaride çalışabilmesi için anakart üzerindeki bellek yuvalarının (A1/A2, B1/B2) doğru eşleştirilmesi gerektiği, bunun bellek bant genişliğini teorik olarak iki katına çıkardığı vurgulanmıştır. Bu gün, donanım montajının salt fiziksel bir işlem olmadığı, doğru yapılandırılmadığında donanımın potansiyel performansının kullanılamayacağı gösterilmiştir. Ayrıca Secure Boot mekanizmasının, önyükleme sürecindeki her bir bileşenin (bootloader, çekirdek) dijital imzasını bir önceki katman tarafından doğrulanmış olmasını şart koşarak zincirleme bir güven (chain of trust) kurduğu, bu sayede önyükleme öncesi bulaşan kötücül yazılımların (rootkit) engellenmeye çalışıldığı teorik olarak ele alınmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Montaj sürecinde CPU, ZIF (Zero Insertion Force) soket kolunun kaldırılıp pin hizalama okunun (üçgen işaret) doğru konumlanmasıyla yerleştirilmiş; RAM modülleri performans için A2 ve B2 yuvalarına takılmıştır. M.2 NVMe SSD, PCIe tabanlı olduğu için SATA M.2 modellerinden ayırt edilmesi gereken M-key çentiğine göre anakarta monte edilmiştir. BIOS'a girilerek bellek üreticisinin belirttiği hızda çalışabilmesi için XMP (Extreme Memory Profile) etkinleştirilmiştir.

```
BIOS > Memory > XMP Profile: Profile 1 (DDR4-3200)
BIOS > Boot Mode: UEFI | Secure Boot: Enabled
```

Kurulum sonrasında işletim sistemi GPT disk yapısıyla UEFI modunda kurulmuş, böylece 2TB üzeri disk desteği ve daha hızlı önyükleme süresi sağlanmıştır. Ayrıca kasa içi hava akış yönü göz önünde bulundurularak fan yönleri (ön taraftan içeri, arka taraftan dışarı) kontrol edilmiş, CPU soğutucusunun anakart üzerine montaj baskısının üretici tarafından belirtilen tork/sıra ile eşit dağıtılarak yapıldığı doğrulanmıştır. Montajın son adımında NVMe SSD'nin gerçekten PCIe hattı üzerinden mi yoksa yanlışlıkla SATA modunda mı algılandığı, işletim sistemi disk yönetimi arayüzünden bağlantı arayüzü bilgisi okunarak doğrulanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** RAM modüllerinin ilk montajda yanlışlıkla A1 ve A2 yuvalarına (aynı kanal) takıldığı, bu nedenle dual-channel modun devreye girmediği ve bellek bant genişliğinin beklenenin altında kaldığı fark edilmiştir. Sorun, anakart kullanım kılavuzundaki kanal şeması referans alınarak modüllerin doğru (A2/B2) yuvalara taşınmasıyla giderilmiş, bu deneyim üretici dokümantasyonunun montaj öncesi mutlaka incelenmesi gerektiğini göstermiştir. Değişiklik sonrası BIOS bellek özet ekranından çalışma modunun "Dual Channel" olarak göründüğü teyit edilmiştir.

---

### Gün 3: Donanımsal Arıza Tespiti — POST Ses Kodları, PSU Voltaj Testleri ve MemTest86 ile RAM Doğrulama

- **Teorik Bilgi ve Amaç:** Üçüncü günde, arızalı bildirilen bilgisayarlarda sistematik donanımsal arıza tespiti (hardware troubleshooting) metodolojisi uygulanmıştır. POST (Power-On Self-Test) sürecinin, sistem açılışında BIOS/UEFI firmware'i tarafından CPU, RAM ve grafik biriminin sırayla test edildiği bir öz-tanılama rutini olduğu, herhangi bir bileşen testten geçemediğinde anakart üreticisine özgü kodlanmış bip seslerinin (beep code) verildiği teorik olarak incelenmiştir. Ayrıca güç kaynağının (PSU) ATX 24-pin konnektöründen çıkan +12V, +5V ve +3.3V hatlarının endüstri standardına göre ±%5 tolerans aralığında kalması gerektiği, bu aralığın dışına çıkan voltajların rastgele sistem çökmelerine yol açabileceği değerlendirilmiştir. Arıza tespit sürecinde izlenen sıranın (önce görsel inceleme, ardından güç kaynağı doğrulaması, en son bellek/depolama testi) rastgele parça değişimine kıyasla hem zaman hem maliyet açısından daha verimli olduğu, bunun sistematik arıza ayıklamanın (troubleshooting) temel ilkesi olduğu vurgulanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Arızalı bir iş istasyonunda tekrarlayan üç kısa bip sesi duyulması üzerine, ilgili anakart üreticisinin (AMI BIOS) kod tablosundan bu sesin RAM algılama hatasına işaret ettiği tespit edilmiştir. Bunun üzerine multimetre ile PSU'nun 24-pin konnektöründen +12V ve +5V hatları ölçülmüş, ardından MemTest86 önyüklenebilir USB ortamından çalıştırılarak bellek modülleri sırayla test edilmiştir.

```
PSU 24-pin: +12V=11.94V | +5V=5.02V | +3.3V=3.28V (tolerans icinde)
```

Bu sistematik yaklaşım, arıza kaynağının yazılımsal mı yoksa donanımsal mı olduğunun hızlıca ayrıştırılmasını sağlamıştır. Ölçüm sırasında multimetrenin DC voltaj moduna alınıp siyah probun toprak (GND) pinine, kırmızı probun ise ilgili güç hattına temas ettirilmesi gerektiği, yanlış mod seçiminin hatalı veya hiç okuma alınamamasına yol açacağı uygulamalı olarak deneyimlenmiştir. Ayrıca RAM modüllerinin tek tek (birer birer) tekrar test edilmesinin, arızalı olan spesifik modülün toplu testte gizlenen bir hatayı maskeleyebileceği ihtimaline karşı önemli bir teşhis adımı olduğu değerlendirilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** MemTest86'nın ilk geçişinde (pass) herhangi bir hata bildirmemesine rağmen bilgisayarın yoğun kullanımda çökmeye devam ettiği görülmüştür. Bu durum, bazı bellek hatalarının yalnızca ısınma sonrası ortaya çıkabileceği bilgisiyle değerlendirilmiş; testin sekiz geçişin üzerine çıkarılarak uzatılmasıyla ilgili modülde ısıya bağlı aralıklı (intermittent) bir hata tespit edilmiş ve modül değiştirilmiştir. Bu deneyim, kısa süreli testlerin donanım güvenilirliğini garanti etmediğini, kritik sistemlerde uzatılmış (burn-in) testlerin gerekliliğini göstermiştir.

---

### Gün 4: Çevre Birimlerinin Donanımsal Kurulumu ve Ağa Entegrasyonu — Yazıcılar, Tarayıcılar, IP Telefonlar

- **Teorik Bilgi ve Amaç:** Dördüncü günde, iş istasyonlarına bağımlı olmadan doğrudan ağ üzerinden hizmet veren çevre birimlerinin (network peripherals) donanımsal kurulumu ele alınmıştır. Ağa bağlı yazıcı, tarayıcı ve IP telefonların, geleneksel USB bağlantılı emsallerinden farklı olarak kendi ağ arayüz kartına ve gömülü işletim sistemine sahip olduğu, bu sayede birden fazla kullanıcı tarafından eş zamanlı erişilebildiği teorik temel olarak açıklanmıştır. IP telefonların ayrıca ses trafiğini veri trafiğinden ayıran VLAN (Voice VLAN) mimarisi üzerinden çalıştığı, bunun gecikme (latency) ve jitter değerlerini düşürerek ses kalitesini koruduğu değerlendirilmiştir. Ağ yazıcılarında yaygın kullanılan LPD/IPP protokollerinin, yazdırma işini istemciden alıp kendi dahili kuyruğunda yönettiği, bu sayede istemci bilgisayarın kapanmasının bekleyen yazdırma işini etkilemediği de teorik çerçeveye eklenmiştir. VLAN kavramının donanımsal olarak, tek bir fiziksel switch üzerinde birden fazla mantıksal ağ oluşturmayı sağladığı; 802.1Q etiketleme standardının çerçevelere VLAN kimliği ekleyerek switch'lerin trafiği doğru segmentlere yönlendirmesine imkân tanıdığı da açıklanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Yeni kurulan ağ yazıcısına statik IP adresi, alt ağ maskesi ve varsayılan ağ geçidi bilgileri tanımlanarak kurum içi DHCP kapsamının dışında sabit bir kimlik verilmiştir. Ağ tarayıcı üzerinde SMB (scan-to-folder) paylaşım yapılandırması tamamlanmış, IP telefon ise switch portu üzerinde tanımlı ses VLAN'ına otomatik atanacak şekilde LLDP-MED protokolü ile yapılandırılmıştır.

```
Printer: IP 192.168.10.50 | Mask 255.255.255.0 | GW 192.168.10.1
```

Bu yapılandırma, çevre birimlerinin ağ yöneticisi tarafından merkezi olarak izlenebilir ve arızalara hızlı müdahale edilebilir hale gelmesini sağlamıştır. Tarayıcı cihazının firmware sürümü de bu süreçte kontrol edilmiş, güncel olmayan firmware'in bazı ağ protokollerinde uyumsuzluk çıkarabileceği bilgisiyle cihaz üretici sunucusundan güncellenmiştir. Kurulumun ardından her cihazın ping ve web arayüzü erişilebilirliği ayrı ayrı doğrulanarak, yalnızca fiziksel bağlantının değil ağ katmanı yapılandırmasının da doğru tamamlandığı teyit edilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Yeni kurulan IP telefonun switch'e bağlandığında açılmadığı fark edilmiş, incelemede telefonun harici adaptörü olmayan bir PoE (Power over Ethernet) modeli olduğu, bağlandığı switch portunun ise PoE+ desteklemediği belirlenmiştir. Sorun, telefonun PoE+ destekli başka bir porta taşınmasıyla giderilmiş, bu deneyim çevre birimi siparişlerinde güç besleme gereksinimlerinin önceden switch envanteriyle karşılaştırılması gerektiğini göstermiştir. Bu vaka aynı zamanda çevre birimi kurulum kontrol listesine bir "PoE sınıfı doğrulama" adımının eklenmesine yol açmıştır.

---

### Gün 5: Kesintisiz Güç Kaynaklarının (UPS) Donanımsal Yapısının İncelenmesi — Akü Bakımı ve Yük Testleri

- **Teorik Bilgi ve Amaç:** Altyapı fazının son gününde, kritik sistemlerin ani elektrik kesintilerine karşı korunmasını sağlayan Kesintisiz Güç Kaynağı (UPS) donanımı incelenmiştir. Line-interactive ve online double-conversion olmak üzere iki temel UPS topolojisinin teorik farkı ele alınmış; online tipte şebeke gerilimi sürekli olarak DC'ye çevrilip ardından tekrar AC'ye dönüştürüldüğü için çıkışta anlık kesinti (transfer time) oluşmadığı, bu nedenle sunucu odaları gibi kritik ortamlarda tercih edildiği değerlendirilmiştir. Ayrıca UPS kapasitesinin VA (Volt-Amper) ve gerçek Watt değeri arasındaki farkın güç faktörü (power factor) ile ilişkili olduğu, kapasite planlamasında bu ayrımın gözetilmesi gerektiği vurgulanmıştır. Line-interactive modelde ise şebeke geriliminin doğrudan yüke aktarıldığı, yalnızca kesinti anında milisaniyeler içinde aküye geçiş yapıldığı; bu geçiş süresinin çoğu bilgisayar için sorun oluşturmasa da hassas sunucu güç kaynaklarında anlık kesintiye yol açabileceği belirtilmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** Sistem odasındaki online double-conversion UPS ünitesinin VRLA (Valve Regulated Lead-Acid) tipi akü grubu incelenmiş, akülerin gerilim ve iç direnç (impedance) değerleri özel bir akü test cihazıyla ölçülmüştür. Ardından, yüksek gerilimle çalışılması nedeniyle elektrik bakım personelinin gözetiminde, UPS'in beyan gücünün belirli bir yüzdesine karşılık gelen bir yük bankası (load bank) bağlanarak, akünün beyan edilen çalışma süresini (runtime) gerçekten karşılayıp karşılamadığı test edilmiştir. Test sırasında UPS'in şebeke gerilimini kapatan bir simülasyon anahtarı yardımıyla aküye geçişi tetiklenmiş, geçiş anında bağlı yükte herhangi bir kesinti veya yeniden başlama (reboot) yaşanmadığı gözlemlenerek online topolojinin sıfır aktarım süresi iddiası doğrulanmıştır.

```
UPS Kapasite: 3000VA x 0.9 (PF) = 2700W gercek guc
```

Bu test, teorik kapasite değerinin gerçek yük altında doğrulanmasının, kritik sistemler için hayati önem taşıdığını somut biçimde göstermiştir. Ayrıca UPS'in kendi dahili kendi kendini test (self-test) rutininin periyodik olarak tetiklenmesinin, akü sağlığının sürekli izlenmesi açısından yük bankası testine tamamlayıcı bir önlem olduğu not edilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Üç yıllık akü grubunun, üretici tarafından beyan edilen çalışma süresinin belirgin biçimde altında kaldığı tespit edilmiştir. Bu durum, kurşun-asit akülerin şarj/deşarj döngüleriyle kapasite kaybına uğradığı (aging) bilgisiyle açıklanmış; iç direnç ölçüm sonuçlarının eşik değeri aşması nedeniyle akü grubunun planlı bakım kapsamında değiştirilmesi kararlaştırılmıştır. Bu vaka, akü ömrünün takviminden ziyade periyodik ölçümle takip edilmesi gerektiğini göstermiştir.

---

## HAFTA 2: AĞ (NETWORK) DONANIMLARI VE ALTYAPI (Gün 6 – Gün 10)

---

### Gün 6: Ağ Altyapısının Fiziksel Topolojisinin İncelenmesi — Switch, Router ve Access Point Konumlandırması

- **Teorik Bilgi ve Amaç:** İkinci haftanın ilk gününde, e-belediye hizmetlerinin ve kurum içi bilgi sistemlerinin üzerinde yükseldiği, belediyenin en kritik donanım bacağı olan ağ altyapısının fiziksel topolojisi ele alınmıştır. OSI referans modelinin fiziksel katmanı (Layer 1) ve veri bağı katmanı (Layer 2) teorik olarak incelenmiş; switch cihazlarının MAC adres tablosu üzerinden çerçeve (frame) yönlendirdiği, router cihazlarının ise IP adresleme üzerinden ağlar arası (Layer 3) yönlendirme yaptığı ayrımı netleştirilmiştir. Ayrıca kurumsal ağlarda yaygın kullanılan çekirdek-dağıtım-erişim (core-distribution-access) katmanlı mimarinin, ağ trafiğini hiyerarşik olarak yöneterek arıza izolasyonunu ve ölçeklenebilirliği kolaylaştırdığı değerlendirilmiştir. Router cihazlarının ayrıca farklı ağlar arasında geçiş yaparken paket başlığındaki TTL (Time To Live) değerini azaltarak sonsuz döngüye giren paketlerin ağda kalıcı olarak dolaşmasını engellediği, bunun ağ katmanı tasarımının temel güvenlik önlemlerinden biri olduğu değerlendirilmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** Belediye hizmet binasının fiziksel ağ topolojisi incelenmiş; sistem odasındaki bir adet çekirdek switch'in (Cisco Catalyst 9300) katlara dağıtılmış erişim switch'lerine fiber uplink ile bağlandığı, kablosuz erişimin ise koridorlara yerleştirilmiş Ubiquiti UniFi access point cihazları üzerinden sağlandığı tespit edilmiştir. Access point yerleşiminin, sinyal örtüşmesini en aza indirecek ve ölü bölge (dead zone) bırakmayacak şekilde bina planı üzerinde işaretlendiği görülmüştür. Ayrıca komşu access point'lerin farklı kanallara (2.4 GHz bandında 1/6/11) atandığı, bu kanal planlamasının komşu cihazlar arası girişimi (co-channel interference) azaltarak kablosuz performansı artırdığı teknik olarak doğrulanmıştır.

```
Core Switch (Katman 3) --fiber uplink--> Access Switch (Kat 2) --> AP
```

Bu hiyerarşik yapı, ileride tek bir erişim switch'inde yaşanacak arızanın tüm ağı değil yalnızca ilgili katı etkilemesini sağlamaktadır. Fiber uplink hatlarında kullanılan SFP modüllerinin, bakır Ethernet'e kıyasla çok daha uzun mesafelerde sinyal kaybı yaşamadan veri iletebildiği, bu nedenle katlar arası omurga bağlantılarında bakır yerine fiber tercih edildiği gözlemlenmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Binanın belirli koridorlarında kablosuz sinyal gücünün kabul edilebilir seviyenin altına düştüğü fark edilmiştir. Sorun, bir kablosuz site anketi (Wi-Fi heatmap survey) yapılarak zayıf sinyal bölgelerinin haritalanması ve bu bölgelere ek access point konumlandırılmasının planlanmasıyla çözüme kavuşturulmuştur. Bu deneyim, kablosuz kapsama planlamasının bina mimarisi üzerinde deneysel olarak doğrulanması gerektiğini, teorik kapsama yarıçapına körü körüne güvenilemeyeceğini göstermiştir.

---

### Gün 7: Ethernet Kablolama Standartları — CAT6/CAT7, T568A/T568B Renk Kodları ve RJ45/Patch Panel Sonlandırma

- **Teorik Bilgi ve Amaç:** Yedinci günde, ağ altyapısının fiziksel katmanını oluşturan bakır kablolama standartları uygulamalı olarak incelenmiştir. Bükümlü çift (twisted pair) kablo kategorilerinin (CAT6, CAT7) birbirinden bant genişliği ve iç kılıflama (shielding) açısından ayrıştığı, CAT7'nin her bir çiftin ayrı ayrı ekranlanmasıyla (S/FTP) çapraz konuşmayı (crosstalk) CAT6'ya kıyasla daha etkin bastırdığı teorik olarak ele alınmıştır. Ayrıca T568A ve T568B olmak üzere iki farklı pin renk sıralaması standardının var olduğu, bir ağda hangisi seçilirse seçilsin kablonun iki ucunda da aynı standardın tutarlı uygulanması gerektiği vurgulanmıştır. Kablonun her iki ucunda aynı standart yerine bilinçli olarak T568A ve T568B karışık kullanılması durumunda ise bir "çapraz (crossover) kablo" elde edildiği, modern cihazların Auto-MDIX özelliği sayesinde bu ayrıma artık büyük ölçüde ihtiyaç duymadığı da teorik not olarak eklenmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** Uygulamalı olarak CAT6 kablo üzerinde T568B renk sıralamasına göre RJ45 konnektör çakma (crimping) işlemi gerçekleştirilmiş, ardından kablonun diğer ucu patch panel üzerinde punch-down (110 blok) aracıyla sonlandırılmıştır.

```
T568B: Turuncu/Beyaz-Turuncu-Yesil/Beyaz-Mavi-Mavi/Beyaz-Yesil-Kahve/Beyaz-Kahve
```

Sonlandırılan hat, bir kablo sertifikasyon cihazı (Fluke tarzı test cihazı) ile test edilerek iletim (continuity), çapraz konuşma ve kablo uzunluğu parametreleri standartlara uygunluk açısından doğrulanmıştır. Patch panel üzerindeki her port ayrıca kalıcı bir etiketleme şemasıyla (kat-oda-priz numarası) işaretlenmiş, böylece ileride yapılacak bir müdahalede fiziksel kablonun ağ diyagramındaki karşılığının anında bulunabilmesi sağlanmıştır. Bu doğrulama adımının, görsel olarak sağlam görünen bir bağlantının elektriksel olarak da uygun olduğunu garanti etmediği için zorunlu olduğu değerlendirilmiştir. Ayrıca kablo uzunluğunun CAT6 standardında maksimum 100 metre (segment başına) ile sınırlı olduğu, bu mesafenin aşılması durumunda sinyal zayıflamasının (attenuation) veri hatalarına yol açabileceği patch panel sonlandırması sırasında dikkate alınmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Test cihazında bir hatta "split pair" (ayrık çift) hatası bildirilmiştir; bu durum, doğru renklerin kullanılmasına rağmen pinlerin yanlış çiftlerden eşleştirilmesinden kaynaklanmaktadır ve görsel kontrolle fark edilmesi zordur. Sorun, konnektörün kesilip T568B standardına tam sadık kalınarak yeniden çakılmasıyla giderilmiş, elektriksel testin görsel kontrolden neden vazgeçilmez olduğu bir kez daha doğrulanmıştır.

---

### Gün 8: Yönetilebilir Ağ Anahtarlarında Fiziksel Port Yapılandırması ve PoE Kapasite Hesaplamaları

- **Teorik Bilgi ve Amaç:** Sekizinci günde, yönetilebilir ağ anahtarlarının (managed switch) donanımsal port yapılandırması ve güç üzerinden veri (Power over Ethernet — PoE) standartları incelenmiştir. PoE'nin 802.3af, 802.3at ve 802.3bt olmak üzere farklı nesillerde farklı güç bütçeleri sunduğu, bir switch'in toplam PoE bütçesinin bağlı tüm cihazların (IP telefon, access point, IP kamera) talep ettiği gücün toplamını karşılayamadığı durumda bazı portların güç alamayacağı teorik olarak değerlendirilmiştir. Bu nedenle switch seçiminin yalnızca port sayısına değil, toplam PoE güç bütçesine göre de yapılması gerektiği sonucuna varılmıştır. Ayrıca PoE'nin, veri hattıyla aynı bükümlü çift kablo üzerinden düşük gerilimli DC gücü ilettiği, cihazın önce switch ile bir "sınıflandırma" (classification) el sıkışması yaparak ihtiyaç duyduğu güç sınıfını bildirdiği, switch'in ancak bu bildirim sonrası ilgili porta güç verdiği teorik olarak açıklanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Ağ sorumlusu personelin gözetiminde, switch'in konsol portuna seri kablo ile bağlanılıp komut satırı arayüzüne (CLI) erişilerek belirli portlara VLAN ataması ve PoE güç sınırı tanımlanmıştır. Switch üzerinde bağlı cihazların topladığı toplam PoE tüketimi izlenmiş ve switch'in beyan edilen 370W bütçesiyle karşılaştırılmıştır. Yönetim arayüzündeki port bazlı güç tüketim raporu incelenerek, hangi cihazın gerçek tüketiminin beyan edilen sınıfın (PoE class) çok altında kaldığı belirlenmiş, bu bilgi ileride kapasite planlamasında kullanılmak üzere kayıt altına alınmıştır.

```
Switch(config)# interface gi1/0/5
Switch(config-if)# power inline auto max 15400
```

Bu yapılandırma sürecinde, düşük öncelikli cihazlara (örneğin dekoratif bir ekran) bağlı portların güç sınırının bilinçli olarak düşürülmesinin, kritik cihazların (IP telefon) güç kesintisi riskini azalttığı gözlemlenmiştir. Switch üzerindeki port bazlı VLAN ataması ayrıca ses ve veri trafiğinin aynı fiziksel kablo üzerinde mantıksal olarak ayrıştırılmasını sağlayarak, yayın (broadcast) trafiğinin gereksiz yere tüm cihazlara ulaşmasının da önüne geçmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Yeni access point cihazlarının eklenmesiyle switch'in toplam PoE talebinin beyan edilen bütçeyi aşmaya başladığı fark edilmiştir. Sorun, öncelik sırası düşük olan bir kısım portun PoE gücünün manuel olarak sınırlandırılması ve orta vadede ek bir PoE enjektör cihazının devreye alınmasının planlanmasıyla yönetilebilir hale getirilmiştir.

---

### Gün 9: Donanımsal Güvenlik Duvarı Cihazlarının Fiziksel Montajı ve Ağ Geçidi Yapılandırması

- **Teorik Bilgi ve Amaç:** Dokuzuncu günde, ağın dış dünyaya açılan noktasını koruyan donanımsal güvenlik duvarı (hardware firewall) cihazları incelenmiştir. Yazılımsal bir güvenlik duvarından farklı olarak donanımsal modellerin (FortiGate, Sophos XG gibi), paket incelemesini işletim sisteminin genel işlemcisi yerine özel amaçlı ASIC/NPU çipleri üzerinden yürüttüğü, bunun yüksek trafik hacminde gecikmeyi önemli ölçüde azalttığı teorik olarak ele alınmıştır. Ayrıca durumlu paket incelemesinin (stateful inspection) yalnızca tek bir paketi değil, bağlantının tüm oturum durumunu (connection state) takip ederek karar verdiği vurgulanmıştır. Firewall'un ağ segmentasyonundaki rolü de ele alınmış; farklı güvenlik seviyesine sahip bölgeler (DMZ, iç ağ, misafir ağı) arasındaki trafiğin varsayılan olarak reddedilip yalnızca açıkça izin verilen trafiğin geçmesine olanak tanıyan "varsayılan reddet" (default-deny) prensibinin donanımsal güvenlik duvarlarının temel işletim mantığı olduğu değerlendirilmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** Bilgi İşlem Müdürlüğü'nden sorumlu ağ personelinin refakatinde, yeni bir FortiGate cihazı sistem odasındaki rack'e monte edilmiş, WAN arayüzü internet servis sağlayıcısının hattına, LAN arayüzü ise çekirdek switch'e bağlanmıştır. Ağ geçidi (gateway) rolü üstlenen cihaz üzerinde temel NAT ve trafik yönlendirme kuralları tanımlanmış, ayrıca ikinci bir WAN bağlantısı için failover yapılandırması hazırlanmıştır. Cihazın güç kaynağı yedekliliği için ikinci bir dahili PSU seçeneği de değerlendirilmiş, kritik ağ geçidi cihazlarında tek nokta arızasının (single point of failure) tüm belediye binasının internet ve e-belediye hizmet erişimini etkileyebileceği gerekçesiyle bu seçeneğin gelecek bütçe planına dahil edilmesi önerilmiştir.

```
config system interface
  edit "wan1"
    set ip 203.0.113.10 255.255.255.0
```

Bu montaj sırasında cihaz üzerindeki tüm portların WAN/LAN etiketleriyle fiziksel olarak da işaretlenmesinin, ileride yapılacak bakımlarda hata riskini azalttığı değerlendirilmiştir. Ayrıca cihazın yönetim arayüzüne yalnızca iç ağdan erişilebilecek şekilde kısıtlanması, dış dünyadan doğrudan yönetim paneline erişim denemelerinin önlenmesi açısından ek bir donanımsal/yapılandırmasal güvenlik katmanı olarak değerlendirilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** İlk yapılandırma sırasında WAN ve LAN arayüzlerinin fiziksel olarak yanlış portlara bağlanması nedeniyle belediye genelinde kısa süreli bir internet kesintisi yaşanmıştır. Sorun, cihaz arayüzlerinin etiketleriyle fiziksel port numaralarının tekrar karşılaştırılıp doğru şekilde yeniden bağlanmasıyla giderilmiş; bu deneyim kritik cihaz değişikliklerinin bakım penceresi (maintenance window) içinde yapılması gerekliliğini pekiştirmiştir.

---

### Gün 10: Sistem Odası Fiziksel Altyapısının, İklimlendirme Sistemlerinin ve Ortam İzleme Sensörlerinin Donanımsal Kontrolü

- **Teorik Bilgi ve Amaç:** İkinci haftanın son gününde, ağ ve sunucu donanımlarını barındıran sistem odasının fiziksel altyapısı incelenmiştir. Veri merkezi soğutma teorisinde yaygın kullanılan sıcak koridor/soğuk koridor (hot aisle/cold aisle) düzeninin, soğuk havanın cihaz ön yüzünden emilip sıcak havanın arka taraftan tahliye edilmesini sağlayarak soğutma verimliliğini artırdığı ele alınmıştır. Ayrıca ASHRAE gibi endüstri standartlarının önerdiği sıcaklık (18-27°C) ve bağıl nem aralıklarının, donanım ömrünü ve kararlılığını doğrudan etkilediği teorik olarak değerlendirilmiştir. Aşırı düşük nemin statik elektrik boşalması (ESD) riskini artırdığı, aşırı yüksek nemin ise devre kartlarında yoğuşmaya ve korozyona yol açabileceği; bu nedenle sıcaklık kadar nem oranının da sıkı bir aralıkta tutulması gerektiği vurgulanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Sistem odasındaki CRAC (Computer Room Air Conditioner) ünitesinin hava akış yönü ve kapasitesi incelenmiş, rack kabinlerindeki boş U alanlarının kör panel (blanking panel) ile kapatılıp kapatılmadığı kontrol edilmiştir. Oda geneline yerleştirilmiş sıcaklık ve nem sensörlerinin eşik değerleri gözden geçirilmiş, belirlenen sınırların aşılması durumunda uyarı gönderecek şekilde izleme sistemi yapılandırılmıştır. Ayrıca kapı açık kalma sensörü ve su baskını (leak detection) şeridi gibi ek fiziksel güvenlik sensörlerinin de aynı izleme paneline entegre edildiği, böylece sistem odasının tek bir merkezi ekrandan bütünsel biçimde denetlenebildiği görülmüştür.

```
Sensor Threshold: Sicaklik > 27C VEYA Nem > %60 -> Alarm
```

Bu kontroller, donanım arızalarının büyük bir kısmının doğrudan elektronik hatadan değil, uygunsuz ortam koşullarından kaynaklandığı gerçeğini somutlaştırmıştır. CRAC ünitesinin filtrelerinin de bu denetim kapsamında incelendiği, tıkanmış bir filtrenin hava akış hacmini düşürerek soğutma kapasitesini teoride olduğundan daha düşük bir seviyeye çektiği belirlenmiştir. Sensörlerin izleme yazılımına gönderdiği verilerin zaman içinde grafiklenmesinin, ani bir arızadan önce ortam koşullarında oluşan kademeli bozulma eğilimlerinin (trend) fark edilmesine de imkân tanıdığı değerlendirilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Sıcak koridorda ölçülen sıcaklığın beklenenin üzerinde olduğu tespit edilmiştir. İncelemede, rack kabinlerindeki bazı boş U alanlarında kör panel bulunmadığı, bu nedenle sıcak havanın soğuk koridora sızarak (recirculation) soğutulmuş havayla karıştığı belirlenmiştir. Sorun, eksik kör panellerin tamamlanmasıyla giderilmiş ve koridor sıcaklık farkı normale dönmüştür.

---

## HAFTA 3: SUNUCU (SERVER) DONANIMLARI VE VERİ DEPOLAMA (Gün 11 – Gün 15)

---

### Gün 11: Rack Tipi Sunucuların Fiziksel Yapısı — Kabin Montajı, Kızak Sistemleri ve Kablo Yönetimi

- **Teorik Bilgi ve Amaç:** Üçüncü haftanın ilk gününde, yazılımların üzerinde çalıştığı fiziksel sunucuların rack tipi kurulum standartları incelenmiştir. Rack unit (U) kavramının, bir sunucu kabinindeki dikey alanı standart 4,45 cm'lik birimlerle ölçtüğü, sunucu form faktörlerinin (1U, 2U, 4U) bu birime göre tanımlandığı teorik olarak ele alınmıştır. Ayrıca kablo yönetiminin yalnızca estetik bir kaygı olmadığı; düzensiz kablolamanın hava akışını engelleyerek soğutma verimliliğini düşürdüğü ve arıza anında ilgili kablonun tespitini zorlaştırdığı vurgulanmıştır. Rack kabinlerinin standart 19 inç genişlikte tasarlandığı, bu standardizasyonun farklı üreticilerin sunucu ve switch cihazlarının aynı kabine sorunsuz monte edilebilmesini sağlayan bir uyumluluk katmanı oluşturduğu da teorik çerçeveye eklenmiştir. Kabin içi ağırlık dağılımının fiziksel kararlılık açısından önemi de değerlendirilmiş; en ağır donanımların kabinin alt kısmına yerleştirilmesinin devrilme riskini azalttığı ve dolu bir rack kabininin döşeme yük taşıma kapasitesiyle de uyumlu olması gerektiği vurgulanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Yeni teslim alınan bir Dell PowerEdge R740 (2U) sunucusu, önce rack kabinine kızak (rail kit) sistemi vida ile sabitlenerek, ardından sunucunun bu kızaklar üzerine kaydırılıp kilitlenmesiyle monte edilmiştir. Sunucu arkasına kablo yönetim kolu (cable management arm) takılarak, güç ve ağ kablolarının sunucu rack'ten dışarı çekilse dahi zarar görmeyecek şekilde esnek biçimde yönlendirilmesi sağlanmıştır. Sunucunun rack üzerindeki konumu belirlenirken ayrıca üst ve alt komşu cihazlarla arasında servis (bakım) için yeterli erişim boşluğu bırakılmasına, ağır cihazların ise devrilme riskini azaltmak amacıyla kabinin alt bölümüne yerleştirilmesine dikkat edilmiştir.

```
Rack Unit (1U) = 44.45 mm | PowerEdge R740 = 2U (88.9 mm)
```

Kablolar ayrıca güç ve veri hatları ayrı kablo kanallarından geçirilerek elektromanyetik girişim riski en aza indirilmiştir. Sunucunun ön panelindeki hava girişinin önünde herhangi bir kablo demeti bulunmadığı, böylece iç fanların emdiği havanın kablo yığınıyla engellenmediği ayrıca kontrol edilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Kızak sisteminin vida noktalarının rack kabinindeki delik aralığıyla tam örtüşmediği, bu nedenle sunucunun rack'e tam oturmadığı fark edilmiştir. Sorun, rail kit kılavuzunda belirtilen kare delik (square hole) standardının kabin tipiyle karşılaştırılıp doğru vida noktalarının yeniden hizalanmasıyla çözülmüştür.

---

### Gün 12: Sunucu Anakartları — Çoklu İşlemci Mimarileri, ECC RAM Teknolojisi ve Yedekli Güç Kaynakları

- **Teorik Bilgi ve Amaç:** On ikinci günde, kurumsal sunucu anakartlarının masaüstü anakartlarından ayrışan mimari özellikleri incelenmiştir. Xeon ve EPYC gibi sunucu işlemcilerinin çoklu soket (multi-socket) yapılandırmayı desteklediği, bu mimaride NUMA (Non-Uniform Memory Access) mantığının devreye girdiği, yani her işlemcinin kendi yerel belleğine diğerinden daha hızlı eriştiği teorik olarak ele alınmıştır. Ayrıca ECC (Error-Correcting Code) RAM teknolojisinin, bellekte oluşabilecek tek bitlik hataları donanımsal olarak tespit edip düzelttiği, bunun 7/24 çalışan sunucularda veri bütünlüğü için hayati olduğu değerlendirilmiştir. Standart RAM'in yalnızca 8 veri biti taşıdığı, ECC RAM'in ise her bayt grubuna ek bir parite/hata düzeltme biti ekleyerek kozmik ışın veya elektriksel gürültü kaynaklı bit hatalarını sessizce (kullanıcı fark etmeden) düzelttiği teorik olarak açıklanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Sunucu anakartı üzerinde iki adet Xeon Silver işlemci soketinin ve bu soketlere yakın konumlandırılmış ECC RDIMM bellek yuvalarının yerleşimi incelenmiştir. Sistem yöneticisinin onayı ve gözetiminde, sunucunun yedekli güç kaynağı (Redundant PSU, 1+1) yapılandırması test edilmiş; iki PSU'dan biri fiziksel olarak çıkarılarak sunucunun kesintisiz çalışmaya devam edip etmediği doğrulanmıştır. NUMA mimarisinin pratikteki etkisini gözlemlemek amacıyla, bir uygulamanın hangi CPU soketine ve o soketin yerel belleğine atandığı işletim sistemi araçlarıyla incelenmiş, yanlış NUMA düğümünden bellek erişiminin (remote memory access) gecikmeyi artırdığı teorik bilgiyle ilişkilendirilmiştir.

```
$ ipmitool sensor | grep PSU
PS1 Status  | 0x01 | ok
```

Bu test, yedeklilik (redundancy) kavramının yalnızca teoride var olmadığını, fiziksel olarak doğrulanması gerektiğini göstermiştir. Ayrıca iki PSU'nun ayrı elektrik hatlarına (farklı priz grubu/UPS çıkışı) bağlanmasının, tek bir elektrik hattı kesintisinde dahi sunucunun çalışmaya devam edebilmesi için yedekliliğin tam anlamıyla sağlanmasında ikinci bir kritik koşul olduğu değerlendirilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** PSU'lardan birinin aslında önceden sessizce arızalanmış olduğu, ancak yedekliliğin bu arızayı kullanıcıdan gizleyerek fark edilmesini geciktirdiği ortaya çıkmıştır. Sorun, iDRAC üzerinden PSU durum alarmlarının e-posta bildirimiyle aktifleştirilmesi ve arızalı PSU'nun değiştirilmesiyle çözülmüş; yedekliliğin izlemeden bağımsız güvenilemeyeceği sonucuna varılmıştır.

---

### Gün 13: Donanımsal RAID Kontrolcüleri — RAID 0, 1, 5 ve 10 Mimarilerinin Fiziksel Disklerde Yapılandırılması

- **Teorik Bilgi ve Amaç:** On üçüncü günde, sunucu depolamasının donanımsal güvenilirliğini sağlayan RAID (Redundant Array of Independent Disks) kontrolcüleri incelenmiştir. RAID 0'ın veriyi diskler arasında bölerek (striping) yalnızca performans artışı sağladığı ancak yedeklilik sunmadığı, RAID 1'in aynalama (mirroring) yoluyla veriyi ikinci bir diske birebir kopyaladığı, RAID 5'in ise veriyi ve hesaplanan parite bilgisini diskler arasında dağıtarak tek disk arızasına karşı koruma sağladığı, RAID 10'un ise mirror çiftlerinin striping ile birleştirilmesiyle hem performans hem yedeklilik sunduğu teorik olarak karşılaştırılmıştır. Donanımsal RAID kontrolcüsünün, bu hesaplamaları (özellikle parite) ana işlemciye yük bindirmeden kendi üzerindeki özel bir işlemci (RAID-on-Chip) ile yürüttüğü, bunun yazılımsal RAID çözümlerine kıyasla temel avantajı olduğu belirtilmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** Sistem yöneticisinin gözetiminde, test amacıyla ayrılmış bir sunucu üzerindeki donanımsal RAID kontrolcüsünün (PERC) önyükleme öncesi yapılandırma ekranına girilerek altı adet SAS disk üzerinde RAID 5 dizisi (array) oluşturulmuş, kontrolcünün önbellek (cache) modülünün pil destekli (BBU — Battery Backup Unit) olduğu doğrulanmıştır. Dizi oluşturulduktan sonra sanal disk (virtual disk) başlatma modu olarak "Full Initialization" seçilmiş, bu sürecin tüm disk yüzeyini sıfırlayarak dizinin baştan tutarlı bir parite durumunda başlamasını sağladığı gözlemlenmiştir.

```
$ perccli /c0 add vd type=r5 drives=0:1-6
```

Bu doğrulama önemlidir, çünkü BBU'suz bir önbellek, ani elektrik kesintisinde yazma işlemi tamamlanmamış (dirty) verinin kaybolmasına yol açabilmektedir. Kontrolcü üzerindeki önbelleğin yazma politikasının (write-back / write-through) da incelenmesi gerektiği; write-back modunun performans avantajı sunarken BBU koruması olmadan veri kaybı riski taşıdığı, write-through modunun ise her yazmayı doğrudan diske ilettiği için daha güvenli ama daha yavaş olduğu karşılaştırılmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** RAID 5 dizisinde yoğun yazma testleri sırasında beklenenden düşük performans gözlemlenmiştir. Bu durum, her yazma işleminde parite değerinin yeniden hesaplanmasının donanımsal bir maliyet getirdiği bilgisiyle açıklanmış; yazma yoğunluğu yüksek iş yükleri için orta vadede RAID 10 mimarisine geçişin daha uygun olacağı sonucuna varılmıştır. Bu değerlendirme, RAID mimarisi seçiminin salt kapasite verimliliğine değil, hedeflenen iş yükünün okuma/yazma oranına göre de yapılması gerektiğini göstermiştir.

---

### Gün 14: Ağ Bağlantılı Depolama (NAS) ve Depolama Alanı Ağları (SAN) — Hot-Swap Disk Bakımı ve Arıza Simülasyonu

- **Teorik Bilgi ve Amaç:** On dördüncü günde, sunucu depolama mimarisinin ağ üzerinden paylaşılan biçimleri olan NAS (Network Attached Storage) ve SAN (Storage Area Network) donanımları karşılaştırmalı olarak incelenmiştir. NAS'ın dosya seviyesinde (file-level, örneğin SMB/NFS protokolleriyle) erişim sunduğu, SAN'ın ise blok seviyesinde (block-level, iSCSI veya Fibre Channel üzerinden) erişim sağlayarak istemci tarafında yerel bir disk gibi görüneceği teorik farkı ele alınmıştır. Ayrıca hot-swap teknolojisinin, disk yuvalarının elektriksel olarak sistemi kapatmadan çıkarılıp takılabilmesini sağlayan donanımsal bir tasarım olduğu vurgulanmıştır. SAN mimarisinin genellikle yüksek performanslı veritabanı sunucuları gibi düşük gecikme gerektiren iş yükleri için tercih edildiği, NAS'ın ise ortak dosya paylaşımı gibi senaryolarda kurulum ve yönetim kolaylığıyla öne çıktığı karşılaştırılmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Sistem yöneticisinin onayı ve gözetiminde, NAS cihazının disk kafesi (disk tray) incelenmiş, RAID korumalı bir dizide arızalı disk senaryosu simüle etmek amacıyla çalışan bir disk fiziksel olarak yuvasından çıkarılmıştır. Sistemin kesintiye uğramadan bozulmuş (degraded) modda çalışmaya devam ettiği, yeni bir diskin takılmasıyla RAID kontrolcüsünün otomatik olarak yeniden yapılandırma (rebuild) sürecini başlattığı gözlemlenmiştir. Bu süreçte disk kafesindeki LED gösterge renklerinin (yeşil: sağlıklı, sarı/kırmızı yanıp sönen: rebuild veya arıza) donanım teknisyeni için hızlı bir görsel teşhis aracı işlevi gördüğü de not edilmiştir.

```
RAID Status: DEGRADED -> REBUILDING (disk takildi, %0 -> %100)
```

Bu simülasyon, teorik olarak anlatılan kesintisiz bakım kavramının gerçek donanım üzerinde nasıl işlediğini somut biçimde göstermiştir. Rebuild süresinin disk kapasitesiyle doğru orantılı arttığı, bu nedenle yüksek kapasiteli disklerde (özellikle RAID 5'te) rebuild penceresinin uzamasının ikinci bir arıza riskine maruz kalma süresini de artırdığı gözlemlenmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Yeniden yapılandırma (rebuild) süreci devam ederken dizinin ikinci bir disk arızasına karşı savunmasız (RAID 5'te tek parite) kaldığı fark edilmiştir. Bu risk, gelecekteki yapılandırmalarda dizide bir hot-spare disk bulundurulması ve kritik veriler için RAID 5 yerine RAID 6 veya RAID 10 tercih edilmesi önerisiyle kayıt altına alınmıştır.

---

### Gün 15: Sunucu Donanım İzleme Protokolleri — iLO/iDRAC Üzerinden Sıcaklık, Fan ve Voltaj Denetimi

- **Teorik Bilgi ve Amaç:** Üçüncü haftanın son gününde, sunucu donanımının işletim sisteminden bağımsız olarak izlenmesini sağlayan bant dışı yönetim (out-of-band management) teknolojisi incelenmiştir. HP iLO ve Dell iDRAC gibi çözümlerin, sunucu anakartına entegre ayrı bir BMC (Baseboard Management Controller) çipi üzerinden çalıştığı, bu sayede ana işletim sistemi çökmüş veya kapalı olsa dahi sunucunun sıcaklık, fan devri ve voltaj gibi donanımsal parametrelerine erişilebildiği ve hatta uzaktan güç açma/kapama yapılabildiği teorik olarak ele alınmıştır. Bu bağımsızlık, IPMI (Intelligent Platform Management Interface) standardının donanım yönetimindeki temel katkısı olarak değerlendirilmiştir. BMC çipinin kendi ayrı ağ arayüzü ve düşük güç tüketimli bekleme (standby) gücüyle çalıştığı, bu sayede sunucunun ana gücü tamamen kesilmediği sürece BMC'nin her zaman erişilebilir kaldığı da vurgulanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Sistem yöneticisinin hesabı ve refakatinde, sunucunun iDRAC web arayüzüne ayrı bir yönetim ağı (out-of-band network) üzerinden erişilmiş, anakart üzerindeki sıcaklık sensörleri, fan devir hızları ve voltaj hatları canlı olarak izlenmiştir. Fan eğrisinin (fan curve) belirli sıcaklık eşiklerine göre otomatik hız artışı yapacak şekilde yeniden yapılandırılmıştır. Ayrıca sistem günlüğünde (System Event Log) daha önce kaydedilmiş donanımsal uyarılar (ECC düzeltilebilir hata sayısı, disk yeniden deneme sayısı gibi) geriye dönük olarak incelenerek, henüz kritik seviyeye ulaşmamış ama kademeli kötüleşme eğilimindeki bileşenlerin erken tespiti hedeflenmiştir.

```
$ ipmitool sensor
Fan1 RPM | 4200 | ok
Inlet Temp | 22.0 | ok
```

Bu ince ayar, hem donanımın aşırı ısınmadan korunmasını hem de gereksiz yere maksimum fan hızında çalışılarak oluşan gürültü ve enerji israfının önlenmesini amaçlamıştır. Voltaj sensörlerinden alınan anlık değerlerin, anakart üzerindeki güç dönüştürücü (VRM) devrelerinin sağlıklı çalışıp çalışmadığının dolaylı bir göstergesi olarak da kullanılabildiği not edilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Fabrika ayarlarındaki fan eğrisinin, oda sıcaklığı normal seviyedeyken bile fanları sürekli yüksek devirde çalıştırdığı gözlemlenmiştir. Sorun, iDRAC üzerinden özel bir fan eğrisi tanımlanarak eşik değerlerin gerçek ortam sıcaklığı verileriyle yeniden kalibre edilmesiyle giderilmiş, gürültü ve güç tüketimi gözle görülür şekilde azalmıştır.

---

## HAFTA 4: AKILLI ŞEHİR SİSTEMLERİ — GÖMÜLÜ DONANIM VE IoT ENTEGRASYONU (Gün 16 – Gün 20)

---

### Gün 16: Akıllı Şehir Sensör Düğümleri İçin Mikrodenetleyici Mimarilerinin İncelenmesi — GPIO Pinleri, Çalışma Voltajları ve Saat Frekansları

- **Teorik Bilgi ve Amaç:** Son haftanın ilk gününde, belediyenin Akıllı Şehir Biriminin park ve meydanlara kurmayı planladığı çevresel izleme istasyonu pilot projesi kapsamında kullanılacak mikrodenetleyici (MCU) mimarileri incelenmiştir. Bir mikrodenetleyicinin, genel amaçlı bir mikroişlemciden farklı olarak işlemci çekirdeğini, belleği ve giriş/çıkış birimlerini tek bir yonga üzerinde barındırdığı, bu bütünleşik yapının düşük güç tüketimi ve düşük maliyetle gömülü uygulamalara uygun hale geldiği teorik olarak ele alınmıştır. GPIO (General Purpose Input/Output) pinlerinin dijital olarak yalnızca iki gerilim seviyesi (mantıksal 0 ve 1) arasında çalıştığı, bu seviyelerin platforma göre 3.3V veya 5V olabileceği vurgulanmıştır. Ayrıca saat frekansının (clock speed) yalnızca işlem hızını değil, aynı zamanda güç tüketimini de doğrudan etkilediği; bu nedenle pil ile çalışan gömülü uygulamalarda genellikle daha düşük frekanslı ve düşük güçlü mikrodenetleyicilerin tercih edildiği teorik olarak açıklanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** ESP32-WROOM-32, Raspberry Pi 4 ve Arduino Uno platformları donanımsal olarak karşılaştırılmış; ESP32'nin 3.3V mantık seviyesinde ve çift çekirdekli 240 MHz işlemcisiyle, Arduino Uno'nun ise 5V mantık seviyesinde ve 16 MHz tek çekirdekli mikrodenetleyicisiyle çalıştığı belirlenmiştir. Ayrıca ESP32'nin dahili Wi-Fi ve Bluetooth radyosuyla geldiği, bu sayede harici bir ağ modülüne gerek kalmadan toplanan verinin doğrudan belediyenin merkezi Akıllı Şehir sunucusuna iletilebileceği, Arduino Uno'nun ise bu tür kablosuz bağlantı için ek bir genişleme kartı (shield) gerektirdiği tespit edilmiş ve bu nedenle pilot proje için ESP32 platformu tercih edilmiştir.

```
ESP32: 3.3V logic, 240MHz | Arduino Uno: 5V logic, 16MHz
```

Bu karşılaştırma, bir sensörün veya modülün hangi platforma doğrudan, hangisine ise ek devre elemanıyla bağlanabileceğinin önceden planlanması gerektiğini göstermiştir. Ayrıca Raspberry Pi 4'ün tam bir işletim sistemi (Linux tabanlı) çalıştırabilen bir mikroişlemci kartı olduğu, ESP32 ve Arduino'nun ise doğrudan donanım üzerinde çalışan (bare-metal) firmware ile çalıştığı; bu farkın proje gereksinimine göre platform seçiminde belirleyici olduğu değerlendirilmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** 5V mantık seviyesinde çalışan bir Arduino çıkışının, 3.3V ile çalışan ESP32'nin girişine doğrudan bağlanmasının donanımı kalıcı olarak zarar verebileceği fark edilmiştir. Bu risk, iki farklı gerilim seviyesi arasında güvenli dönüşüm sağlayan bir lojik seviye çevirici (logic level shifter) devresinin araya eklenmesiyle ortadan kaldırılmıştır.

---

### Gün 17: Sensör Haberleşme Protokollerinin Donanımsal İncelemesi — I2C, SPI ve UART

- **Teorik Bilgi ve Amaç:** On yedinci günde, mikrodenetleyicilerin çevresel sensörlerle haberleşmesinde kullanılan üç temel donanımsal protokol incelenmiştir. I2C'nin yalnızca iki hat (SDA/SCL) üzerinden senkron ve adreslenebilir çoklu cihaz haberleşmesine izin verdiği, SPI'ın ayrı veri hatları (MOSI/MISO) ve cihaz seçim (chip-select) hattı sayesinde I2C'ye kıyasla çok daha yüksek hızda çalışabildiği, UART'ın ise saat hattı olmaksızın asenkron, noktadan noktaya seri haberleşme sağladığı teorik olarak karşılaştırılmıştır. Bu protokol seçiminin, sensörün veri hacmi ve gerçek zamanlılık gereksinimine göre yapılması gerektiği değerlendirilmiştir. I2C'nin aynı iki hat üzerinden onlarca cihazı benzersiz adresleriyle desteklemesi nedeniyle pin sayısının kısıtlı olduğu küçük gömülü sistemlerde tercih edildiği, SPI'ın ise her ek cihaz için ayrı bir chip-select hattı gerektirmesine rağmen daha yüksek veri hızı sunduğu için ekran veya SD kart gibi yüksek bant genişliği isteyen modüllerde tercih edildiği karşılaştırılmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Çevresel izleme istasyonu prototipi için seçilen bir DHT22 sıcaklık/nem sensörü tek hatlı özel bir zamanlama protokolüyle, bakım ekibinin sahadaki cihaza yetkisiz müdahalesini önlemek amacıyla eklenen bir MFRC522 RFID kart okuyucu ise SPI protokolüyle mikrodenetleyiciye bağlanmıştır. I2C hattı üzerindeki bağlı cihazlar, bir tarama komutuyla adresleri okunarak doğrulanmıştır. SPI hattına bağlanan MFRC522 modülünün SCK (saat), MOSI, MISO ve SS hatlarının her biri mikrodenetleyicinin ilgili donanımsal SPI pinlerine bire bir eşlenmiş, yazılım tarafında ise yalnızca kütüphane çağrısıyla bu donanımsal hatların soyutlandığı gözlemlenmiştir.

```
I2C scan: Found device at address 0x3C (OLED display)
```

Bu bağlantılar sırasında her protokolün kendine özgü fiziksel kablolama gereksinimlerinin (I2C için ortak hat, SPI için dört hat) doğru uygulanmasının, haberleşme hatalarını en baştan önlediği gözlemlenmiştir. UART bağlantısında ise TX ve RX hatlarının çapraz bağlanması (bir cihazın TX'i diğerinin RX'ine) gerektiği, bu basit ama sık yapılan bağlantı hatasının kontrol listesine eklenmesi gerektiği notu düşülmüştür.

- **Karşılaşılan Zorluk ve Çözüm:** I2C hattına bağlı sensörden veri okunamadığı, tarama komutunun hiçbir cihaz bulamadığı görülmüştür. Sorun, I2C protokolünün açık kollektör (open-drain) çıkış mimarisi gereği SDA ve SCL hatlarında harici pull-up dirençlerine ihtiyaç duyduğunun hatırlanmasıyla teşhis edilmiş; hatlara 4.7 kΩ'luk pull-up dirençleri eklenerek haberleşme başarıyla sağlanmıştır.

---

### Gün 18: Akıllı Şehir Çevre İzleme İstasyonunun Devre Şeması İncelemesi ve Breadboard Üzerinde Prototiplenmesi

- **Teorik Bilgi ve Amaç:** On sekizinci günde, belediyenin park ve meydanlara kurmayı planladığı IoT tabanlı çevre izleme istasyonunun donanım geliştirme sürecindeki ilk aşaması olan prototipleme ele alınmıştır. Breadboard'un, lehim gerektirmeden devre elemanlarının birbirine iç yaylı temas noktaları üzerinden geçici olarak bağlanmasına imkân tanıyan bir prototipleme aracı olduğu, bu sayede bir tasarımın kalıcı bir baskılı devre kartına (PCB) dönüştürülmeden önce ucuz ve tekrarlanabilir biçimde test edilebildiği teorik olarak açıklanmıştır. Bu aşamanın donanım geliştirme yaşam döngüsünde yazılımdaki "geliştirme ortamı" kavramına karşılık geldiği değerlendirilmiştir. Devre şemasının (schematic) fiziksel yerleşimden (layout) bağımsız olarak yalnızca elektriksel bağlantıları ve sinyal akışını temsil ettiği, bu soyutlamanın devrenin mantığını breadboard, PCB veya simülasyon ortamından bağımsız olarak anlamayı kolaylaştırdığı da vurgulanmıştır.

- **Teknik Uygulama ve Mimari Kararlar:** Pilot istasyonun sıcaklık/nem izleme devresinin şeması incelenmiş; şema üzerindeki güç hattı, mikrodenetleyici ve sensör bağlantıları breadboard üzerinde fiziksel olarak yeniden kurulmuştur. Breadboard'un güç şeridi (power rail) üzerinden 3.3V hattı tüm bileşenlere ortak olarak dağıtılmış, sensör verisi mikrodenetleyicinin analog/dijital girişine bağlanmıştır. Devrede ortak referans noktası (GND) olmadan hiçbir dijital sinyalin doğru yorumlanamayacağı ilkesi gereği, tüm bileşenlerin toprak hatlarının tek bir ortak noktada birleştirildiği ayrıca kontrol edilmiştir.

```
Breadboard: Power Rail (+3.3V) -> Sensor VCC, MCU 3V3
```

Bu aşamada devre şemasının doğru okunmasının, breadboard üzerindeki fiziksel bağlantı hatalarını en aza indiren temel beceri olduğu görülmüştür. Toplanan sensör verisinin mikrodenetleyici tarafından belirli aralıklarla okunup UART üzerinden bilgisayara aktarılması sağlanarak, cihazın uçtan uca (sensör-MCU-çıktı) veri akışının bütünlüğü doğrulanmıştır.

- **Karşılaşılan Zorluk ve Çözüm:** Kurulan devrenin zaman zaman kesintili çalıştığı, sensör verisinin aralıklarla kaybolduğu gözlemlenmiştir. İnceleme sonucunda breadboard'un güç şeridi bağlantı noktalarından birinin gevşek temas ettiği belirlenmiş; sorun, tüm bağlantı noktalarının multimetre ile tek tek süreklilik testinden geçirilip gevşek olanların yeniden sıkıca takılmasıyla giderilmiştir. Bu deneyim, breadboard prototiplerinin hızlı doğrulama için değerli olsa da mekanik temas güvenilirliğinin lehimli bir PCB'ye kıyasla düşük olduğunu ve uzun süreli testlerde bu sınırlamanın göz önünde bulundurulması gerektiğini göstermiştir.

---

### Gün 19: Donanım-Yazılım Arayüzü Doğrulaması — Bootloader Üzerinden Sinyal ve Voltaj Seviyesi Testleri

- **Teorik Bilgi ve Amaç:** On dokuzuncu günde, donanım ile yazılımın kesiştiği kritik nokta olan bootloader mekanizması donanımsal açıdan incelenmiştir. Bootloader'ın, mikrodenetleyicinin flaş belleğine yeni bir program yazılabilmesi için özel bir programlama gerilimi ve belirli reset/boot pin durumlarını gerektirdiği, bu pin durumlarının yanlış ayarlanması halinde programlamanın gerçekleşmeyeceği teorik olarak ele alınmıştır. Ayrıca UART üzerinden yapılan seri haberleşmenin, yazılımsal bir ayardan önce fiziksel katmanda doğru gerilim seviyesi ve saat hızının (baud rate) her iki uçta da eşleşmesini gerektiren donanımsal bir ön koşulu olduğu vurgulanmıştır. USB-seri (USB-to-UART) köprü çipinin, bilgisayarın USB portu ile mikrodenetleyicinin UART pinleri arasında gerilim ve protokol dönüşümünü donanımsal olarak üstlendiği, bu çipin sürücüsü kurulu olmadan işletim sisteminin cihazı tanıyamayacağı da teorik olarak eklenmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** Akıllı Şehir çevre izleme istasyonu prototipine bootloader üzerinden minimal bir test programı yüklenmiş, ardından cihazın UART hattı üzerinden gönderdiği veri, bir lojik analizör yardımıyla sinyal genliği ve zamanlaması açısından gözlemlenmiştir. Bu ölçüm, seri terminaldeki yazılımsal çıktının, fiziksel hatta gerçekten doğru gerilim seviyesinde (3.3V mantık) ve doğru baud rate'te iletildiğinin donanımsal olarak teyit edilmesini sağlamıştır. Lojik analizörün yakaladığı sinyal, bit başına düşen süreyi hesaplayarak yazılımda tanımlanan baud rate değeriyle çapraz kontrol etmiş, iki değerin örtüşmesi haberleşmenin hem yazılımsal hem donanımsal katmanda tutarlı olduğunu doğrulamıştır.

```
UART: Baud=115200 | TX Idle=3.3V | Bit süresi=8.68us
```

Bu doğrulama adımı, bir yazılım hatasıyla bir donanım/sinyal hatasının birbirinden ayırt edilebilmesi açısından kritik bir teşhis becerisi olarak değerlendirilmiştir. Reset pininin programlama öncesi kısa süreliğine düşük seviyeye (LOW) çekilerek mikrodenetleyicinin bootloader moduna zorlanması gerektiği, bu donanımsal el sıkışmanın (handshake) yazılım yükleme aracı tarafından otomatik yönetildiği de ayrıca gözlemlenmiştir.

- **Karşılaşılan Zorluk ve Çözüm:** Seri terminalde anlamsız (mojibake) karakterler görüntülendiği fark edilmiştir. Lojik analizör ile yapılan inceleme, sinyalin fiziksel olarak sağlıklı iletildiğini, ancak terminal yazılımının farklı bir baud rate'e ayarlı olduğunu göstermiştir. Sorun, gönderici ve alıcı taraftaki baud rate değerlerinin eşitlenmesiyle çözülmüş, bu deneyim iletişim hatalarının her zaman donanımsal olmayabileceğini göstermiştir.

---

### Gün 20: Donanım Envanterinin Genel Denetimi ve 20 Günlük Donanım Staj Sürecinin Değerlendirilmesi

- **Teorik Bilgi ve Amaç:** Yirminci ve son günde, belediye bünyesindeki donanım varlıklarının sistematik olarak takip edilmesini sağlayan BT varlık yönetimi (IT Asset Management) kavramı ele alınmıştır. Her donanımın seri numarası, garanti durumu ve yaşam döngüsü (lifecycle) bilgisiyle merkezi bir envanterde kayıtlı tutulmasının, hem bütçe planlamasında hem de arıza/güvenlik olaylarında hızlı müdahalede kritik rol oynadığı değerlendirilmiştir. Bu değerlendirme aynı zamanda, dört hafta boyunca iş istasyonundan sunucuya, ağ altyapısından Akıllı Şehir Biriminin gömülü sistem pilot projesine uzanan katmanlı bir donanım öğrenme sürecinin genel bir özeti niteliği taşımıştır. Envanter kayıtlarının yalnızca satın alma anında değil, cihazın kullanım ömrü boyunca (bakım, arıza, yer değişikliği) güncel tutulmasının, uzun vadeli donanım yenileme (refresh) planlamasının sağlıklı yapılabilmesi için zorunlu olduğu değerlendirilmiştir.

- **Teknik Uygulama ve Mimari Kararlar:** Sistem odasındaki ve kullanıcı iş istasyonlarındaki donanımların fiziksel sayımı yapılmış, seri numaraları merkezi envanter kaydıyla karşılaştırılmıştır. Bakım günlükleri (maintenance log) düzenlenerek hangi cihaza ne zaman hangi işlemin uygulandığı kayıt altına alınmış, ardından yirmi günlük sürecin tüm bulguları bir taslak rapor haline getirilmiştir. Envanterdeki her donanım kalemi ayrıca kritiklik seviyesine göre sınıflandırılmış (kritik sunucu/ağ donanımı, standart iş istasyonu, sarf çevre birimi), bu sınıflandırmanın ileride bakım önceliklendirmesinde referans alınması planlanmıştır.

```
Envanter: Seri No | Model | Garanti Bitis | Son Bakim Tarihi
```

Bu sürecin, bireysel iş istasyonu bakımından başlayıp sunucu ve ağ katmanlarından geçerek gömülü sistemlere uzanan bütünsel bir donanım altyapısı anlayışı kazandırdığı sonucuna varılmıştır. Taslak rapor hazırlanırken her haftanın bulguları ayrı bir bölüm olarak ele alınmış, böylece staj sürecinin başından sonuna kadar izlenebilir ve denetlenebilir bir belge ortaya konmuştur.

- **Karşılaşılan Zorluk ve Çözüm:** Fiziksel sayım sırasında, merkezi envanter kaydında yer almayan birkaç cihazın (gölge BT / shadow IT) tespit edildiği görülmüştür. Bu durum, zaman içinde kayıt dışı eklenen donanımların sistemli bir denetim mekanizması olmadan fark edilemeyeceğini göstermiş; sorun, tespit edilen cihazların envantere işlenmesi ve düzenli fiziksel sayım denetiminin bir prosedüre bağlanması önerisiyle kapatılmıştır.

---
