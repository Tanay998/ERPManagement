<?php
session_start();
date_default_timezone_set('Asia/Kolkata'); // Set your timezone

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

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

$forgot_message = '';
$forgot_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $email = $_POST['email'];
    
    try {
        $stmt = $pdo->prepare("SELECT userid FROM userregis WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $reset_token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
            
            $stmt = $pdo->prepare("UPDATE userregis SET reset_token = ?, reset_expiry = ? WHERE email = ?");
            if ($stmt->execute([$reset_token, $expiry, $email])) {
                // Generate proper reset link for your ERPManagement2 root
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $reset_link = "$protocol://$_SERVER[HTTP_HOST]/ERPManagement5/reset_password.php?token=$reset_token";
                
                $forgot_message = "<div class='alert alert-info'>
                    <strong>Password Reset Link:</strong><br>
                    <a href='$reset_link' target='_blank'>Click here to reset password</a><br><br>
                    <small>Token: $reset_token<br>Expires: $expiry</small>
                </div>";
                
                error_log("Password reset generated for $email. Token: $reset_token, Expiry: $expiry");
            } else {
                $forgot_error = "Failed to generate reset link. Please try again.";
            }
        } else {
            $forgot_error = "No account found with that email address.";
        }
    } catch (PDOException $e) {
        $forgot_error = "Error: " . $e->getMessage();
        error_log("Forgot password error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .forgot-password-form {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Login</h2>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            
            <?php if($forgot_error): ?>
                <div class="alert alert-danger"><?php echo $forgot_error; ?></div>
            <?php endif; ?>
            
            <?php if($forgot_message): ?>
                <?php echo $forgot_message; ?>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-secondary">Back to Login</a>
                </div>
            <?php else: ?>
                <!-- Login Form -->
                <form action="auth.php" method="post" id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <select name="location" class="form-select" required>
                            <?php
                            $stmt = $pdo->query("SELECT DISTINCT location FROM locations");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='".htmlspecialchars($row['location'])."'>".htmlspecialchars($row['location'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User ID</label>
                        <input type="text" name="user_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                    <div class="text-center mt-3">
                        <a href="#" onclick="toggleForgotPassword()">Forgot Password?</a>
                    </div>
                </form>
                
                <!-- Forgot Password Form -->
                <form method="POST" id="forgotPasswordForm" style="display:none;">
                    <h5 class="text-center mb-3">Reset Password</h5>
                    <div class="mb-3">
                        <label class="form-label">Registered Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <button type="submit" name="forgot_password" class="btn btn-warning w-100">Get Reset Link</button>
                    <div class="text-center mt-3">
                        <a href="#" onclick="toggleForgotPassword()">Back to Login</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleForgotPassword() {
            document.getElementById('loginForm').style.display = 
                document.getElementById('loginForm').style.display === 'none' ? 'block' : 'none';
            document.getElementById('forgotPasswordForm').style.display = 
                document.getElementById('forgotPasswordForm').style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>