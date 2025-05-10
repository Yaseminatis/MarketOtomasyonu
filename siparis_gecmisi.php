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
$kullanici_id = $user['id'];

// Kullanıcının siparişlerini çekme
$query = "SELECT * FROM orders WHERE kullanici_id = ? ORDER BY tarih DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $kullanici_id);
$stmt->execute();
$siparisler = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sipariş Geçmişi</title>
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
            max-width: 800px;
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
        <h2>📜 Sipariş Geçmişiniz</h2>

        <table>
            <tr>
                <th>Sipariş ID</th>
                <th>Tarih</th>
                <th>Toplam Fiyat</th>
                <th>Detaylar</th>
            </tr>
            <?php while ($siparis = $siparisler->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $siparis['id']; ?></td>
                    <td><?php echo date("d-m-Y H:i", strtotime($siparis['tarih'])); ?></td>
                    <td><?php echo number_format($siparis['toplam_fiyat'], 2); ?> TL</td>
                    <td><a href="siparis_detay.php?id=<?php echo $siparis['id']; ?>">Görüntüle</a></td>
                </tr>
            <?php } ?>
        </table>

        <div class="back-link">
            <a href="dashboard.php">⬅️ Geri Dön</a>
        </div>
    </div>
</body>
</html>
