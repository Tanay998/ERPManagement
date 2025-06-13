<?php
require 'db_connection.php'; // your DB config file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id FROM userregis WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE userregis SET reset_token = ?, reset_expiry = ? WHERE id = ?");
        $update->bind_param("ssi", $token, $expiry, $user_id);
        $update->execute();

        $reset_link = "http://yourdomain.com/reset_password.php?token=$token";

        $subject = "Password Reset Link";
        $message = "Click the following link to reset your password: $reset_link";
        $headers = "From: noreply@yourdomain.com";

        if (mail($email, $subject, $message, $headers)) {
            echo "Password reset link has been sent to your email.";
        } else {
            echo "Failed to send email.";
        }
    } else {
        echo "No account found with this email.";
    }
}
?>
