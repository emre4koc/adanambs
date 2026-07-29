<?php
require_once '../config/session_check_admin.php';
require_once '../config/db.php';

$sayfa_baslik = "Duyuru Yönetimi";
$mesaj = '';

// Sayfa yüklendiğinde session'daki mesajı al ve temizle
if (isset($_SESSION['mesaj'])) {
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
}

// --- İŞLEM BLOKLARI ---

// Duyuru ekleme/güncelleme
if (isset($_POST['duyuru_kaydet'])) {
    $id = $_POST['id'];
    $baslik = trim($_POST['baslik']);
    $icerik = trim($_POST['icerik']);
    $tarih = !empty($_POST['tarih']) ? $_POST['tarih'] : null;

    if (empty($id)) { // Yeni duyuru ekleme
        $stmt = $pdo->prepare("INSERT INTO duyurular (baslik, icerik, tarih) VALUES (?, ?, ?)");
        if ($stmt->execute([$baslik, $icerik, $tarih])) {
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Duyuru başarıyla eklendi ve bildirimler gönderildi.'];
            
            // Tüm kullanıcılara bildirim gönder (yöneticiler hariç)
            $users_stmt = $pdo->query("SELECT id FROM users WHERE rol != 1");
            $user_ids = $users_stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($user_ids) {
                $bildirim_mesaji = "Yeni duyuru: " . (strlen($baslik) > 50 ? substr($baslik, 0, 47) . '...' : $baslik);
                $bildirim_linki = "/anasayfa.php";
                
                $bildirim_stmt = $pdo->prepare("INSERT INTO bildirimler (user_id, mesaj, link) VALUES (?, ?, ?)");
                foreach ($user_ids as $user_id) {
                    $bildirim_stmt->execute([$user_id, $bildirim_mesaji, $bildirim_linki]);
                }
            }
        } else {
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Duyuru eklenirken bir hata oluştu.'];
        }
    } else { // Mevcut duyuru güncelleme
        $stmt = $pdo->prepare("UPDATE duyurular SET baslik = ?, icerik = ?, tarih = ? WHERE id = ?");
        if ($stmt->execute([$baslik, $icerik, $tarih, $id])) {
            // Güncellenen duyurunun okuma kayıtlarını sıfırla — herkes tekrar görsün
            $pdo->prepare("DELETE FROM duyuru_okundu WHERE duyuru_id = ?")->execute([$id]);
            $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Duyuru başarıyla güncellendi. Tüm okuma kayıtları sıfırlandı, kullanıcılar duyuruyu tekrar görecek.'];
        } else {
            $_SESSION['mesaj'] = ['tip' => 'error', 'icerik' => 'Duyuru güncellenirken bir hata oluştu.'];
        }
    }
    header("Location: duyurular.php");
    exit();
}

// Okuma detayı AJAX isteği — arşivleme bloğundan ÖNCE olmalı
if (isset($_GET['action']) && $_GET['action'] === 'okuma_detay' && isset($_GET['id'])) {
    $duyuru_id = (int)$_GET['id'];

    $okuyanlar_stmt = $pdo->prepare("
        SELECT u.ad, u.soyad,
               DATE_FORMAT(o.okunma_tarihi, '%d.%m.%Y %H:%i') AS okunma_tarihi
        FROM duyuru_okundu o
        JOIN users u ON o.user_id = u.id
        WHERE o.duyuru_id = ?
        ORDER BY o.okunma_tarihi ASC
    ");
    $okuyanlar_stmt->execute([$duyuru_id]);
    $okuyanlar_data = $okuyanlar_stmt->fetchAll(PDO::FETCH_ASSOC);

    $okumayanlar_stmt = $pdo->prepare("
        SELECT u.ad, u.soyad
        FROM users u
        WHERE u.aktif = 1 AND u.rol != 1
        AND u.id NOT IN (
            SELECT user_id FROM duyuru_okundu WHERE duyuru_id = ?
        )
        ORDER BY u.ad, u.soyad
    ");
    $okumayanlar_stmt->execute([$duyuru_id]);
    $okumayanlar_data = $okumayanlar_stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(['okuyanlar' => $okuyanlar_data, 'okumayanlar' => $okumayanlar_data]);
    exit();
}

// Arşivleme, Arşivden Çıkarma ve Silme İşlemleri
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action == 'arsivle') {
        $pdo->prepare("UPDATE duyurular SET arsiv = 1 WHERE id = ?")->execute([$id]);
        $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Duyuru başarıyla arşivlendi.'];
    } elseif ($action == 'arsivden_cikar') {
        $pdo->prepare("UPDATE duyurular SET arsiv = 0 WHERE id = ?")->execute([$id]);
        $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Duyuru arşivden çıkarıldı.'];
    } elseif ($action == 'sil') {
        $pdo->prepare("DELETE FROM duyurular WHERE id = ?")->execute([$id]);
        $_SESSION['mesaj'] = ['tip' => 'success', 'icerik' => 'Duyuru kalıcı olarak silindi.'];
    }
    header("Location: duyurular.php");
    exit();
}

