<?php
session_start();
include 'includes/db_connect.php';
include 'includes/stok_hareketi.php'; // Stok hareketi kaydı için fonksiyon

// Kullanıcı giriş kontrolü (Yalnızca Admin)
if (!isset($_SESSION['kullanici_adi']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Ürün ID kontrolü ve güvenli çekme
$urun = null;
if (isset($_GET['id'])) {
    $urun_id = intval($_GET['id']);
    
    // Ürün bilgilerini güvenli şekilde çekme
    $query = "SELECT * FROM urunler WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $urun_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $urun = $result->fetch_assoc();
}

// Eğer ürün bilgisi çekilemediyse hata mesajı
if (!$urun) {
    die("⚠️ Ürün bulunamadı. Lütfen geri dönün.");
}

// Ürün güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $urun_id = intval($_POST['id']);
    $urun_adi = trim(htmlspecialchars($_POST['urun_adi']));
    $kategori_id = intval($_POST['kategori_id']);
    $fiyat = floatval($_POST['fiyat']);
    $yeni_stok = intval($_POST['stok']);
    $aciklama = trim(htmlspecialchars($_POST['aciklama']));
    $resim = $urun['resim']; // Mevcut resim

    // Resim yükleme (isteğe bağlı)
    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {
        $resim_adi = uniqid() . "_" . basename($_FILES['resim']['name']);
        $hedef_dosya = "uploads/" . $resim_adi;
        $resim_tipi = strtolower(pathinfo($hedef_dosya, PATHINFO_EXTENSION));
        
        if (in_array($resim_tipi, ['jpg', 'jpeg', 'png'])) {
            if (move_uploaded_file($_FILES['resim']['tmp_name'], $hedef_dosya)) {
                if (!empty($urun['resim']) && file_exists("uploads/" . $urun['resim'])) {
                    unlink("uploads/" . $urun['resim']);
                }
                $resim = $resim_adi;
            }
        }
    }

    // Eski stok değeri
    $eski_stok = intval($urun['stok']);
    $stok_degisiklik = $yeni_stok - $eski_stok;

    // Veritabanında güncelleme
    $query = "UPDATE urunler SET urun_adi = ?, kategori_id = ?, fiyat = ?, stok = ?, aciklama = ?, resim = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sidissi", $urun_adi, $kategori_id, $fiyat, $yeni_stok, $aciklama, $resim, $urun_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Stok hareketi kaydı (Güncelleme)
        stok_hareketi_kaydet($conn, $urun_id, $stok_degisiklik, $eski_stok, $yeni_stok, "Güncelleme");
        header("Location: urun_listele.php?success=✅ Ürün başarıyla güncellendi.");
        exit();
    } else {
        $hata_mesaji = "⚠️ Ürün güncellenemedi. Lütfen tekrar deneyin.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🚀 Ürün Düzenle</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .container {
            width: 400px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        input, textarea, select {
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
            font-weight: bold;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📝 Ürün Düzenle</h2>
        <form action="urun_duzenle.php?id=<?php echo $urun_id; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $urun['id']; ?>">
            <input type="text" name="urun_adi" value="<?php echo htmlspecialchars($urun['urun_adi']); ?>" required><br>
            
            <label>Kategori:</label>
            <select name="kategori_id" required>
                <?php
                $kategori_query = "SELECT * FROM kategori";
                $kategori_result = $conn->query($kategori_query);
                while ($kategori = $kategori_result->fetch_assoc()) {
                    $selected = ($kategori['id'] == $urun['kategori_id']) ? 'selected' : '';
                    echo "<option value='{$kategori['id']}' $selected>{$kategori['kategori_adi']}</option>";
                }
                ?>
            </select><br>

            <input type="number" step="0.01" name="fiyat" value="<?php echo htmlspecialchars($urun['fiyat']); ?>" required><br>
            <input type="number" name="stok" value="<?php echo htmlspecialchars($urun['stok']); ?>" required><br>
            <textarea name="aciklama" placeholder="Açıklama"><?php echo htmlspecialchars($urun['aciklama']); ?></textarea><br>
            <input type="file" name="resim" accept="image/*"><br>
            <button type="submit">Güncelle</button>
        </form>

        <?php if (isset($hata_mesaji)) { ?>
            <p class="message error"><?php echo $hata_mesaji; ?></p>
        <?php } ?>
    </div>
</body>
</html>
