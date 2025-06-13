<?php
// fetch_districts1.php
include('config.php'); // Include the database connection

if (isset($_GET['state_id'])) {
    $state_id = intval($_GET['state_id']); // Sanitize the input
    error_log("Received state_id: " . $state_id); // Log the received state_id

    $sql = "SELECT id, name FROM districts WHERE state_id = $state_id ORDER BY name ASC";
    error_log("Executing query: " . $sql); // Log the query

    $query = mysqli_query($db, $sql);

    $districts = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $districts[] = $row;
        }
    } else {
        error_log("Query failed: " . mysqli_error($db)); // Log any query errors
    }
    echo json_encode($districts); // Return districts as JSON
} else {
    echo json_encode([]); // Return an empty array if no state_id is provided
}
?>