<?php
include('../config/db.php');

$id = intval($_POST['id']);
mysqli_query($conn, "UPDATE musabakalar SET yayinda = 1 WHERE id = $id");

header("Location: musabaka_onay.php");
exit;
?>
