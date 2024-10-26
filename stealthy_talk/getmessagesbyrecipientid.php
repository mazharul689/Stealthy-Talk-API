<?php
// Enable CORS (Cross-Origin Resource Sharing) headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, OPTIONS"); 

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
    $dbName = 'stealthy_talk';  // Change this to your database name
    $dbPort = 3306;  // Change this to your database port (if different from default)

    // Create a new database connection
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if recipient_id is provided
    if (isset($_GET['recipient_id'])) {
        $recipient_id = intval($_GET['recipient_id']); // Get the recipient_id from the query string

        // SQL query to get messages for the specific recipient_id along with admin name and subject from stego_images
        $sql = "
            SELECT 
                messages.message_id,
                messages.admin_id,
                CONCAT(users.firstName, ' ', users.lastName) AS admin_name,
                messages.recipient_id,
                messages.stego_image_id,
                stego_images.subject
            FROM 
                messages
            JOIN 
                users ON messages.admin_id = users.user_id
            JOIN 
                stego_images ON messages.stego_image_id = stego_images.image_id
            WHERE 
                messages.recipient_id = ?
        ";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $recipient_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $messages = array();

            // Fetch all rows and store them in the array
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }

            // Send response as JSON
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'success', 'data' => $messages));
        } else {
            // No records found
            $response = array('status' => 'error', 'message' => 'No messages found for the given recipient_id');
            header('Content-Type: application/json');
            echo json_encode($response);
        }

        // Close the statement
        $stmt->close();
    } else {
        // recipient_id not provided
        $response = array('status' => 'error', 'message' => 'recipient_id not provided');
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    // Close database connection
    $conn->close();
} else {
    // Invalid request method
    header("HTTP/1.1 405 Method Not Allowed");
}
?>
