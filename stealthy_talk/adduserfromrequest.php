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

    // Check if username is provided
    if (isset($input['username']) && isset($input['password'])) {
        $username = $input['username'];

        // Check if username already exists
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = $row['count'];

        if ($count > 0) {
            // Username already exists
            $response = array('status' => 'error', 'message' => 'Username already exists');
        } else {
            // Insert new user data into the database
            $password_hash = password_hash($input['password'], PASSWORD_DEFAULT);
            $firstName = $input['firstName'];
            $lastName = $input['lastName'];
            $email = $input['email'];
            $phone = $input['phone'];
            $role_id = $input['role_id']; // Make sure you have a valid role_id
            $status_id = $input['status_id'];
            $profile_data = isset($input['profile_data']) ? $input['profile_data'] : '';

            $stmt = $conn->prepare("INSERT INTO users (firstName, lastName, email, phone, username, password_hash, role_id, status_id, profile_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssiis", $firstName, $lastName, $email, $phone, $username, $password_hash, $role_id, $status_id, $profile_data);
            if ($stmt->execute()) {
                // User created successfully

                // Remove the corresponding row from user_request table where username matches
                $stmt_delete = $conn->prepare("DELETE FROM user_request WHERE username = ?");
                $stmt_delete->bind_param("s", $username);
                if ($stmt_delete->execute()) {
                    $response = array('status' => 'success', 'message' => 'User created successfully and user request deleted');
                } else {
                    $response = array('status' => 'success', 'message' => 'User created successfully but failed to delete user request');
                }
                $stmt_delete->close();
            } else {
                $response = array('status' => 'error', 'message' => 'Error creating user');
            }
        }

        // Close the statement
        $stmt->close();
    } else {
        // Username or password not provided
        $response = array('status' => 'error', 'message' => 'Username or password not provided');
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
