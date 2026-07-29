<?php
include('../config/db.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Veri kaydetme kısmı
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tarih   = $_POST['tarih'];
  $saat    = $_POST['saat'];
  $takim1  = $_POST['takim1'];
  $takim2  = $_POST['takim2'];
  $yer     = $_POST['yer'];

  foreach ($tarih as $i => $tr) {
    if (!$tr || !$takim1[$i] || !$takim2[$i]) continue;

    $tr  = mysqli_real_escape_string($conn, $tr);
    $s   = mysqli_real_escape_string($conn, $saat[$i]);
    $t1  = mysqli_real_escape_string($conn, $takim1[$i]);
    $t2  = mysqli_real_escape_string($conn, $takim2[$i]);
    $y   = mysqli_real_escape_string($conn, $yer[$i]);

    $sql = "INSERT INTO musabakalar (tarih, saat, takim1, takim2, yer, yayinda)
            VALUES ('$tr', '$s', '$t1', '$t2', '$y', 0)";
    mysqli_query($conn, $sql);
  }

  echo "<div style='padding:15px; background:#dff0d8; color:#3c763d;'>Müsabakalar taslak olarak kaydedildi.</div>";
}
?>

<!-- Form kısmı -->
<h2>Toplu Müsabaka Ekle</h2>
<form method="post">
  <table border="1" cellpadding="6">
    <?php for ($i = 0; $i < 5; $i++): ?>
    <tr>
      <td><input type="text" name="tarih[]" placeholder="Tarih"></td>
      <td><input type="text" name="saat[]" placeholder="Saat"></td>
      <td><input type="text" name="takim1[]" placeholder="Takım 1"></td>
      <td><input type="text" name="takim2[]" placeholder="Takım 2"></td>
      <td><input type="text" name="yer[]" placeholder="Yer"></td>
    </tr>
    <?php endfor; ?>
  </table>
  <br>
  <button type="submit">Kaydet</button>
</form>
