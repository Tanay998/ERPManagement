<?php
session_start();

if(!isset($_SESSION['temp_user']) || !isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
    header("Location: login.php");
    exit();
}

$entered_otp = (int)$_POST['otp'];
$stored_otp = (int)$_SESSION['otp'];
$otp_expiry = (int)$_SESSION['otp_expiry'];

if(time() > $otp_expiry) {
    session_unset();
    session_destroy();
    header("Location: login.php?error=otp_expired");
    exit();
}

if($entered_otp === $stored_otp) {
    // OTP verified, create user session
    $_SESSION['user_id'] = $_SESSION['temp_user']['user_id'];
    $_SESSION['login_type'] = $_SESSION['temp_user']['login_type'];
    
    unset($_SESSION['temp_user']);
    unset($_SESSION['otp']);
    unset($_SESSION['otp_expiry']);
    
    header("Location: index.php");
    exit();
} else {
    header("Location: otp_verify.php?error=invalid_otp");
    exit();
}
?>