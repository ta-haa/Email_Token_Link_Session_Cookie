<?php
///////////////////////////////////////////////////SQLDEKİ TOKEN COOKİE'NİN TOKENI
///////////////////////////////////////////////////SQLDEKİ CAPTCHA İSE PHP VERİFİCATİON TOKENI
///////////////////////////////////////////////////BİRBİRİNE KARIŞTIRMA 
 
///////////////////////////////////////////////////KULLANICI GİRİŞ YAPTIĞINDA SESSİON VE COOKİE İÇİN TOKEN OLUŞTUR
  
session_start();  
include("connect.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php'; 

//burdaki saat önemli burdaki saat tokenımıza verilecek süreye için mesela 5 dakika sonra token sil gibi
date_default_timezone_set('Europe/Istanbul');

$giris_basarili = false;  
$hata_mesaji = "";  
///////////////////////////////////////////////////GİRİŞ KISMI
if(isset($_POST["giris"])) {

    $email = $_POST['Gemail'];
    $sifre = $_POST['Gsifre'];

    // E-posta doğrulama
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script> document.getElementById("kayithata").innerHTML="Geçersiz email formatı"; </script>';
        exit();
    }

    $salt      = '@donerteka_';
    $hashed    = hash('sha256', $sifre . $salt);

    $email = stripcslashes($email);
    $hashed = stripcslashes($hashed);

    // Prepared statement kullanımı
    $stmt = $con->prepare("SELECT * FROM doner WHERE eposta = ? AND sifre = ?");
    $stmt->bind_param('ss', $email, $hashed);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1) {

        $sqlflip = "SELECT * FROM doner WHERE eposta = ? AND verification = 1";
        $stmt = $con->prepare($sqlflip);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $sonucflip = $stmt->get_result();

        if (mysqli_num_rows($sonucflip) > 0) {
            // Eğer Verification 1 ise giriş yap 

            $giris_basarili = true;
            $_SESSION['Gemail'] = $email;

            $token = bin2hex(random_bytes(32)); 
            $teka = '@donerteka_';
            $hashed_token = password_hash($token.$teka, PASSWORD_DEFAULT);  

            $stmt = $con->prepare("UPDATE doner SET token = ? WHERE eposta = ?");
            $stmt->bind_param('ss', $hashed_token, $email);
            $stmt->execute(); 

            if(isset($_POST['beni_hatirla']) && $_POST['beni_hatirla'] == 'evet') {
                setcookie('token', $hashed_token, time() + (86400 * 30), "/");
                setcookie('Gemail', $email, time() + (86400 * 30), "/");  
                setcookie('beni_hatirla', 'evet', time() + (86400 * 30), "/"); 
  
                header("Location: login.php");
                exit();  
            }


            echo '<div style="color:red;position:fixed;left:87%;top:2%; z-index:3;padding: 10px 15px;border-radius: 4px;color: #ffffff;cursor: pointer;width: 70px;height: 25px;transition: background-color 0.3s ease;background-color: red;">'; 
            echo '<a style="color:white;text-decoration:none;text-align:center;" href="logout.php"><div>Çıkış</div></a>';
            echo '</div>';
            echo '<style>.girisyapildi{display:none}</style>';  


        } else {
            // Eğer Verification 0 ise E-posta doğrulama kodu gönder

            
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////EĞER EPOSTA DOĞRULAMASI YAPMAZSA GİRİŞ YAPMAYA ÇALIŞTIĞINDA EPOSTANIZI DOĞRULAYIN DİYOR ve code tekrar gönderiyor///////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            $mail = new PHPMailer(true);
            try {
                // Sunucu ayarları
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'taha@yahoo.com';
                $mail->Password   = 'asdkalsdşasd231312a1z.';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                // Gönderen ve alıcı ayarları
                $mail->setFrom('Doner@info.com', 'Doner');
                $mail->addAddress($email);

                // E-posta içeriği
                $mail->isHTML(true);
                $mail->Subject = "Email Verification";

                $token = bin2hex(random_bytes(32));
                $teka = '@donerteka_';
                $hashed_token = password_hash($token.$teka, PASSWORD_DEFAULT);

                // Geçerlilik süresi (5 dakika)
                $expiry = new DateTime();
                $expiry->add(new DateInterval('PT5M'));
                $expiry = $expiry->format('Y-m-d H:i:s');

                $verifyLink = "http://localhost/start/verify.php?token=" . urlencode($hashed_token);

                $mail->Body = "<p>Your verification link is: <a style='font:bold 30px arial;color:red;text-decoration:none' href='".$verifyLink."'>Verify</a></p>";

                $mail->send();

                // Token ve geçerlilik süresini veri tabanına ekle
                $stmt = $con->prepare("UPDATE doner SET captcha = ?, captchaexpiry = ? WHERE eposta = ?");
                $stmt->bind_param("sss", $hashed_token, $expiry, $email);

                if (!$stmt->execute()) {
                    die("Veri eklenirken bir hata oluştu: " . $stmt->error);
                }

                echo "<h1 style='color:red'>Lütfen E-posta Doğrulayın. E-posta başarıyla gönderildi.</h1>";
            } catch (Exception $e) {
                echo "Mesaj gönderilemedi. Mailer Hatası: {$mail->ErrorInfo}";
            }
        }
    } else {    
        $hata_mesaji = "Geçersiz kullanıcı adı veya şifre";
    }

    $stmt->close();
    mysqli_close($con);
} 
?>
 


