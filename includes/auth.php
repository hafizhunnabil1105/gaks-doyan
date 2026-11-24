<?php
function wajib_login() {
    if (!isset($_SESSION['user'])) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

function wajib_admin() {
    wajib_login();
    if ($_SESSION['user']['role'] !== 'admin') {
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}
?>