<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

// Load Composer's autoloader
require 'vendor/autoload.php';

$host = 'localhost';
$dbname = 'schoolfeesys';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$location = $_POST['location'];
$user_id = $_POST['user_id'];
$password = $_POST['password'];

try {
    $stmt = $pdo->prepare("SELECT u.* FROM userregis u 
                          JOIN locations l ON u.userid = l.userid 
                          WHERE u.userid = ? AND l.location = ?");
    $stmt->execute([$user_id, $location]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Store username in session
        $_SESSION['username'] = $user['username'];
        
        // Generate OTP
        $otp = rand(100000, 999999);
        $otp_expiry = time() + 300; // 5 minutes expiry

        // Store in session
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = $otp_expiry;
        $_SESSION['temp_user'] = [
            'user_id' => $user_id,
            'location' => $location,
            'username' => $user['username'],
            'email' => $user['email']
        ];

        // Create PHPMailer instance
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'testingerp294@gmail.com';
            $mail->Password   = 'vzna msmm notv litv'; // USE APP PASSWORD HERE
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->SMTPDebug  = 0; // Reduce debug level for production

            // Recipients
            $mail->setFrom('no-reply@schoolfeesystem.com', 'School Fee System');
            $mail->addAddress($user['email'], $user['username']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Login';
            $mail->Body    = "Dear ".$user['username'].",<br><br>Your OTP for login is: <strong>".$otp."</strong><br>Valid for 5 minutes.";
            $mail->AltBody = "Your OTP is: ".$otp."\nValid for 5 minutes.";

            $mail->send();
            error_log("OTP email sent to ".$user['email']);
            header("Location: otp_verify.php?otp=".$otp); // Pass OTP for testing
            exit();
        } catch (Exception $e) {
            error_log("Mail Error: ".$e->getMessage()." | ".$mail->ErrorInfo);
            header("Location: login.php?error=email_failed&debug=".urlencode($e->getMessage()));
            exit();
        }
            } else {
                error_log("Login failed for $user_id. Location: $location");
                header("Location: login.php?error=invalid_credentials");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Authentication error: " . $e->getMessage());
            header("Location: login.php?error=database_error");
            exit();
        }
?>