<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: index.php");
    exit();
}

// Satış işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $urun_id = intval($_POST['urun_id']);
    $adet = intval($_POST['adet']);
    $kullanici_adi = $_SESSION['kullanici_adi'];

    // Kullanıcı bilgisi
    $kullanici_sorgu = "SELECT id FROM kullanicilar WHERE kullanici_adi = ?";
    $stmt = $conn->prepare($kullanici_sorgu);
    $stmt->bind_param("s", $kullanici_adi);
    $stmt->execute();
    $result = $stmt->get_result();
    $kullanici = $result->fetch_assoc();
    $kullanici_id = $kullanici['id'];

    // Stok kontrolü
    $stok_sorgu = "SELECT stok FROM urunler WHERE id = ?";
    $stmt = $conn->prepare($stok_sorgu);
    $stmt->bind_param("i", $urun_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $urun = $result->fetch_assoc();

    if ($adet > $urun['stok']) {
        $hata_mesaji = "⚠️ Yetersiz stok!";
    } else {
        // Satış işlemi ve stok güncelleme
        $satis_query = "INSERT INTO satislar (kullanici_id, urun_id, adet) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($satis_query);
        $stmt->bind_param("iii", $kullanici_id, $urun_id, $adet);
        $stmt->execute();

        $stok_guncelle = "UPDATE urunler SET stok = stok - ? WHERE id = ?";
        $stmt = $conn->prepare($stok_guncelle);
        $stmt->bind_param("ii", $adet, $urun_id);
        $stmt->execute();
        $basari_mesaji = "✅ Satış başarılı!";
    }
}
