<?php
// fetch_districts.php
include('config.php'); // Include the database connection

if (isset($_GET['state_id'])) {
    $state_id = intval($_GET['state_id']); // Sanitize the input
    $sql = "SELECT id, name FROM districts WHERE state_id = $state_id ORDER BY name ASC";
    $query = mysqli_query($db, $sql);

    $districts = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $districts[] = $row;
        }
    }
    echo json_encode($districts); // Return districts as JSON
} else {
    echo json_encode([]); // Return an empty array if no state_id is provided
}
?>