<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: index.php");
    exit();
}

// Sipariş ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: siparis_gecmisi.php");
    exit();
}

$siparis_id = intval($_GET['id']);

// Sipariş detaylarını çekme
$query = "SELECT o.id, o.toplam_fiyat, o.tarih, od.urun_adi, od.adet, od.birim_fiyat 
          FROM orders o 
          JOIN order_details od ON o.id = od.order_id 
          WHERE o.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $siparis_id);
$stmt->execute();
$detaylar = $stmt->get_result();

// Sipariş bilgilerini alma
$siparis = $detaylar->fetch_assoc();
$toplam_fiyat = $siparis['toplam_fiyat'];
$tarih = $siparis['tarih'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sipariş Detayları</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .container {
            width: 90%;
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📦 Sipariş Detayları</h2>
        <p><strong>Sipariş Tarihi:</strong> <?php echo date("d-m-Y H:i", strtotime($tarih)); ?></p>
        <p><strong>Toplam Fiyat:</strong> <?php echo number_format($toplam_fiyat, 2); ?> TL</p>

        <table>
            <tr>
                <th>Ürün Adı</th>
                <th>Adet</th>
                <th>Birim Fiyat</th>
                <th>Toplam</th>
            </tr>
            <?php
            // Sipariş ürünlerini tekrar çekiyoruz (çoklu ürünler için)
            $stmt->execute();
            $detaylar = $stmt->get_result();
            while ($row = $detaylar->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['urun_adi']); ?></td>
                    <td><?php echo $row['adet']; ?></td>
                    <td><?php echo number_format($row['birim_fiyat'], 2); ?> TL</td>
                    <td><?php echo number_format($row['adet'] * $row['birim_fiyat'], 2); ?> TL</td>
                </tr>
            <?php } ?>
        </table>

        <div class="back-link">
            <a href="siparis_gecmisi.php">⬅️ Sipariş Geçmişine Dön</a>
        </div>
    </div>
</body>
</html>
