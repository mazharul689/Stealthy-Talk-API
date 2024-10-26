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
    $dbPort = 3306;  // Change this to your database port (if different from default)

    // Create a new database connection
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if user ID is provided in the query string
    if (isset($_GET['id']) && isset($input['username']) && isset($input['password'])) {
        $user_id = $_GET['id']; // Get the user ID from the query string
        $username = $input['username'];

        // Check if the user exists
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'];

        if ($count == 0) {
            // User not found
            $response = array('status' => 'error', 'message' => 'User not found');
        } else {
            // Update user data
            $password_hash = password_hash($input['password'], PASSWORD_DEFAULT);
            $firstName = $input['firstName'];
            $lastName = $input['lastName'];
            $email = $input['email'];
            $phone = $input['phone'];
            $role_id = $input['role_id']; // Make sure you have a valid role_id
            $status_id = 1; // Optional field
            $profile_data = isset($input['profile_data']) ? $input['profile_data'] : '';

            $stmt = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ?, phone = ?, username = ?, password_hash = ?, role_id = ?, status_id = ?, profile_data = ? WHERE user_id = ?");
            $stmt->bind_param("ssssssiisi", $firstName, $lastName, $email, $phone, $username, $password_hash, $role_id, $status_id, $profile_data, $user_id);
            if ($stmt->execute()) {
                $response = array('status' => 'success', 'message' => 'User updated successfully');
            } else {
                $response = array('status' => 'error', 'message' => 'Error updating user');
            }
        }

        // Close the statement
        $stmt->close();
    } else {
        // User ID, username, or password not provided
        $response = array('status' => 'error', 'message' => 'User ID, username, or password not provided');
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
