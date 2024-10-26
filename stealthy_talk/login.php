<?php
// Enable CORS (Cross-Origin Resource Sharing) headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Add more methods as needed


// Respond to preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the posted JSON data
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);

    // Database connection details
    $dbHost = 'localhost';  // Change this to your database host
    $dbUsername = 'root';  // Change this to your database username
    $dbPassword = '';  // Change this to your database password
    $dbName = 'stealthy_talk';  // Change this to your database name
    $dbPort = 3306;
    // Create a new database connection
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if username and password are provided
    if (isset($input['username']) && isset($input['password'])) {
        // Retrieve user data from the database with role name
        $stmt = $conn->prepare("SELECT u.user_id, u.username, u.password_hash, u.role_id, r.role_name 
                                FROM users u 
                                JOIN roles r ON u.role_id = r.role_id 
                                WHERE u.username = ?");
        $stmt->bind_param("s", $input['username']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify password
            if (password_verify($input['password'], $user['password_hash'])) {
                $response = array('status' => 'success', 'message' => 'Login successful', 'user' => $user);
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid password');
            }
        } else {
            $response = array('status' => 'error', 'message' => 'User not found');
        }

        // Close the statement
        $stmt->close();
    } else {
        $response = array('status' => 'error', 'message' => 'Username and password are required');
    }

    // Close database connection
    $conn->close();

    // Send response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.1 405 Method Not Allowed");
}
?>
