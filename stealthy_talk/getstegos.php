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

// Check if the request is a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Database connection details
    $dbHost = 'localhost';  // Change this to your database host
    $dbUsername = 'root';  // Change this to your database username
    $dbPassword = '';  // Change this to your database password
    $dbName = 'ecom';  // Change this to your database name
    $dbPort = 3306;  // Change this to your database port (if different from default)

    // Create a new database connection
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve image_id from the request
    $image_id = isset($_GET['image_id']) ? intval($_GET['image_id']) : null;

    // Validate image_id
    if ($image_id !== null) {
        // Prepare and execute the SQL statement
        $stmt = $conn->prepare("SELECT message FROM secret WHERE image_id = ?");
        $stmt->bind_param("i", $image_id);

        // Execute the statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $response = array('status' => 'success', 'message' => $row['message']);
            } else {
                $response = array('status' => 'error', 'message' => 'No message found for the provided image_id');
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Error executing query: ' . $stmt->error);
        }

        // Close the statement
        $stmt->close();
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid image_id');
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
