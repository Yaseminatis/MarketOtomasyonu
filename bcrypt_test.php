<?php
// Bcrypt test fonksiyonu
$test_password = "testpassword";
$hashed_password = password_hash($test_password, PASSWORD_BCRYPT);

echo "Hashli Şifre: " . $hashed_password . "<br>";

// Şifreyi doğrulama testi
if (password_verify($test_password, $hashed_password)) {
    echo "Şifre doğrulama BAŞARILI!";
} else {
    echo "Şifre doğrulama BAŞARISIZ!";
}
?>