<?php
///////////////////////////////////////////////////SESSİON VE COOKİE TOKEN KONTROL
///////////////////////////////////////////////////EĞER TOKEN SQLDEKİ İLE DENK İSE DİV OLUŞTURUYO

if (isset($_SESSION['Gemail']) && isset($_SESSION['token'])) {
    include("connect.php");

    $Semail = $_SESSION['Gemail'];
    $token = $_SESSION['token'];
      
    $stmt = $con->prepare("SELECT eposta, token FROM doner WHERE eposta = ? AND token = ?");
    $stmt->bind_param("ss", $Semail, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) { 
        $row = $result->fetch_assoc(); 

        echo '<div style="color:red;position:fixed;left:87%;top:2%; z-index:3;padding: 10px 15px;border-radius: 4px;color: #ffffff;cursor: pointer;width: 70px;height: 25px;transition: background-color 0.3s ease;background-color: red;">'; 
        echo '<a style="color:white;text-decoration:none;text-align:center;" href="logout.php"><div>Çıkış</div></a>';
        echo '</div>';
        echo '<style>.girisyapildi{display:none}</style>'; 

        echo "Giris Yapildi."; 

    } else {
        echo "Kullanıcı veya token bulunamadı."; 
    }

    


    $stmt->close();
    $con->close(); 

} else { 
}

?> 
   
<!-- KAYIT OL FORM -->

        <div class="form-container" id="signup-container">
            <h2>Kayıt Ol / Sign Up</h2>
            <form id="login-form" method="POST"> 
                <input type="text" id="signup-username" required placeholder="Mail" name="Kemail"> 
<br/><br/>
                <input type="password" id="signup-password" required placeholder="Şifre" name="Ksifre">
<br/><br/>

<div id="kayithata" style="padding:5px;color:red;text-align:center "></div>

<br/>
                <button type="submit" class="btnkayit" name="uyeol">Kayıt Ol</button>
                
                
            </form>
        </div>


 



        
 <?php 
 
////////////////////////////UYE KAYIT ETTİKTEN SONRA 5 DAKİKALIK SÜRESİ OLAN VERİFİCATİON KODU GÖNDERİYOR
////////////////////////////YUKARDA PHPMAİLER ÇAĞIRDIĞIMIZ İÇİN BURDA ÇAĞIRAMIYORUZ
//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;
//require 'phpmailer/src/Exception.php';
//require 'phpmailer/src/PHPMailer.php';
//require 'phpmailer/src/SMTP.php'; 

include("connect.php");

// Türkiye saat dilimini ayarla
date_default_timezone_set('Europe/Istanbul');

