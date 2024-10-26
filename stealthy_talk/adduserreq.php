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
    if (isset($input['userName']) && isset($input['password'])) {
        $userName = $input['userName'];

        // Check if username already exists
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM user_request WHERE userName = ?");
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
            $password = password_hash($input['password'], PASSWORD_DEFAULT);
            $firstName = $input['firstName'];
            $lastName = $input['lastName'];
            $email = $input['email'];
            // $userName = $input['userName'];
            $mobileNo = $input['mobileNo'];
            $status_id = 3;
            // $role_id = $input['role_id']; // Make sure you have a valid role_id
            // $profile_data = isset($input['profile_data']) ? $input['profile_data'] : '';

            $stmt = $conn->prepare("INSERT INTO user_request (firstName, lastName, email, mobileNo, userName, `password`, status_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $firstName, $lastName, $email, $mobileNo, $userName, $password, $status_id);
            if ($stmt->execute()) {
                $response = array('status' => 'success', 'message' => 'User request submitted successfully');
            } else {
                $response = array('status' => 'error', 'message' => 'Error requesting user');
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
