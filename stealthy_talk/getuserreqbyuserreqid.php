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

// Check if user_id parameter is provided
if(isset($_GET['id'])) {
    $userReqId = $_GET['id'];

    // Fetch specific user data with role name
    // $sql = "SELECT u.*, r.role_name
    //         FROM users u
    //         INNER JOIN roles r ON u.role_id = r.role_id
    //         WHERE u.user_id = ?";
    $sql = "SELECT * FROM user_request u WHERE u.userReqId = ?";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userReqId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();
        
        // Remove sensitive information from the response
        // unset($user['password']);
        
        // Send response as JSON
        header('Content-Type: application/json');
        echo json_encode($user);
    } else {
        // No user found
        $response = array('status' => 'error', 'message' => 'User not found');
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    // Close the statement
    $stmt->close();
} else {
    // Missing user_id parameter
    $response = array('status' => 'error', 'message' => 'User ID parameter is missing');
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Close database connection
$conn->close();
?>
