<?php
session_start();
include 'includes/db_connect.php';
include 'includes/stok_hareketi.php'; // Stok hareketi kaydı için fonksiyon

// Kullanıcı giriş kontrolü (Yalnızca Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Ürün silme işlemi
if (isset($_GET['id'])) {
    $urun_id = intval($_GET['id']);

    // Ürün bilgilerini çekme
    $query = "SELECT * FROM urunler WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $urun_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $urun = $result->fetch_assoc();

    if ($urun) {
        $onceki_stok = $urun['stok'];
        $stok_degisiklik = -$onceki_stok;
        $yeni_stok = 0; // Silme işleminde stok sıfırlanır

        // Stok hareketi kaydını oluştur
        stok_hareketi_kaydet($conn, $urun_id, $stok_degisiklik, $onceki_stok, $yeni_stok, "Silme");

        // Ürüne ait görseli silme (varsa)
        if (!empty($urun['resim']) && file_exists("uploads/" . $urun['resim'])) {
            unlink("uploads/" . $urun['resim']);
        }

        // Ürünü veritabanından silme
        $query = "DELETE FROM urunler WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $urun_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: urun_listele.php?success=✅ Ürün başarıyla silindi.");
        } else {
            header("Location: urun_listele.php?error=⚠️ Ürün silinemedi. Lütfen tekrar deneyin.");
        }
    } else {
        header("Location: urun_listele.php?error=⚠️ Ürün bulunamadı.");
    }
} else {
    header("Location: urun_listele.php?error=⚠️ Geçersiz istek.");
}
exit();
