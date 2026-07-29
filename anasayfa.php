<?php
require_once 'config/session_check.php';
require_once 'config/db.php';

// PHPMailer (SMTP) - eski mail() fonksiyonu yerine kullanılacak
require_once __DIR__ . '/lib/PHPMailer/Exception.php';
require_once __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// --- SMTP AYARLARI (BURAYI KENDİ KURUMSAL MAİL BİLGİLERİNLE DOLDUR) ---
$smtp_host     = ""; // mail.adanambs.com
$smtp_port     = ""; // 465
$smtp_email    = ""; // ihk@adanambs.com
$smtp_sifre    = ""; // d1BV%mI!7zYtfDxr
$smtp_from_ad  = "Adana İ.H.K."; // Gönderen adı (isteğe bağlı, değiştirebilirsin)

date_default_timezone_set('Europe/Istanbul');

$sayfa_baslik = "Anasayfa";

// Duyuruları çek
$duyurular = $pdo->query("SELECT * FROM duyurular WHERE arsiv = 0 ORDER BY olusturma_tarihi DESC LIMIT 10")->fetchAll();

// duyuru_okundu tablosunu yoksa oluştur
$pdo->exec("
    CREATE TABLE IF NOT EXISTS duyuru_okundu (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        duyuru_id INT NOT NULL,
        okunma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_okuma (user_id, duyuru_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (duyuru_id) REFERENCES duyurular(id) ON DELETE CASCADE
    )
");

// AJAX: Okudum isteği gelirse kaydet ve çık
if (isset($_POST['action']) && $_POST['action'] === 'duyuru_oku' && isset($_POST['duyuru_id'])) {
    $duyuru_id = (int)$_POST['duyuru_id'];
    $user_id_ajax = $_SESSION['user_id'];
    try {
        $pdo->prepare("INSERT IGNORE INTO duyuru_okundu (user_id, duyuru_id) VALUES (?, ?)")
            ->execute([$user_id_ajax, $duyuru_id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false]);
    }
    exit();
}

// Kullanıcının henüz okumadığı en yeni duyuruyu bul
$okunmamis_stmt = $pdo->prepare("
    SELECT d.* FROM duyurular d
    WHERE d.arsiv = 0
    AND d.id NOT IN (
        SELECT duyuru_id FROM duyuru_okundu WHERE user_id = ?
    )
    ORDER BY d.olusturma_tarihi DESC
    LIMIT 1
");
$okunmamis_stmt->execute([$_SESSION['user_id']]);
$otomatik_duyuru = $okunmamis_stmt->fetch();

// Haftanın başlangıç ve bitişini hesapla
if (date('w') == 5) { $hafta_baslangic = date('Y-m-d'); } 
else { $hafta_baslangic = date('Y-m-d', strtotime('last Friday')); }
$hafta_bitis = date('Y-m-d', strtotime($hafta_baslangic . ' +7 days'));

// Haftalık Müsabaka Tebligatı için SADECE AKTİF müsabakaları çek
$stmt = $pdo->prepare("
    SELECT m.*, l.ad as lig_adi, s.ad as stadyum_adi, 
           t1.ad as ev_sahibi, t2.ad as misafir,
           CONCAT(h.ad, ' ', h.soyad) as hakem,
           CONCAT(y1.ad, ' ', y1.soyad) as yardimci_1,
           CONCAT(y2.ad, ' ', y2.soyad) as yardimci_2,
           CONCAT(d4.ad, ' ', d4.soyad) as dorduncu_hakem,
           CONCAT(g.ad, ' ', g.soyad) as gozlemci
    FROM musabakalar m
    LEFT JOIN ligler l ON m.lig_id = l.id
    LEFT JOIN stadyumlar s ON m.stadyum_id = s.id
    LEFT JOIN takimlar t1 ON m.ev_sahibi_id = t1.id
    LEFT JOIN takimlar t2 ON m.misafir_id = t2.id
    LEFT JOIN users h ON m.hakem_id = h.id
    LEFT JOIN users y1 ON m.yardimci_1_id = y1.id
    LEFT JOIN users y2 ON m.yardimci_2_id = y2.id
    LEFT JOIN users d4 ON m.dorduncu_hakem_id = d4.id
    LEFT JOIN users g ON m.gozlemci_id = g.id
    WHERE m.arsiv = 0
    ORDER BY CAST(m.mac_no AS UNSIGNED) ASC
");
$stmt->execute();
$haftalik_musabakalar = $stmt->fetchAll();

// Kullanıcı istatistikleri (Sadece arşivlenmemiş maçlar)
$user_id = $_SESSION['user_id'];
$aktif_gorev_sayisi_stmt = $pdo->prepare("SELECT COUNT(*) FROM musabakalar WHERE (hakem_id = :user_id OR yardimci_1_id = :user_id OR yardimci_2_id = :user_id OR dorduncu_hakem_id = :user_id OR gozlemci_id = :user_id) AND arsiv = 0");
$aktif_gorev_sayisi_stmt->execute(['user_id' => $user_id]);
$aktif_gorev_sayisi = $aktif_gorev_sayisi_stmt->fetchColumn();

$toplam_gorev_sayisi_stmt = $pdo->prepare("SELECT COUNT(*) FROM musabakalar WHERE (hakem_id = :user_id OR yardimci_1_id = :user_id OR yardimci_2_id = :user_id OR dorduncu_hakem_id = :user_id OR gozlemci_id = :user_id)");
$toplam_gorev_sayisi_stmt->execute(['user_id' => $user_id]);
$toplam_gorev_sayisi = $toplam_gorev_sayisi_stmt->fetchColumn();

// Bugün doğum günü olanlar (users tablosunda dogum_tarihi alanı olmalı)
$bugun = date('m-d');
$dogum_gunu_olanlar = $pdo->query("
    SELECT id, ad, soyad, email, 
           DATE_FORMAT(dogum_tarihi, '%d.%m.%Y') as dogum_tarihi_format,
           YEAR(CURDATE()) - YEAR(dogum_tarihi) as yas
    FROM users 
    WHERE DATE_FORMAT(dogum_tarihi, '%m-%d') = '$bugun'
    AND aktif = 1
    ORDER BY ad, soyad
")->fetchAll();

// Kullanıcının kendi doğum günü mü?
$benim_dogum_gunum = false;
foreach($dogum_gunu_olanlar as $kisi) {
    if ($kisi->id == $user_id) {
        $benim_dogum_gunum = true;
        break;
    }
}

// Doğum günü mail gönderimi - Günde bir kez
if (!empty($dogum_gunu_olanlar)) {
    foreach($dogum_gunu_olanlar as $kisi) {
        // Mail gönderildi mi kontrol et (bugün için)
        $mail_kontrol = $pdo->prepare("
            SELECT id FROM dogum_gunu_mailleri 
            WHERE user_id = ? AND mail_tarihi = CURDATE()
        ");
        $mail_kontrol->execute([$kisi->id]);
        
        if (!$mail_kontrol->fetch()) {
            // Mail gönder
            $konu = "Doğum Gününüz Kutlu Olsun!";
            $mesaj = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                        <h2 style='color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px;'>
                            Doğum Gününüz Kutlu Olsun!
                        </h2>
                        <p>Sayın <strong>" . htmlspecialchars($kisi->ad . ' ' . $kisi->soyad) . "</strong>,</p>
                        <p>Doğum gününüzü en içten dileklerimizle kutlar, sağlıklı, mutlu ve başarılı nice yıllar dileriz.</p>
                        <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;'>
                            Saygılarımızla,<br>
                            <strong>Hakem Yönetim Sistemi</strong>
                        </p>
                    </div>
                </body>
                </html>
            ";
            
            // PHPMailer ile SMTP üzerinden gönderim (eski mail() fonksiyonunun yerine)
            $mail_gonderildi = false;
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $smtp_host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtp_email;
                $mail->Password   = $smtp_sifre;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 465 portu için SSL/SMTPS
                $mail->Port       = $smtp_port;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($smtp_email, $smtp_from_ad);
                $mail->addAddress($kisi->email);

                $mail->isHTML(true);
                $mail->Subject = $konu;
                $mail->Body    = $mesaj;

                $mail->send();
                $mail_gonderildi = true;
            } catch (PHPMailerException $e) {
                // Gönderim başarısız oldu, sessizce devam et (site 500 hatası vermesin)
                $mail_gonderildi = false;
            }

            if ($mail_gonderildi) {
                // Mail gönderildi kaydını tut
                try {
                    $pdo->prepare("
                        INSERT INTO dogum_gunu_mailleri (user_id, mail_tarihi) 
                        VALUES (?, CURDATE())
                    ")->execute([$kisi->id]);
                } catch (Exception $e) {
                    // Tablo yoksa oluştur ve tekrar dene
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS dogum_gunu_mailleri (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id INT NOT NULL,
                            mail_tarihi DATE NOT NULL,
                            UNIQUE KEY unique_mail (user_id, mail_tarihi),
                            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                        )
                    ");
                    $pdo->prepare("
                        INSERT INTO dogum_gunu_mailleri (user_id, mail_tarihi) 
                        VALUES (?, CURDATE())
                    ")->execute([$kisi->id]);
                }
            }
        }
    }
}

// Türkçe ay isimleri için dizi
$turkce_aylar = ["", "Oca", "Şub", "Mar", "Nis", "May", "Haz", "Tem", "Ağu", "Eyl", "Eki", "Kas", "Ara"];
$baslangic_gun = date('d', strtotime($hafta_baslangic));
$baslangic_ay = $turkce_aylar[date('n', strtotime($hafta_baslangic))];
$bitis_gun = date('d', strtotime($hafta_bitis));
$bitis_ay = $turkce_aylar[date('n', strtotime($hafta_bitis))];
$haftalik_tarih_araligi = "$baslangic_gun $baslangic_ay - $bitis_gun $bitis_ay";

include 'templates/header.php';
?>
<div class="container mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Aktif Görev Sayısı -->
        <a href="<?php echo BASE_URL; ?>/gorevlerim.php?filtre=aktif" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-transform duration-200 ease-in-out">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-gray-500 text-sm font-medium">Aktif Görevler</h3>
                    <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo $aktif_gorev_sayisi; ?></p>
                </div>
                <div class="text-blue-600 text-3xl">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </a>

        <!-- Toplam Görevler -->
        <a href="<?php echo BASE_URL; ?>/gorevlerim.php" class="block bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-transform duration-200 ease-in-out">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-gray-500 text-sm font-medium">Sezonluk Toplam</h3>
                    <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $toplam_gorev_sayisi; ?></p>
                </div>
                <div class="text-green-600 text-3xl">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
        </a>

        <!-- Duyuru Panosu -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-48">
            <h3 class="text-gray-500 text-sm font-medium flex-shrink-0 flex items-center">
                <i class="fas fa-bullhorn mr-2 text-blue-600"></i>Duyuru Panosu
            </h3>
            <div class="flex-grow overflow-y-auto mt-2 pr-2">
                <ul class="space-y-2 text-sm">
                    <?php if (empty($duyurular)): ?>
                        <li class="text-gray-500">Gösterilecek duyuru yok.</li>
                    <?php else: ?>
                        <?php foreach($duyurular as $duyuru): ?>
                        <li class="p-2 bg-gray-50 rounded hover:bg-gray-100 cursor-pointer duyuru-item"
                            data-baslik="<?php echo htmlspecialchars($duyuru->baslik); ?>"
                            data-icerik="<?php echo nl2br(htmlspecialchars($duyuru->icerik)); ?>"
                            data-tarih="<?php echo date('d.m.Y', strtotime($duyuru->olusturma_tarihi)); ?>"
                            data-ilgili-tarih="<?php echo !empty($duyuru->tarih) ? date('d.m.Y', strtotime($duyuru->tarih)) : ''; ?>">
                            <p class="font-semibold text-gray-800 pointer-events-none truncate"><?php echo htmlspecialchars($duyuru->baslik); ?></p>
                            <p class="text-gray-600 truncate pointer-events-none text-xs"><?php echo htmlspecialchars($duyuru->icerik); ?></p>
                            <?php if (!empty($duyuru->tarih)): ?>
                                <p class="text-xs text-red-600 mt-1 pointer-events-none">İlgili Tarih: <?php echo date('d.m.Y', strtotime($duyuru->tarih)); ?></p>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Doğum Günü Kutlaması -->
        <?php if (!empty($dogum_gunu_olanlar)): ?>
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-48 border-l-4 border-blue-600">
            <h3 class="text-gray-700 text-sm font-semibold flex items-center mb-3">
                <i class="fas fa-birthday-cake mr-2 text-blue-600"></i>Doğum Günü
            </h3>
            <div class="flex-grow overflow-y-auto">
                <?php if ($benim_dogum_gunum): ?>
                    <div class="mb-3 pb-3 border-b border-gray-200">
                        <p class="text-gray-800 font-medium mb-1">Doğum gününüz kutlu olsun!</p>
                        <p class="text-xs text-gray-500">Sağlıklı, mutlu nice yıllar dileriz.</p>
                    </div>
                <?php endif; ?>
                
                <?php 
                $diger_kisiler = array_filter($dogum_gunu_olanlar, function($k) use ($user_id) {
                    return $k->id != $user_id;
                });
                ?>
                
                <?php if (!empty($diger_kisiler)): ?>
                    <p class="text-gray-600 text-xs mb-2">Bugün doğum günü:</p>
                    <ul class="space-y-1">
                        <?php foreach($diger_kisiler as $kisi): ?>
                        <li class="text-sm text-gray-700 flex items-center">
                            <i class="fas fa-circle text-blue-600 text-xs mr-2"></i>
                            <?php echo htmlspecialchars($kisi->ad . ' ' . $kisi->soyad); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <!-- Doğum günü yoksa boş alan -->
        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col h-48 items-center justify-center border border-gray-200">
            <i class="fas fa-calendar-day text-gray-300 text-3xl mb-2"></i>
            <p class="text-gray-400 text-sm">Bugün doğum günü yok</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Haftalık Müsabaka Tebligatı (<?php echo $haftalik_tarih_araligi; ?>)</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">Tarih - Saat</th>
                        <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">Lig</th>
                        <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">Müsabaka</th>
                        <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">Stadyum</th>
                        <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">Hakem</th>
                        <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">1. Y. Hakem</th>
                        <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">2. Y. Hakem</th>
                        <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">4. Hakem</th>
                        <th class="py-2 px-4 text-left text-sm font-semibold text-gray-600">Gözlemci</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php if (empty($haftalik_musabakalar)): ?>
                        <tr><td colspan="9" class="text-center py-4">Bu hafta için gösterilecek müsabaka bulunmamaktadır.</td></tr>
                    <?php else: ?>
                        <?php foreach($haftalik_musabakalar as $musabaka): ?>
                        <tr class="border-b">
                            <td class="py-2 px-4"><?php echo date('d.m.Y', strtotime($musabaka->tarih)); ?> - <?php echo date('H:i', strtotime($musabaka->saat)); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($musabaka->lig_adi); ?></td>
                            <td class="py-2 px-4 font-medium"><?php echo htmlspecialchars($musabaka->ev_sahibi); ?> - <?php echo htmlspecialchars($musabaka->misafir); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($musabaka->stadyum_adi); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($musabaka->hakem ?? '-'); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($musabaka->yardimci_1 ?? '-'); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($musabaka->yardimci_2 ?? '-'); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($musabaka->dorduncu_hakem ?? '-'); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($musabaka->gozlemci ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Duyuru Modal -->
<div id="duyuru-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
        <button id="duyuru-modal-close" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        <!-- Otomatik açılış rozeti -->
        <div id="duyuru-yeni-rozet" class="hidden mb-3">
            <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-1 rounded-full">
                🔔 Yeni Duyuru
            </span>
        </div>
        <h3 id="duyuru-modal-baslik" class="text-xl font-bold text-gray-800 mb-2"></h3>
        <p id="duyuru-modal-tarih" class="text-xs text-gray-500 mb-4"></p>
        <div id="duyuru-modal-icerik" class="text-gray-700 max-h-64 overflow-y-auto mb-5"></div>
        <!-- Okudum butonu: sadece otomatik pop-up'ta görünür -->
        <div id="duyuru-okudum-alani" class="hidden border-t pt-4 flex items-center justify-between">
            <p class="text-xs text-gray-500">Bu duyuruyu bir daha görmeyeceksiniz.</p>
            <button id="duyuru-okudum-btn" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-5 rounded-lg transition-colors">
                ✓ Okudum
            </button>
        </div>
    </div>
</div>

<?php if ($otomatik_duyuru): ?>
<script>
    var otomatikDuyuru = {
        id: <?php echo (int)$otomatik_duyuru->id; ?>,
        baslik: <?php echo json_encode(htmlspecialchars($otomatik_duyuru->baslik)); ?>,
        icerik: <?php echo json_encode(nl2br(htmlspecialchars($otomatik_duyuru->icerik))); ?>,
        tarih: <?php echo json_encode(date('d.m.Y', strtotime($otomatik_duyuru->olusturma_tarihi))); ?>,
        ilgiliTarih: <?php echo json_encode(!empty($otomatik_duyuru->tarih) ? date('d.m.Y', strtotime($otomatik_duyuru->tarih)) : ''); ?>
    };
</script>
<?php else: ?>
<script>var otomatikDuyuru = null;</script>
<?php endif; ?>

<script>
(function() {
    var modal       = document.getElementById('duyuru-modal');
    var closeBtn    = document.getElementById('duyuru-modal-close');
    var baslikEl    = document.getElementById('duyuru-modal-baslik');
    var tarihEl     = document.getElementById('duyuru-modal-tarih');
    var icerikEl    = document.getElementById('duyuru-modal-icerik');
    var rozet       = document.getElementById('duyuru-yeni-rozet');
    var okudumAlani = document.getElementById('duyuru-okudum-alani');
    var okudumBtn   = document.getElementById('duyuru-okudum-btn');

    // Modalı aç
    function modalAc(baslik, icerik, tarih, ilgiliTarih, otomatik) {
        baslikEl.textContent = baslik;
        var tarihMetni = 'Yayın Tarihi: ' + tarih;
        if (ilgiliTarih) tarihMetni += ' | İlgili Tarih: ' + ilgiliTarih;
        tarihEl.textContent = tarihMetni;
        icerikEl.innerHTML = icerik;

        if (otomatik) {
            rozet.classList.remove('hidden');
            okudumAlani.classList.remove('hidden');
        } else {
            rozet.classList.add('hidden');
            okudumAlani.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // Modalı kapat
    function modalKapat() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Kapatma butonu
    closeBtn.addEventListener('click', modalKapat);

    // Arka plana tıklayınca kapat
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modalKapat();
    });

    // Pano duyurularına tıklayınca aç (mevcut davranış)
    document.querySelectorAll('.duyuru-item').forEach(function(item) {
        item.addEventListener('click', function() {
            modalAc(
                this.dataset.baslik,
                this.dataset.icerik,
                this.dataset.tarih,
                this.dataset.ilgiliTarih || '',
                false // elle açılan → okudum butonu yok
            );
        });
    });

    // Okudum butonu
    if (okudumBtn) {
        okudumBtn.addEventListener('click', function() {
            okudumBtn.disabled = true;
            okudumBtn.textContent = 'Kaydediliyor...';

            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=duyuru_oku&duyuru_id=' + otomatikDuyuru.id
            })
            .then(function(r) { return r.json(); })
            .then(function() { modalKapat(); })
            .catch(function() { modalKapat(); });
        });
    }

    // Sayfa yüklenince okunmamış duyuru varsa otomatik aç
    if (otomatikDuyuru) {
        modalAc(
            otomatikDuyuru.baslik,
            otomatikDuyuru.icerik,
            otomatikDuyuru.tarih,
            otomatikDuyuru.ilgiliTarih,
            true // otomatik açılış → okudum butonu göster
        );
    }
})();
</script>

<?php include 'templates/footer.php'; ?>