<?php
// Enable CORS (Cross-Origin Resource Sharing) headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

// Database connection details
$dbHost = 'localhost';  // Change this to your database host
$dbUsername = 'root';   // Change this to your database username
$dbPassword = '';       // Change this to your database password
$dbName = 'stealthy_talk';// Change this to your database name
$dbPort = 3306;         // Change this to your database port (if different from default)

// Create a new database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all rows from the stego_images table
$sql = "SELECT * FROM stego_images";
$result = $conn->query($sql);

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Fetch rows as associative array
    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    // Return rows as JSON
    echo json_encode($rows);
} else {
    // No rows found
    echo json_encode(array());
}

// Close database connection
$conn->close();
?>
