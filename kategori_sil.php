<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü (Yalnızca Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Kategori silme işlemi
if (isset($_GET['id'])) {
    $kategori_id = intval($_GET['id']);
    $query = "DELETE FROM kategori WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $kategori_id);
    $stmt->execute();
}

header("Location: kategori_yonetimi.php?success=Kategori başarıyla silindi.");
exit();
