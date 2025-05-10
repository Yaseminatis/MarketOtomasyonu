<?php
session_start();
include 'includes/db_connect.php';

// Kullanıcı giriş kontrolü (Yalnızca Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Stok hareketi ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $urun_id = intval($_POST['urun_id']);
    $miktar = intval($_POST['miktar']);
    $tur = $_POST['tur'];

    // Stok hareketini kaydetme
    $query = "INSERT INTO stok_hareketleri (urun_id, miktar, tur) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $urun_id, $miktar, $tur);
    $stmt->execute();

    // Stok güncelleme
    if ($tur === 'Giris') {
        $update_query = "UPDATE urunler SET stok = stok + ? WHERE id = ?";
    } else {
        $update_query = "UPDATE urunler SET stok = stok - ? WHERE id = ?";
    }
    
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ii", $miktar, $urun_id);
    $update_stmt->execute();

    $basari_mesaji = "✅ Stok hareketi başarıyla kaydedildi.";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Stok Yönetimi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            text-align: center;
            margin-top: 10px;
        }
        .message.success {
            color: green;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>📦 Stok Yönetimi</h2>
        <form action="stok_yonetimi.php" method="post">
            <label for="urun_id">Ürün Seç:</label>
            <select name="urun_id" required>
                <?php
                // Ürünleri listeleme
                $urun_sorgu = "SELECT * FROM urunler ORDER BY urun_adi ASC";
                $urun_sonuc = $conn->query($urun_sorgu);
                while ($urun = $urun_sonuc->fetch_assoc()) {
                    echo "<option value='".$urun['id']."'>".$urun['urun_adi']." (Mevcut: ".$urun['stok'].")</option>";
                }
                ?>
            </select>
            <input type="number" name="miktar" placeholder="Miktar" required>
            <select name="tur">
                <option value="Giris">Giriş</option>
                <option value="Cikis">Çıkış</option>
            </select>
            <button type="submit">Stok Güncelle</button>
        </form>

        <?php if (isset($basari_mesaji)) { ?>
            <p class="message success"><?php echo $basari_mesaji; ?></p>
        <?php } ?>
    </div>
</body>
</html>
