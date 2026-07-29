<?php
require_once 'config/session_check.php';
require_once 'config/db.php';

date_default_timezone_set('Europe/Istanbul');

// --- KRİTİK GÜNCELLEME 1: SESSION KİLİDİNİ BIRAKMA ---
// Yetki kontrolü için gerekli verileri alıp hemen session'ı kapatıyoruz.
// Bu, 3-5 kişi aynı anda işlem yaptığında sunucunun kilitlenmesini (503 hatasını) önler.
$user_rol = $_SESSION['user_rol'];
$user_id = $_SESSION['user_id'];
session_write_close(); 
// -----------------------------------------------------

// YETKİ KONTROLÜ
if ($user_rol != 3) { 
    header("Location: index.php"); 
    exit(); 
}

$musabaka_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$view = isset($_GET['view']) ? $_GET['view'] : 'live';

// --- VERİ TABANI İŞLEMLERİ ---
if ($musabaka_id > 0) {
    $auth = $pdo->prepare("SELECT id FROM musabakalar WHERE id = ? AND gozlemci_id = ?");
    $auth->execute([$musabaka_id, $user_id]);
    if (!$auth->fetch()) { die("Yetkisiz Erişim."); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // KRONOMETRE AKSİYONU
        if (isset($_POST['krono_aksiyon'])) {
            $yari = $_POST['yari'];
            $is_40 = isset($_POST['is_40']) ? 1 : 0;
            $kolon = ($yari == 1) ? 'ilk_yari_baslangic' : 'ikinci_yari_baslangic';
            $pdo->prepare("INSERT INTO musabaka_kronometre (musabaka_id, $kolon, is_40_dakika) VALUES (?, NOW(), ?) 
                           ON DUPLICATE KEY UPDATE $kolon = NOW(), is_40_dakika = ?")->execute([$musabaka_id, $is_40, $is_40]);
            exit('OK');
        }

        // VERİLERİ SIFIRLA
        if (isset($_POST['reset_match'])) {
            $pdo->prepare("UPDATE musabakalar SET ev_skor = 0, misafir_skor = 0 WHERE id = ?")->execute([$musabaka_id]);
            $pdo->prepare("DELETE FROM musabaka_kronometre WHERE musabaka_id = ?")->execute([$musabaka_id]);
            $pdo->prepare("DELETE FROM musabaka_notlar WHERE musabaka_id = ?")->execute([$musabaka_id]);
            header("Location: gozlemci_canli.php?id=$musabaka_id"); exit();
        }

        // OLAY KAYDET
        if (isset($_POST['olay_kaydet'])) {
            $dk = $_POST['mac_dakikasi'];
            $detay = $_POST['olay_detay'];
            $kisi = $_POST['ilgili_kisi'] ?? 'Hakem';
            if (!empty($_POST['is_gol'])) {
                $col = ($_POST['is_gol'] === 'EV') ? 'ev_skor' : 'misafir_skor';
                $pdo->prepare("UPDATE musabakalar SET $col = $col + 1 WHERE id = ?")->execute([$musabaka_id]);
            }
            $pdo->prepare("INSERT INTO musabaka_notlar (musabaka_id, dakika, not_icerigi) VALUES (?, ?, ?)")->execute([$musabaka_id, $dk, "[$kisi] $detay"]);
        }
        
        if (isset($_POST['not_sil'])) {
            $pdo->prepare("DELETE FROM musabaka_notlar WHERE id = ?")->execute([$_POST['not_id']]);
        }
        header("Location: gozlemci_canli.php?id=$musabaka_id"); exit();
    }
}

// --- RAPOR GÖRÜNÜMÜ ---
if ($view === 'report'):
    $pdo->prepare("UPDATE musabaka_kronometre SET mac_bitti = 1 WHERE musabaka_id = ?")->execute([$musabaka_id]);
    $stmt = $pdo->prepare("SELECT m.*, l.ad as lig_adi, s.ad as stadyum_adi, t1.ad as ev_sahibi, t2.ad as misafir,
                           h.ad as h_ad, h.soyad as h_soyad, h.lisans_no as h_lisans, h.klasman as h_klasman,
                           y1.ad as y1_ad, y1.soyad as y1_soyad, y1.lisans_no as y1_lisans, y1.klasman as y1_klasman,
                           y2.ad as y2_ad, y2.soyad as y2_soyad, y2.lisans_no as y2_lisans, y2.klasman as y2_klasman,
                           d4.ad as d4_ad, d4.soyad as d4_soyad, d4.lisans_no as d4_lisans, d4.klasman as d4_klasman
                           FROM musabakalar m 
                           JOIN takimlar t1 ON m.ev_sahibi_id = t1.id 
                           JOIN takimlar t2 ON m.misafir_id = t2.id 
                           LEFT JOIN ligler l ON m.lig_id = l.id 
                           LEFT JOIN stadyumlar s ON m.stadyum_id = s.id
                           LEFT JOIN users h ON m.hakem_id = h.id
                           LEFT JOIN users y1 ON m.yardimci_1_id = y1.id
                           LEFT JOIN users y2 ON m.yardimci_2_id = y2.id
                           LEFT JOIN users d4 ON m.dorduncu_hakem_id = d4.id
                           WHERE m.id = ?");
    $stmt->execute([$musabaka_id]); $m = $stmt->fetch(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="tr"><head><meta charset="UTF-8"><title>Geçici Rapor</title>
<style>
    @page { size: A4; margin: 5mm; } body { font-family: Arial; font-size: 11px; margin: 0; padding: 10px; }
    .wrap { width: 190mm; margin: 0 auto; border: 1px solid #000; padding: 10px; }
    .title { text-align: center; font-size: 16px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px; }
    .sec { background: #eee; border: 1px solid #000; padding: 4px; font-weight: bold; text-align: center; margin: 10px 0 5px; }
    table { width: 100%; border-collapse: collapse; } td, th { border: 1px solid #000; padding: 4px; }
    .note-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; margin-top: 5px; }
    .n-box { border: 1px solid #000; min-height: 80px; padding: 5px; }
    .n-title { font-weight: bold; border-bottom: 1px solid #000; font-size: 9px; margin-bottom: 3px; }
    @media print { .no-print { display: none; } }
</style>
</head>
<body>
    <div class="no-print" style="margin-bottom:10px;"><button onclick="window.print()">Yazdır / PDF</button> <button onclick="window.location.href='?id=<?=$musabaka_id?>'">Geri Dön</button></div>
    <div class="wrap">
        <div class="title">GEÇİCİ MÜSABAKA BİLGİ FORMU</div>
        <div class="sec">MÜSABAKA BİLGİLERİ</div>
        <table>
            <tr><td width="15%"><b>Maç No:</b></td><td width="35%"><?=$m->mac_no?></td><td width="15%"><b>Lig / Hafta:</b></td><td><?=$m->lig_adi?> / <?=$m->hafta_no?>. Hafta</td></tr>
            <tr><td><b>Müsabaka:</b></td><td><?=$m->ev_sahibi?> - <?=$m->misafir?></td><td><b>Skor:</b></td><td><b><?=$m->ev_skor?> - <?=$m->misafir_skor?></b></td></tr>
            <tr><td><b>Tarih / Saat:</b></td><td><?=$m->tarih?> / <?=$m->saat?></td><td><b>Stadyum:</b></td><td><?=$m->stadyum_adi?></td></tr>
        </table>
        <div class="sec">HAKEM BİLGİLERİ</div>
        <table>
            <tr><th>Görevi</th><th>Adı Soyadı</th><th>Lisans No</th><th>Klasman</th></tr>
            <tr><td><b>Hakem</b></td><td><?=$m->h_ad.' '.$m->h_soyad?></td><td><?=$m->h_lisans?></td><td><?=$m->h_klasman?></td></tr>
            <tr><td><b>Yardımcı 1</b></td><td><?=$m->y1_ad.' '.$m->y1_soyad?></td><td><?=$m->y1_lisans?></td><td><?=$m->y1_klasman?></td></tr>
            <tr><td><b>Yardımcı 2</b></td><td><?=$m->y2_ad.' '.$m->y2_soyad?></td><td><?=$m->y2_lisans?></td><td><?=$m->y2_klasman?></td></tr>
            <tr><td><b>4. Hakem</b></td><td><?=$m->d4_ad.' '.$m->d4_soyad?></td><td><?=$m->d4_lisans?></td><td><?=$m->d4_klasman?></td></tr>
        </table>
        <div class="sec">MÜSABAKA NOTLARI</div>
        <div class="note-grid">
            <?php foreach(['Hakem','Y.Hakem 1','Y.Hakem 2','4.Hakem','Genel'] as $s): ?>
            <div class="n-box" <?=($s=='Genel')?'style="grid-column: span 2"':''?>>
                <div class="n-title"><?=strtoupper($s)?> NOTLARI</div>
                <?php $ns=$pdo->prepare("SELECT * FROM musabaka_notlar WHERE musabaka_id=? AND not_icerigi LIKE ? ORDER BY dakika ASC");
                $ns->execute([$musabaka_id, "[$s]%"]);
                while($n=$ns->fetch(PDO::FETCH_OBJ)){ echo "<div><b>{$n->dakika}'</b> ".str_replace("[$s] ","",$n->not_icerigi)."</div>"; } ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body></html>
<?php exit; endif; ?>

<?php include 'templates/header.php'; ?>
<div class="content-wrapper">
    <div class="content">
        <div class="container-fluid mx-auto px-2 py-4 max-w-5xl">
            <?php if ($musabaka_id === 0): ?>
                <div class="grid gap-4 md:grid-cols-2">
                    <?php $st=$pdo->prepare("SELECT m.*, t1.ad as ev, t2.ad as mis, l.ad as lig FROM musabakalar m JOIN takimlar t1 ON m.ev_sahibi_id=t1.id JOIN takimlar t2 ON m.misafir_id=t2.id LEFT JOIN ligler l ON m.lig_id=l.id WHERE m.gozlemci_id=? AND m.arsiv=0 ORDER BY m.tarih ASC");
                    $st->execute([$user_id]);
                    while($r=$st->fetch(PDO::FETCH_OBJ)): ?>
                        <a href="?id=<?=$r->id?>" class="bg-white p-5 rounded-2xl border hover:border-blue-400 shadow-sm">
                            <div class="flex justify-between mb-1"><span class="text-[10px] font-bold text-blue-600 uppercase"><?=$r->lig?></span></div>
                            <div class="text-lg font-black text-slate-700"><?=$r->ev?> - <?=$r->mis?></div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: 
                $st=$pdo->prepare("SELECT m.*, t1.ad as ev, t2.ad as mis, l.ad as lig, s.ad as saha,
                                   CONCAT(h.ad,' ',h.soyad) as h_ad, CONCAT(y1.ad,' ',y1.soyad) as y1_ad, 
                                   CONCAT(y2.ad,' ',y2.soyad) as y2_ad, CONCAT(d4.ad,' ',d4.soyad) as d4_ad
                                   FROM musabakalar m 
                                   JOIN takimlar t1 ON m.ev_sahibi_id=t1.id 
                                   JOIN takimlar t2 ON m.misafir_id=t2.id 
                                   LEFT JOIN ligler l ON m.lig_id=l.id 
                                   LEFT JOIN stadyumlar s ON m.stadyum_id=s.id 
                                   LEFT JOIN users h ON m.hakem_id = h.id
                                   LEFT JOIN users y1 ON m.yardimci_1_id = y1.id
                                   LEFT JOIN users y2 ON m.yardimci_2_id = y2.id
                                   LEFT JOIN users d4 ON m.dorduncu_hakem_id = d4.id
                                   WHERE m.id=? AND m.gozlemci_id=?");
                $st->execute([$musabaka_id, $user_id]); $mac=$st->fetch(PDO::FETCH_OBJ);
                
                $k=$pdo->query("SELECT *, 
                    UNIX_TIMESTAMP(ilk_yari_baslangic) as t1,
                    UNIX_TIMESTAMP(ikinci_yari_baslangic) as t2,
                    UNIX_TIMESTAMP(NOW()) as now
                    FROM musabaka_kronometre WHERE musabaka_id=$musabaka_id")->fetch(PDO::FETCH_ASSOC);
                $startTime=0; $currentHalf=0; $isDone=false; $is40 = 0; $elapsed = 0;
                if($k){ 
                    $isDone = (isset($k['mac_bitti']) && $k['mac_bitti'] == 1);
                    $is40 = $k['is_40_dakika'] ?? 0;
                    if($k['ikinci_yari_baslangic']){ 
                        $elapsed = $k['now'] - $k['t2'];
                        $currentHalf=2; 
                    }
                    elseif($k['ilk_yari_baslangic']){ 
                        $elapsed = $k['now'] - $k['t1'];
                        $currentHalf=1; 
                    } 
                }
            ?>
            
            <div class="bg-white border rounded-xl mb-4 overflow-hidden shadow-sm transition-all">
                <div onclick="toggleHakemler()" class="p-3 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition-colors">
                    <div class="text-[11px] font-bold text-slate-600"><span class="text-blue-600 uppercase"><?=$mac->lig?></span> | No: <?=$mac->mac_no?></div>
                    <div class="text-[11px] font-bold text-slate-500 flex items-center gap-2"><?=$mac->saha?> <i id="chevron-icon" class="fas fa-chevron-down transition-transform duration-300"></i></div>
                </div>
                <div id="hakem-detay" class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-slate-50 border-t border-gray-100">
                    <div class="p-3 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="space-y-1"><p class="text-[9px] text-gray-400 font-bold uppercase">Hakem</p><p class="text-[11px] text-slate-700 font-black"><?=$mac->h_ad ?: '-'?></p></div>
                        <div class="space-y-1"><p class="text-[9px] text-gray-400 font-bold uppercase">Y. Hakem 1</p><p class="text-[11px] text-slate-700 font-black"><?=$mac->y1_ad ?: '-'?></p></div>
                        <div class="space-y-1"><p class="text-[9px] text-gray-400 font-bold uppercase">Y. Hakem 2</p><p class="text-[11px] text-slate-700 font-black"><?=$mac->y2_ad ?: '-'?></p></div>
                        <div class="space-y-1"><p class="text-[9px] text-gray-400 font-bold uppercase">4. Hakem</p><p class="text-[11px] text-slate-700 font-black"><?=$mac->d4_ad ?: '-'?></p></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4 space-y-3">
                    <div class="bg-slate-900 rounded-2xl p-5 text-white text-center shadow-lg border-b-4 border-blue-600">
                        <div id="big_clock" class="text-5xl font-mono font-black mb-6 text-blue-400"><?=$isDone ? 'TAMAMLANDI' : '00:00'?></div>
                        <?php if(!$currentHalf && !$isDone): ?>
                            <button onclick="startTimer(1, 0)" class="w-full bg-green-600 py-3 rounded-lg font-bold text-xs uppercase">MAÇI BAŞLAT</button>
                        <?php elseif($currentHalf==1 && !$isDone): ?>
                            <div class="flex flex-col gap-2">
                                <button onclick="startTimer(2, 0)" class="w-full bg-blue-600 py-3 rounded-lg font-bold text-xs uppercase">2. YARI BAŞLAT (45')</button>
                                <button onclick="if(confirm('Emin misiniz? 2. Yarı 40. dakikadan başlayacak.')) startTimer(2, 1)" class="w-full bg-orange-600 py-3 rounded-lg font-bold text-xs uppercase">40. DK'DAN 2. YARI BAŞLAT</button>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between items-center mt-6 pt-4 border-t border-slate-800">
                            <div class="text-[9px] w-1/3 text-left uppercase truncate text-gray-400"><?=$mac->ev?></div>
                            <div class="text-4xl font-black tracking-tighter"><?=$mac->ev_skor?>-<?=$mac->misafir_skor?></div>
                            <div class="text-[9px] w-1/3 text-right uppercase truncate text-gray-400"><?=$mac->mis?></div>
                        </div>
                        <div class="flex justify-center gap-4 mt-4">
                            <form method="POST" onsubmit="return confirm('Sıfırlansın mı?')"><button name="reset_match" class="text-[9px] text-gray-500 underline uppercase">Verileri Sıfırla</button></form>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="hnd('EV','GOL')" class="bg-blue-600 text-white py-3 rounded-lg font-bold text-[10px]">EV GOL</button>
                        <button onclick="hnd('MIS','GOL')" class="bg-blue-600 text-white py-3 rounded-lg font-bold text-[10px]">MIS GOL</button>
                        <button onclick="hnd('EV','İHTAR')" class="bg-yellow-400 text-yellow-900 py-3 rounded-lg font-bold text-[10px]">EV İHTAR</button>
                        <button onclick="hnd('MIS','İHTAR')" class="bg-yellow-400 text-yellow-900 py-3 rounded-lg font-bold text-[10px]">MIS İHTAR</button>
                        <button onclick="hnd('EV','İHRAÇ')" class="bg-red-600 text-white py-3 rounded-lg font-bold text-[10px]">EV İHRAÇ</button>
                        <button onclick="hnd('MIS','İHRAÇ')" class="bg-red-600 text-white py-3 rounded-lg font-bold text-[10px]">MIS İHRAÇ</button>
                    </div>
                    <button onclick="takeReport()" class="w-full bg-emerald-600 text-white text-center py-3 rounded-xl font-bold text-xs shadow-lg uppercase">GEÇİCİ MÜSABAKA RAPORUNU AL</button>
                </div>
                <div class="md:col-span-8">
                    <div class="bg-white p-4 rounded-2xl border h-full flex flex-col shadow-sm">
                        <form id="eventForm" method="POST" class="space-y-3">
                            <input type="hidden" name="mac_dakikasi" id="post_dk" value="0">
                            <input type="hidden" name="olay_kaydet" value="1"><input type="hidden" name="is_gol" id="post_is_gol">
                            <div class="flex flex-wrap gap-1">
                                <?php foreach(['Hakem','Y.Hakem 1','Y.Hakem 2','4.Hakem','Genel'] as $rk): ?>
                                    <label class="cursor-pointer"><input type="radio" name="ilgili_kisi" value="<?=$rk?>" id="radio_<?=$rk?>" class="hidden peer" <?=$rk=='Hakem'?'checked':''?>><span class="peer-checked:bg-blue-600 peer-checked:text-white bg-gray-100 px-3 py-1.5 rounded-full text-[9px] font-bold uppercase inline-block"><?=$rk?></span></label>
                                <?php endforeach; ?>
                            </div>
                            <div class="flex gap-2"><input type="text" name="olay_detay" id="post_detay" placeholder="Notunuz..." class="flex-1 bg-gray-50 border rounded-xl p-2.5 text-sm outline-none"><button class="bg-slate-800 text-white px-5 rounded-xl font-bold text-[10px] uppercase">Kaydet</button></div>
                        </form>
                        <div class="mt-4 flex-1 overflow-y-auto max-h-[400px] space-y-2 pr-1">
                            <?php $notlar=$pdo->prepare("SELECT * FROM musabaka_notlar WHERE musabaka_id=? ORDER BY id DESC"); $notlar->execute([$musabaka_id]);
                            while($o=$notlar->fetch(PDO::FETCH_OBJ)): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl group border border-transparent hover:border-gray-200 transition-all">
                                    <div class="text-xs text-gray-700"><span class="font-black text-blue-600 mr-2"><?=$o->dakika?>'</span> <?=htmlspecialchars($o->not_icerigi)?></div>
                                    <form method="POST"><input type="hidden" name="not_id" value="<?=$o->id?>"><button name="not_sil" class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100"><i class="fas fa-trash-alt"></i></button></form>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let elapsed = <?=$elapsed ?: 0?>;
    let baseMin = <?= ($currentHalf == 2) ? ($is40 ? 40 : 45) : 0 ?>;
    let isDone = <?= $isDone ? 'true' : 'false' ?>;
    let pageLoadTime = Date.now();
    let timerInterval = null;

    // --- KRİTİK GÜNCELLEME 2: OTURUMU CANLI TUTMA (KEEP-ALIVE) ---
    // Gözlemci not girmeden dursa bile 10 dakikada bir sunucuya selam verir, logout'u engeller.
    setInterval(function() {
        fetch('config/session_check.php').then(r => console.log('Session refresh...'));
    }, 600000); 
    // -----------------------------------------------------------
    
    function startTimer(yari, is40) {
        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'krono_aksiyon=1&yari=' + yari + (is40 ? '&is_40=1' : '')
        }).then(function(response) {
            elapsed = 0;
            baseMin = (yari == 2) ? (is40 ? 40 : 45) : 0;
            pageLoadTime = Date.now();
            isDone = false;
            
            if(timerInterval) clearInterval(timerInterval);
            updateClock();
            timerInterval = setInterval(updateClock, 1000);
            setTimeout(function(){ location.reload(); }, 1000);
        });
    }
    
    function updateClock() {
        if (elapsed === 0 && baseMin === 0) return;
        if (isDone) return;
        let now = Date.now();
        let addSeconds = Math.floor((now - pageLoadTime) / 1000);
        let totalElapsed = elapsed + addSeconds;
        let m = baseMin + Math.floor(totalElapsed / 60);
        let s = totalElapsed % 60;
        document.getElementById('big_clock').innerText = (m<10?'0'+m:m)+":"+(s<10?'0'+s:s);
        document.getElementById('post_dk').value = m;
    }
    
    function toggleHakemler() {
        const detay = document.getElementById('hakem-detay');
        const icon = document.getElementById('chevron-icon');
        if (detay.style.maxHeight === '0px' || detay.style.maxHeight === '') {
            detay.style.maxHeight = detay.scrollHeight + 'px'; icon.style.transform = 'rotate(180deg)';
        } else {
            detay.style.maxHeight = '0px'; icon.style.transform = 'rotate(0deg)';
        }
    }
    
    function takeReport() { if(confirm('Rapor oluşturulacaktır. Emin misiniz?')) window.location.href = "?id=<?=$musabaka_id?>&view=report"; }
    
    function hnd(t, tip) {
        if(isDone) return;
        let tAd = (t === 'EV') ? '<?=$mac->ev?>' : '<?=$mac->mis?>';
        if (tip === 'GOL') { document.getElementById('post_detay').value = tAd + " GOL ATTILAR"; document.getElementById('post_is_gol').value = t; document.getElementById('radio_Genel').checked = true; } 
        else { let fno = prompt(tip + " bilgisi:"); if(!fno) return; document.getElementById('post_detay').value = tAd + " " + tip + " (" + fno + ")"; document.getElementById('radio_Hakem').checked = true; }
        document.getElementById('eventForm').submit();
    }
    
    if(elapsed > 0 && !isDone) {
        updateClock();
        timerInterval = setInterval(updateClock, 1000);
    }
</script>
<?php endif; ?>
<?php include 'templates/footer.php'; ?>