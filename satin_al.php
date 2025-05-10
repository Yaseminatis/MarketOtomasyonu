<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: index.php");
    exit();
}

// Kullanıcı ID'sini alma
$kullanici_adi = $_SESSION['kullanici_adi'];
$query = "SELECT id FROM kullanicilar WHERE kullanici_adi = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $kullanici_adi);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$kullanici_id = $user['id'];

// Sepetteki ürünleri çekme ve stok kontrolü
$query = "SELECT s.urun_id, s.adet, u.urun_adi, u.stok, u.fiyat 
          FROM sepet s 
          JOIN urunler u ON s.urun_id = u.id 
          WHERE s.kullanici_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$result = $stmt->get_result();

// Toplam fiyat ve ürün bilgileri
$toplam_fiyat = 0;
$urunler = [];
$yetersiz_stok = [];

while ($row = $result->fetch_assoc()) {
    if ($row['stok'] < $row['adet']) {
        $yetersiz_stok[] = $row['urun_adi'];
    } else {
        $toplam_fiyat += $row['fiyat'] * $row['adet'];
        $urunler[] = $row;
    }
}

// Yetersiz stok kontrolü
if (!empty($yetersiz_stok)) {
    $hata_mesaji = "⚠️ Stok yetersiz: " . implode(", ", $yetersiz_stok);
    header("Location: sepet.php?error=" . urlencode($hata_mesaji));
    exit();
}

// Sipariş kaydı
$query = "INSERT INTO orders (kullanici_id, toplam_fiyat, tarih) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("id", $kullanici_id, $toplam_fiyat);
$stmt->execute();
$order_id = $stmt->insert_id;

// Sipariş detaylarını kaydetme ve stok düşme işlemi
foreach ($urunler as $urun) {
    // Sipariş detayları kaydı
    $query = "INSERT INTO order_details (order_id, urun_id, urun_adi, adet, birim_fiyat) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisid", $order_id, $urun['urun_id'], $urun['urun_adi'], $urun['adet'], $urun['fiyat']);
    $stmt->execute();

    // Stok güncelleme (güvenli bir şekilde)
    $query = "UPDATE urunler SET stok = stok - ? WHERE id = ? AND stok >= ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $urun['adet'], $urun['urun_id'], $urun['adet']);
    $stmt->execute();
}

// Sepeti temizleme
$query = "DELETE FROM sepet WHERE kullanici_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();

// Başarı mesajı
header("Location: sepet.php?success=✅ Satın alma işlemi başarıyla tamamlandı!");
exit();
