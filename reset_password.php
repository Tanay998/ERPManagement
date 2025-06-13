<?php
session_start();
date_default_timezone_set('Asia/Kolkata'); // Set your timezone

// Database configuration
$host = 'localhost';
$dbname = 'schoolfeesys';
$username = 'root';
$password = '';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$error = '';
$success = '';

// Verify token exists
if (!isset($_GET['token'])) {
    header("Location: login.php?error=missing_token");
    exit();
}

$token = $_GET['token'];
error_log("Reset password accessed with token: $token");

// Check token validity
try {
    $stmt = $pdo->prepare("SELECT userid, reset_token, reset_expiry FROM userregis WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = "Token not found in database.";
        error_log("Token not found: $token");
    } elseif (strtotime($user['reset_expiry']) < time()) {
        $error = "Token expired on " . $user['reset_expiry'];
        error_log("Token expired: $token (Expired at: " . $user['reset_expiry'] . ")");
    } else {
        error_log("Token valid for user: " . $user['userid']);
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log("Database error: " . $e->getMessage());
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($new_password)) {
        $error = "Please enter a new password";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        try {
            // Verify token again
            $stmt = $pdo->prepare("SELECT userid FROM userregis WHERE reset_token = ? AND reset_expiry > NOW()");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE userregis SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE reset_token = ?");
                if ($stmt->execute([$hashed_password, $token])) {
                    // Clear any existing sessions
                    session_unset();
                    session_destroy();
                    
                    $success = "Password reset successfully! You can now <a href='login.php' class='alert-link'>login</a> with your new password.";
                    error_log("Password reset successful for token: $token");
                } else {
                    $error = "Failed to update password. Please try again.";
                }
                
                $success = "Password reset successfully! You can now <a href='login.php' class='alert-link'>login</a> with your new password.";
                error_log("Password reset successful for token: $token");
            } else {
                $error = "Invalid or expired reset token.";
            }
        } catch (PDOException $e) {
            $error = "Error updating password: " . $e->getMessage();
            error_log("Password reset error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ERP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .reset-card {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background: white;
        }
        .password-note {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-card">
            <h2 class="text-center mb-4">Reset Password</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-secondary">Back to Login</a>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                        <div class="password-note mt-1">Minimum 8 characters</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" name="reset_password" class="btn btn-primary w-100 py-2">Reset Password</button>
                    
                    <div class="text-center mt-3">
                        <a href="login.php">Back to Login</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>