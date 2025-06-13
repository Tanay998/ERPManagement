<?php
session_start();
if(!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .otp-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="otp-container">
            <h2 class="text-center mb-4">OTP Verification</h2>
            <?php if(isset($_GET['otp'])): ?>
                <div class="alert alert-info">
                    Demo OTP: <?= htmlspecialchars($_GET['otp']) ?>
                </div>
            <?php endif; ?>
            <form action="otp_auth.php" method="post">
                <div class="mb-3">
                    <label class="form-label">Enter OTP</label>
                    <input type="number" name="otp" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
            </form>
        </div>
    </div>
</body>
</html>