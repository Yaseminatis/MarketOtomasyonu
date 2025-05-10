<?php
// Şifreyi güvenli bir şekilde hash etme
$hashed_password = password_hash('admin123', PASSWORD_BCRYPT);
echo "Hashli Şifre: " . $hashed_password;
