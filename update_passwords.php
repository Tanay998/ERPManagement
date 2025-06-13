<?php
require_once 'config.php';

// Fetch all users with plain-text passwords
$result = $db->query("SELECT id, password FROM userregis");

while ($user = $result->fetch_assoc()) {
    // Skip if already hashed
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE userregis SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $user['id']);
        $stmt->execute();
    }
}

echo "All passwords have been hashed successfully. Please delete this file immediately.";