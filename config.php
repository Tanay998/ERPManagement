<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'schoolfeesys'); // Replace with your database name

// Establish a database connection
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if the connection was successful
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}
?>