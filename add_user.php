<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
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

// Function to generate unique user ID
function generateUserID($email, $pdo) {
    $currentYear = date('Y');
    $emailParts = explode('@', $email);
    $emailPrefix = $emailParts[0]; // Get part before @
    $baseId = $currentYear . '_' . $emailPrefix;
    $userId = $baseId;
    $counter = 1;

    // Check if ID exists and add suffix if needed
    do {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM userregis WHERE userid = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            $userId = $baseId . '_' . $counter;
            $counter++;
        }
    } while ($exists);

    return $userId;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $userid = generateUserID($email, $pdo); // Generate unique ID
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $location = $_POST['location'];

    try {
        $pdo->beginTransaction();

        // Insert into userregis table
        $stmt = $pdo->prepare("INSERT INTO userregis (userid, username, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userid, $username, $email, $password]);

        // Insert into locations table
        $stmt = $pdo->prepare("INSERT INTO locations (userid, location) VALUES (?, ?)");
        $stmt->execute([$userid, $location]);

        $pdo->commit();
        $success = "User added successfully! Generated ID: $userid";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #userIdPreview {
            color: #0d6efd;
            font-weight: bold;
            margin-top: 5px;
        }
        .form-control[readonly] {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Add New User</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="userid" class="form-label">User ID (Auto-generated)</label>
                                <input type="text" class="form-control" id="userid" name="userid" readonly required>
                                <div id="userIdPreview">Enter email to generate ID</div>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       oninput="updateIdPreview()" placeholder="user@example.com">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateIdPreview() {
            const email = document.getElementById('email').value;
            if(email.includes('@')) {
                const currentYear = new Date().getFullYear();
                const emailPrefix = email.split('@')[0];
                const baseId = `${currentYear}_${emailPrefix}`;
                document.getElementById('userIdPreview').textContent = `Base ID will be: ${baseId}`;
            } else {
                document.getElementById('userIdPreview').textContent = 'Enter email to generate ID';
            }
        }
    </script>
</body>
</html>