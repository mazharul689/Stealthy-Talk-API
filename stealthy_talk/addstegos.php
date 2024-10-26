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

    // Get the raw POST data
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    // Debug: Output received data
    error_log("Received data: " . print_r($data, true));

    // Retrieve image_id and message from the request
    $image_id = isset($data['image_id']) ? intval($data['image_id']) : null;
    $message = isset($data['message']) ? $data['message'] : '';

    // Debug: Output values to verify
    error_log("image_id: " . $image_id);
    error_log("message: " . $message);

    // Validate inputs
    if ($image_id !== null && !empty($message)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO secret (image_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $image_id, $message);

        // Execute the statement
        if ($stmt->execute()) {
            $response = array('status' => 'success', 'message' => 'Data inserted successfully');
        } else {
            $response = array('status' => 'error', 'message' => 'Error inserting data: ' . $stmt->error);
        }

        // Close the statement
        $stmt->close();
    } else {
        $response = array('status' => 'error', 'message' => 'Invalid input');
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
