<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü (Sadece Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Tüm siparişleri çekme
$query = "SELECT o.id AS siparis_id, o.tarih, o.toplam_fiyat, k.kullanici_adi 
          FROM orders o 
          JOIN kullanicilar k ON o.kullanici_id = k.id 
          ORDER BY o.tarih DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>📜 Tüm Siparişler - Admin</title>
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
        .details a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
        }
        .details a:hover {
            text-decoration: underline;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📜 Tüm Siparişler (Admin)</h2>
        <table>
            <tr>
                <th>Sipariş ID</th>
                <th>Kullanıcı</th>
                <th>Tarih</th>
                <th>Toplam Fiyat</th>
                <th>Detaylar</th>
            </tr>
            <?php if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['siparis_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['kullanici_adi']); ?></td>
                        <td><?php echo date("d-m-Y H:i", strtotime($row['tarih'])); ?></td>
                        <td><?php echo number_format($row['toplam_fiyat'], 2); ?> TL</td>
                        <td class="details">
                            <a href="admin_siparis_detay.php?siparis_id=<?php echo $row['siparis_id']; ?>">Detaylar</a>
                        </td>
                    </tr>
            <?php } 
            } else { ?>
                <tr><td colspan="5">Henüz sipariş bulunmuyor.</td></tr>
            <?php } ?>
        </table>
        <div class="back-link">
            <a href="dashboard.php">⬅️ Geri Dön</a>
        </div>
    </div>
</body>
</html>
