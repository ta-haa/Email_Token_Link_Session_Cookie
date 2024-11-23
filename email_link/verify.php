<?php
include("connect.php"); 

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Token'ı veri tabanında karşılaştırmak için hash'leme yerine doğrudan karşılaştırma yapıyoruz.
    $stmt = $con->prepare("SELECT * FROM doner WHERE captcha = ? AND captchaexpiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token geçerli
        echo "E-posta doğrulandı!";

        

        // Token'ı null yap
        $stmt = $con->prepare("UPDATE doner SET captcha = NULL, captchaexpiry = NULL, verification = 1 WHERE captcha = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        // Geri bildirim veya yönlendirme
        // echo '<script>window.location.href = "index.php";</script>';
    } else {
        echo "Geçersiz veya süresi dolmuş token.";
    }

    $stmt->close();
} else {
    echo "Geçersiz istek.";
}

$con->close();
?>
 