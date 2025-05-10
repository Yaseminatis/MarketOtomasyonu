<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü (güvenlik)
if (!isset($_SESSION['kullanici_adi'])) {
    header("Location: index.php");
    exit();
}

// Kullanıcı bilgileri
$kullanici_adi = $_SESSION['kullanici_adi'];
$kullanici_rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>🚀 Market Stok Takibi - Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 90%;
            max-width: 900px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .navbar a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
            margin-right: 10px;
        }
        .navbar a:hover {
            text-decoration: underline;
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
        td img {
            border-radius: 5px;
            width: 50px;
            height: 50px;
        }
        .search-bar {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        .search-bar input {
            width: 60%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .search-bar button {
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🚀 Market Stok Takibi - Dashboard</h2>
        <div class="status">
            <p>Hoş geldiniz, <strong><?php echo htmlspecialchars($kullanici_adi); ?></strong> (<?php echo htmlspecialchars($kullanici_rol); ?>)</p>
        </div>

        <div class="navbar">
            <a href="profil.php">👤 Profilim</a>
            <a href="siparis_gecmisi.php">📜 Sipariş Geçmişi</a>
            <?php if ($kullanici_rol === 'admin') { ?>
                <a href="urun_ekle.php">+ Yeni Ürün Ekle</a>
                <a href="urun_listele.php">📦 Ürünleri Listele</a>
                <a href="stok_yonetimi.php">📊 Stok Yönetimi</a>
                <a href="satis_raporu.php">📈 Satış Raporu</a>
                <a href="stok_hareket_raporu.php">📊 Stok Hareket Raporu</a>
                <a href="admin_siparisler.php">📜 Tüm Siparişler</a>

            <?php } else { ?>
                <a href="sepet.php">🛒 Sepetim</a>
            <?php } ?>
            <a href="logout.php">🚪 Çıkış Yap</a>
        </div>

        <h3>📦 Mevcut Ürünler</h3>
        
        <div class="search-bar">
            <form method="get">
                <input type="text" name="search" placeholder="Ürün adı ile arayın..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Ara</button>
            </form>
        </div>

        <table>
            <tr>
                <th>Görsel</th>
                <th>Ürün Adı</th>
                <th>Kategori</th>
                <th>Fiyat (TL)</th>
                <th>Stok</th>
                <th>Açıklama</th>
                <th>İşlemler</th>
            </tr>
            
            <?php
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $query = "SELECT u.*, k.kategori_adi FROM urunler u 
                      LEFT JOIN kategori k ON u.kategori_id = k.id 
                      WHERE u.urun_adi LIKE ? 
                      ORDER BY u.tarih DESC";
            $stmt = $conn->prepare($query);
            $search_param = "%$search%";
            $stmt->bind_param("s", $search_param);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td><img src='uploads/".$row['resim']."' alt='Ürün Görseli'></td>
                        <td>".htmlspecialchars($row['urun_adi'])."</td>
                        <td>".htmlspecialchars($row['kategori_adi'])."</td>
                        <td>".number_format($row['fiyat'], 2)." TL</td>
                        <td>".htmlspecialchars($row['stok'])."</td>
                        <td>".htmlspecialchars($row['aciklama'])."</td>
                        <td>";
                    if ($kullanici_rol === 'admin') {
                        echo "<a href='urun_duzenle.php?id=".$row['id']."'>Düzenle</a> | 
                              <a href='urun_sil.php?id=".$row['id']."' onclick=\"return confirm('Bu ürünü silmek istediğinizden emin misiniz?');\">Sil</a>";
                    } else {
                        echo "<a href='sepet_ekle.php?id=".$row['id']."'>🛒 Sepete Ekle</a>";
                    }
                    echo "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Henüz ürün eklenmedi veya aramanıza uygun ürün bulunamadı.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>

