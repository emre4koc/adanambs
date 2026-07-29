<?php
include('../config/db.php');

$result = mysqli_query($conn, "SELECT * FROM musabakalar WHERE yayinda = 0 ORDER BY tarih");

echo "<h2>Yayınlanmamış Müsabakalar</h2><table border='1' cellpadding='6'>";
while($row = mysqli_fetch_assoc($result)) {
  echo "<tr>
    <td>{$row['tarih']}</td>
    <td>{$row['takim1']} - {$row['takim2']}</td>
    <td>{$row['yer']}</td>
    <td><form method='post' action='musabaka_yayinla.php'>
      <input type='hidden' name='id' value='{$row['id']}'>
      <button type='submit'>Yayınla</button>
    </form></td>
  </tr>";
}
echo "</table>";
?>
