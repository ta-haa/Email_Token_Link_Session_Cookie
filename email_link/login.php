<?php 
///////////////////////////////////////////////////SESSİON VE COOKİE TOKEN'I SQLDEKİYLE DENKSE
///////////////////////////////////////////////////İNDEX.PHP YÖNLENDİR 
///////////////////////////////////////////////////DEĞİLSE LOGOUT.PHP YÖNLENDİR
session_start(); 
 
include("connect.php");

if (isset($_COOKIE['beni_hatirla']) && $_COOKIE['beni_hatirla'] == 'evet' && isset($_COOKIE['Gemail']) && isset($_COOKIE['token'])) {
    
    $_SESSION['Gemail'] = $_COOKIE['Gemail'];
    $_SESSION['token'] = $_COOKIE['token'];

    $email = $_COOKIE['Gemail'];
    $token = $_COOKIE['token'];

 
    $stmt = $con->prepare("SELECT * FROM doner WHERE eposta = ? AND token = ?");
    $stmt->bind_param('ss', $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();


  


    if ($result->num_rows == 1) {   
        header("Location: index.php"); 
    exit; 
 
    } else { 
        header("Location: logout.php"); 
    }

    $stmt->close();

}

else{
    header("Location: index.php"); 
} 

mysqli_close($con); 
 
?>