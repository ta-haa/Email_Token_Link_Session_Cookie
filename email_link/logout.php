<?php
session_start();

// Veritabanı bağlantısı (eğer gerekliyse, bağlantıyı buraya ekleyin)
include("connect.php");

// Oturum verilerini temizle
$_SESSION = array(); // Oturumdaki tüm verileri temizler

// Oturum çerezlerini temizle
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
              $params["path"], $params["domain"], 
              $params["secure"], $params["httponly"]);
}

// Oturumu yok et
session_destroy();

// Çerezleri temizle
setcookie('beni_hatirla', '', time() - 3600, '/');
setcookie('Gemail', '', time() - 3600, '/');
setcookie('token', '', time() - 3600, '/');

// Yönlendirme: çıkış yapıldıktan sonra anasayfaya yönlendir
header("Location: login.php");
exit;

?>


