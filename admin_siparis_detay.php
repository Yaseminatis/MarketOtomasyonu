<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü (Sadece Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Sipariş ID kontrolü
$siparis_id = isset($_GET['siparis_id']) ? intval($_GET['siparis_id']) : 0;

// Sipariş detaylarını çekme
$query = "SELECT od.urun_adi, od.adet, od.birim_fiyat 
          FROM order_details od 
          WHERE od.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $siparis_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>📜 Sipariş Detayları - Admin</title>
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
        <h2>📜 Sipariş Detayları (Admin)</h2>
        <table>
            <tr>
                <th>Ürün Adı</th>
                <th>Adet</th>
                <th>Birim Fiyat (TL)</th>
            </tr>
            <?php if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['urun_adi']); ?></td>
                        <td><?php echo $row['adet']; ?></td>
                        <td><?php echo number_format($row['birim_fiyat'], 2); ?> TL</td>
                    </tr>
            <?php } 
            } else { ?>
                <tr><td colspan="3">Sipariş detayları bulunamadı.</td></tr>
            <?php } ?>
        </table>
        <div class="back-link">
            <a href="admin_siparisler.php">⬅️ Siparişlere Geri Dön</a>
        </div>
    </div>
</body>
</html>
