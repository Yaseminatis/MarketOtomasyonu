<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: index.php");
    exit();
}

// Kullanıcı ID ve Sepet ID kontrolü
$kullanici_adi = $_SESSION['kullanici_adi'];
$sepet_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Sepet bilgilerini çekme
$query = "SELECT s.urun_id, s.adet, u.stok 
          FROM sepet s 
          JOIN urunler u ON s.urun_id = u.id 
          WHERE s.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $sepet_id);
$stmt->execute();
$result = $stmt->get_result();
$sepet_urun = $result->fetch_assoc();

// Sepet ve ürün kontrolü
if (!$sepet_urun) {
    header("Location: sepet.php?error=⚠️ Sepet ürünü bulunamadı.");
    exit();
}

$urun_id = $sepet_urun['urun_id'];
$adet = $sepet_urun['adet'];

// Veritabanı işlemlerini başlat (transaction)
$conn->begin_transaction();

try {
    // Sepetten ürünü sil
    $query = "DELETE FROM sepet WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sepet_id);
    $stmt->execute();

    // Stoku geri yükle
    $query = "UPDATE urunler SET stok = stok + ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $adet, $urun_id);
    $stmt->execute();

    // İşlemleri tamamla (commit)
    $conn->commit();
    header("Location: sepet.php?success=✅ Ürün sepetten çıkarıldı ve stok geri yüklendi.");
} catch (Exception $e) {
    // Hata durumunda işlemi geri al (rollback)
    $conn->rollback();
    header("Location: sepet.php?error=⚠️ Sepetten ürün silme işlemi başarısız oldu.");
}
exit();