///////////////////////////////////////////////////KAYIT KISMI

if (isset($_POST["uyeol"])) {

    $email = filter_var(trim($_POST['Kemail']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['Ksifre']); 

    // E-posta doğrulama
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script> document.getElementById("kayithata").innerHTML="Geçersiz email formatı"; </script>';
        exit();
    }

    // Şifre hashleme
    $salt = '@donerteka_';
    $hashedPassword = hash('sha256', $password . $salt);

    // Kullanıcıyı kontrol et
    $stmt = $con->prepare("SELECT * FROM doner WHERE eposta = ? AND sifre = ?");
    $stmt->bind_param("ss", $email, $hashedPassword);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<script> document.getElementById("kayithata").innerHTML="Kullanıcı Mevcut"; </script>';
    } else {
        // Kullanıcıyı ekle
        $stmt = $con->prepare("INSERT INTO doner (eposta, sifre) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashedPassword);

        if ($stmt->execute()) {
            echo '<script> document.getElementById("kayithata").innerHTML="Aramıza Hoşgeldin"; </script>';

            // E-posta doğrulama işlemi
            $mail = new PHPMailer(true);
            try {
                // Sunucu ayarları
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'taha@yahoo.com';
                $mail->Password   = 'ğolıkjhgqfczsadqweçç.ç';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                // Gönderen ve alıcı ayarları
                $mail->setFrom('Doner@info.com', 'Doner');
                $mail->addAddress($email,$email);

                // E-posta içeriği
                $mail->isHTML(true);
                $mail->Subject = "Email Verification";

                $token = bin2hex(random_bytes(32));
                $teka = '@donerteka_';
                $hashed_token = password_hash($token.$teka, PASSWORD_DEFAULT);

                // Geçerlilik süresi (5 dakika)
                $expiry = new DateTime();
                $expiry->add(new DateInterval('PT5M'));
                $expiry = $expiry->format('Y-m-d H:i:s'); 

                $verifyLink = "http://localhost/emailcode/token_verification_emailcode/verify.php?token=" . urlencode($hashed_token);

                $mail->Body = "<p>Your verification link is: <a style='font:bold 30px arial;color:red;text-decoration:none' href='".$verifyLink."'>Verify</a></p>";

                $mail->send();
 
                // Token ve geçerlilik süresini veri tabanına ekle
                $stmt = $con->prepare("UPDATE doner SET captcha = ?, captchaexpiry = ? WHERE eposta = ?");
                $stmt->bind_param("sss", $hashed_token, $expiry, $email);

                if (!$stmt->execute()) {
                    die("Veri eklenirken bir hata oluştu: " . $stmt->error);
                }

                echo "Doğrulama Kodu E-posta'na başarıyla gönderildi.";
            } catch (Exception $e) {
                echo "Mesaj gönderilemedi. Mailer Hatası: {$mail->ErrorInfo}";
            }
        } else {
            echo "<script> document.getElementById('kayithata').innerHTML='Üye eklenirken hata oluştu: " . htmlspecialchars($stmt->error, ENT_QUOTES, 'UTF-8') . "'; </script>";
        }

        $stmt->close();
    }

    $con->close();
} 
    
?>
 


<div class="form-container" id="login-container" >
            <h2>Giriş Yap / Login</h2>
            <form id="sgnform" name="sgnfrmkkontrol" method="POST" onsubmit = "return get_action()"> 
                <input type="text" id="sgntxteposta" required placeholder="Mail" name="Gemail">
 <br/><br/>
                <input type="password" id="sgntxtksifre" required placeholder="Şifre" name="Gsifre">
<br/><br/>


<div>Beni Hatırla / Remember Me</div> 
<div class="cbxhatirla">
  <input id="cbx" type="checkbox" name="beni_hatirla" value="evet">  
</div>  
 

<br/>
                <button type="submit"  class="btnlogin" name="giris">Giriş Yap</button> 
                <br/>
                

            </form>
        </div>

        <div class="message" id="message"></div>
 
    </div>



















































