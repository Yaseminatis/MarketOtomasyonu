<?php
// Stok hareketini kaydetme fonksiyonu
function stok_hareketi_kaydet($conn, $urun_id, $degisiklik, $tur) {
    // Ürünün mevcut stok bilgisini çekme
    $query = "SELECT stok FROM urunler WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $urun_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $urun = $result->fetch_assoc();

    if ($urun) {
        // Mevcut stok bilgisi
        $onceki_stok = $urun['stok'];
        $yeni_stok = $onceki_stok + $degisiklik;

        // Stok hareketini kaydetme
        $query = "INSERT INTO stok_hareketleri (urun_id, degisiklik, onceki_stok, yeni_stok, tur, tarih) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiss", $urun_id, $degisiklik, $onceki_stok, $yeni_stok, $tur);
        $stmt->execute();

        // Ürünün stok bilgisini güncelleme (urunler tablosu)
        $query = "UPDATE urunler SET stok = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $yeni_stok, $urun_id);
        $stmt->execute();
    }
}