// Düzenlenecek duyuruyu çek

// Tüm duyuruları çek + okuma istatistikleri
$toplam_kullanici = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE aktif = 1 AND rol != 1")->fetchColumn();

$duyurular = $pdo->query("
    SELECT d.*,
        (SELECT COUNT(*) FROM duyuru_okundu o WHERE o.duyuru_id = d.id) AS okuyan_sayisi
    FROM duyurular d
    ORDER BY d.olusturma_tarihi DESC
")->fetchAll();

include '../templates/header.php';
?>
<div class="container mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md h-fit">
            <h2 class="text-xl font-semibold mb-4 text-gray-800"><?php echo $duzenlenecek_duyuru ? 'Duyuruyu Düzenle' : 'Yeni Duyuru Ekle'; ?></h2>
            <?php if ($mesaj && (isset($_POST['duyuru_kaydet']) || isset($_SESSION['mesaj']))): ?>
                <div class="p-4 mb-4 text-sm rounded-lg <?php echo $mesaj['tip'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <?php echo htmlspecialchars($mesaj['icerik']); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="duyurular.php">
                <input type="hidden" name="id" value="<?php echo $duzenlenecek_duyuru->id ?? ''; ?>">
                <div class="mb-4">
                    <label for="baslik" class="block text-sm font-medium text-gray-700">Başlık</label>
                    <input type="text" name="baslik" id="baslik" value="<?php echo htmlspecialchars($duzenlenecek_duyuru->baslik ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required>
                </div>
                <div class="mb-4">
                    <label for="icerik" class="block text-sm font-medium text-gray-700">İçerik</label>
                    <textarea name="icerik" id="icerik" rows="5" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required><?php echo htmlspecialchars($duzenlenecek_duyuru->icerik ?? ''); ?></textarea>
                </div>
                <div class="mb-4">
                    <label for="tarih" class="block text-sm font-medium text-gray-700">İlişkili Tarih (İsteğe Bağlı)</label>
                    <input type="date" name="tarih" id="tarih" value="<?php echo htmlspecialchars($duzenlenecek_duyuru->tarih ?? ''); ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                </div>
                <button type="submit" name="duyuru_kaydet" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Kaydet</button>
                <?php if ($duzenlenecek_duyuru): ?>
                    <a href="duyurular.php" class="block text-center w-full bg-gray-200 text-gray-800 mt-2 py-2 px-4 rounded-md hover:bg-gray-300">İptal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Tüm Duyurular</h2>
             <?php if ($mesaj && !isset($_POST['duyuru_kaydet'])): ?>
                <div class="p-4 mb-4 text-sm rounded-lg <?php echo $mesaj['tip'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <?php echo htmlspecialchars($mesaj['icerik']); ?>
                </div>
            <?php endif; ?>
            <div class="overflow-y-auto max-h-[600px]">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($duyurular as $duyuru): 
                        $okuyan = (int)$duyuru->okuyan_sayisi;
                        $yuzde = $toplam_kullanici > 0 ? round(($okuyan / $toplam_kullanici) * 100) : 0;
                        $bar_renk = $yuzde >= 75 ? 'bg-green-500' : ($yuzde >= 40 ? 'bg-yellow-400' : 'bg-red-400');
                    ?>
                    <li class="py-3 <?php echo $duyuru->arsiv ? 'bg-gray-100 opacity-60' : ''; ?>">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0 pr-4">
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($duyuru->baslik); ?></p>
                                <p class="text-sm text-gray-600 truncate"><?php echo htmlspecialchars($duyuru->icerik); ?></p>
                                <p class="text-xs text-gray-400 mt-1">
                                    <?php echo date('d.m.Y', strtotime($duyuru->olusturma_tarihi)); ?>
                                    <?php if ($duyuru->tarih): ?> · İlgili Tarih: <?php echo date('d.m.Y', strtotime($duyuru->tarih)); ?><?php endif; ?>
                                </p>

                                <!-- Okuma istatistiği -->
                                <div class="mt-2">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-500">
                                            <span class="font-semibold text-gray-700"><?php echo $okuyan; ?></span> / <?php echo $toplam_kullanici; ?> kişi okudu
                                        </span>
                                        <button 
                                            class="text-xs text-blue-600 hover:underline okuma-detay-btn"
                                            data-id="<?php echo $duyuru->id; ?>"
                                            data-baslik="<?php echo htmlspecialchars($duyuru->baslik); ?>">
                                            Detay →
                                        </button>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="<?php echo $bar_renk; ?> h-1.5 rounded-full transition-all" style="width: <?php echo $yuzde; ?>%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-shrink-0 flex items-center space-x-3">
                                <a href="?edit_id=<?php echo $duyuru->id; ?>" class="text-blue-500 hover:text-blue-700" title="Düzenle"><i class="fas fa-edit"></i></a>
                                <?php if ($duyuru->arsiv): ?>
                                    <a href="?action=arsivden_cikar&id=<?php echo $duyuru->id; ?>" class="text-green-500 hover:text-green-700" title="Arşivden Çıkar"><i class="fas fa-box-open"></i></a>
                                <?php else: ?>
                                    <a href="?action=arsivle&id=<?php echo $duyuru->id; ?>" class="text-yellow-500 hover:text-yellow-700" title="Arşivle"><i class="fas fa-archive"></i></a>
                                <?php endif; ?>
                                <a href="?action=sil&id=<?php echo $duyuru->id; ?>" class="text-red-500 hover:text-red-700" title="Sil" onclick="return confirm('Bu duyuruyu kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include '../templates/footer.php'; ?>

<!-- Okuma Detay Modalı -->
<div id="okuma-detay-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 relative max-h-[80vh] flex flex-col">
        <button id="okuma-modal-close" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        <h3 id="okuma-modal-baslik" class="text-lg font-bold text-gray-800 mb-4 pr-6"></h3>
        <div class="grid grid-cols-2 gap-4 overflow-y-auto flex-1">
            <!-- Okuyanlar -->
            <div>
                <h4 class="text-sm font-semibold text-green-700 mb-2 flex items-center">
                    <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                    Okuyanlar (<span id="okuyan-sayi">0</span>)
                </h4>
                <ul id="okuyanlar-listesi" class="space-y-1 text-sm text-gray-700"></ul>
            </div>
            <!-- Okumayanlar -->
            <div>
                <h4 class="text-sm font-semibold text-red-600 mb-2 flex items-center">
                    <span class="inline-block w-2 h-2 bg-red-400 rounded-full mr-2"></span>
                    Okumadı (<span id="okumayan-sayi">0</span>)
                </h4>
                <ul id="okumayanlar-listesi" class="space-y-1 text-sm text-gray-700"></ul>
            </div>
        </div>
        <div id="okuma-yukleniyor" class="text-center py-8 text-gray-500 text-sm">Yükleniyor...</div>
    </div>
</div>

<script>
(function() {
    var modal      = document.getElementById('okuma-detay-modal');
    var closeBtn   = document.getElementById('okuma-modal-close');
    var baslikEl   = document.getElementById('okuma-modal-baslik');
    var yukEl      = document.getElementById('okuma-yukleniyor');
    var okuyanEl   = document.getElementById('okuyanlar-listesi');
    var okumayEl   = document.getElementById('okumayanlar-listesi');
    var oSayiEl    = document.getElementById('okuyan-sayi');
    var omSayiEl   = document.getElementById('okumayan-sayi');

    closeBtn.addEventListener('click', function() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });

    document.querySelectorAll('.okuma-detay-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var baslik = this.dataset.baslik;

            baslikEl.textContent = baslik;
            yukEl.classList.remove('hidden');
            okuyanEl.innerHTML = '';
            okumayEl.innerHTML = '';
            oSayiEl.textContent = '0';
            omSayiEl.textContent = '0';
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            fetch('?action=okuma_detay&id=' + id)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    yukEl.classList.add('hidden');

                    oSayiEl.textContent = data.okuyanlar.length;
                    omSayiEl.textContent = data.okumayanlar.length;

                    data.okuyanlar.forEach(function(k) {
                        var li = document.createElement('li');
                        li.className = 'flex flex-col';
                        li.innerHTML = '<span class="font-medium">' + k.ad + ' ' + k.soyad + '</span>'
                            + '<span class="text-xs text-gray-400">' + k.okunma_tarihi + '</span>';
                        okuyanEl.appendChild(li);
                    });

                    if (data.okuyanlar.length === 0) {
                        okuyanEl.innerHTML = '<li class="text-gray-400 text-xs">Henüz kimse okumadı.</li>';
                    }

                    data.okumayanlar.forEach(function(k) {
                        var li = document.createElement('li');
                        li.textContent = k.ad + ' ' + k.soyad;
                        okumayEl.appendChild(li);
                    });

                    if (data.okumayanlar.length === 0) {
                        okumayEl.innerHTML = '<li class="text-gray-400 text-xs">Herkes okudu 🎉</li>';
                    }
                })
                .catch(function() {
                    yukEl.textContent = 'Veri yüklenirken hata oluştu.';
                });
        });
    });
})();
</script>