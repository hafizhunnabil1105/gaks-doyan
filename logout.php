<?php
include 'config/koneksi.php';
session_destroy();
header("Location: " . BASE_URL . "login.php");
exit;
?>