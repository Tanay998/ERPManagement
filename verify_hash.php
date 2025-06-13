// Create a test file verify_hash.php
<?php
$password = 'rupendra@123';
$hash = '$2y$10$FhOCfgu7lXPNQSSKxXe2eOf3Lcp/EeBvs2pQsYBb5eyHKMrVQvAdC';
echo password_verify($password, $hash) ? 'Match' : 'No match';
?>