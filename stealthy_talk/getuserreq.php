<?php
// Enable CORS (Cross-Origin Resource Sharing) headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, OPTIONS"); // Add more methods as needed

// Respond to preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Database connection details
$dbHost = 'localhost';  // Change this to your database host
$dbUsername = 'root';  // Change this to your database username
$dbPassword = '';  // Change this to your database password
$dbName = 'stealthy_talk';  // Change this to your database name
$dbPort = 3306;  // Change this to your database port (if different from default)

// Create a new database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch user table data with role name
// $sql = "SELECT u.*, r.role_name
//         FROM users u
//         INNER JOIN roles r ON u.role_id = r.role_id";
$sql = "SELECT * FROM user_request";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Store results in an array
    $users = array();
    while($row = $result->fetch_assoc()) {
        // Remove sensitive information from the response
        unset($row['password']);
        // unset($row['profile_data']);
        // unset($row['user_id']);
        // unset($row['role_id']);
        
        $users[] = $row;
    }
    // Send response as JSON
    header('Content-Type: application/json');
    echo json_encode($users);
} else {
    // No users found
    $response = array('status' => 'error', 'message' => 'No users found');
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close database connection
$conn->close();
?>
