<?php
session_start();
if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: index.php");
    exit;
}
