<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: index.php");
    exit();
}

// Kullanıcı bilgileri
$kullanici_adi = $_SESSION['kullanici_adi'];

// Kullanıcı ID'sini veritabanından çekme
$query = "SELECT id FROM kullanicilar WHERE kullanici_adi = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $kullanici_adi);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$kullanici_id = $user['id']; // Kullanıcı ID'si

// Ürün ID ve stok kontrolü
$urun_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT stok FROM urunler WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $urun_id);
$stmt->execute();
$result = $stmt->get_result();
$urun = $result->fetch_assoc();

// Ürün ve stok kontrolü
if (!$urun) {
    header("Location: urun_listele.php?error=⚠️ Ürün bulunamadı.");
    exit();
} elseif ($urun['stok'] <= 0) {
    header("Location: urun_listele.php?error=⚠️ Bu ürün stokta yok.");
    exit();
}

// Veritabanı işlemlerini başlat (transaction)
$conn->begin_transaction();

try {
    // Sepette ürün var mı kontrolü
    $query = "SELECT * FROM sepet WHERE kullanici_id = ? AND urun_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $kullanici_id, $urun_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Sepette zaten varsa adet artır
        $query = "UPDATE sepet SET adet = adet + 1 WHERE kullanici_id = ? AND urun_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $kullanici_id, $urun_id);
        $stmt->execute();
    } else {
        // Sepete yeni ürün ekle
        $query = "INSERT INTO sepet (kullanici_id, urun_id, adet) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $kullanici_id, $urun_id);
        $stmt->execute();
    }

    // Stoktan 1 düşme işlemi (güvenli)
    $query = "UPDATE urunler SET stok = stok - 1 WHERE id = ? AND stok > 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $urun_id);
    $stmt->execute();

    // İşlemleri tamamla (commit)
    $conn->commit();

    // Yönlendirme
    header("Location: sepet.php?success=✅ Ürün sepete eklendi.");
} catch (Exception $e) {
    // Hata durumunda işlemi geri al (rollback)
    $conn->rollback();
    header("Location: urun_listele.php?error=⚠️ Sepete ekleme sırasında bir hata oluştu.");
}
exit();
