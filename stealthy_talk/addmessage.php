<?php
// Enable CORS (Cross-Origin Resource Sharing) headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");

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

    // Check if the necessary data is provided
    if (isset($input['admin_id']) && isset($input['stego_image_id']) && isset($input['Rows'])) {
        $admin_id = $input['admin_id'];
        $stego_image_id = $input['stego_image_id'];
        $rows = $input['Rows'];
        $duplicates = [];

        // Prepare SQL statements
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE admin_id = ? AND recipient_id = ? AND stego_image_id = ?");
        $insert_stmt = $conn->prepare("INSERT INTO messages (admin_id, recipient_id, stego_image_id) VALUES (?, ?, ?)");

        // Loop through each row and check for duplicates before inserting
        foreach ($rows as $row) {
            $recipient_id = $row['recipient_id'];

            // Check if the record already exists
            $check_stmt->bind_param("iii", $admin_id, $recipient_id, $stego_image_id);
            $check_stmt->execute();
            $check_stmt->store_result(); // Store the result to avoid "Commands out of sync" error
            $check_stmt->bind_result($count);
            $check_stmt->fetch();

            if ($count == 0) {
                // If no duplicate found, insert the record
                $insert_stmt->bind_param("iii", $admin_id, $recipient_id, $stego_image_id);
                if (!$insert_stmt->execute()) {
                    // If there is an error during execution
                    $response = array('status' => 'error', 'message' => 'Error inserting data');
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                }
            } else {
                // Record is a duplicate
                $duplicates[] = $recipient_id;
            }
        }

        // Prepare the response message
        if (empty($duplicates)) {
            $response = array('status' => 'success', 'message' => 'Messages inserted successfully');
        } else {
            $response = array('status' => 'partial_success', 'message' => 'Some records were duplicates and not inserted', 'duplicates' => $duplicates);
        }

    } else {
        // If the necessary data is not provided
        $response = array('status' => 'error', 'message' => 'Required data not provided');
    }

    // Close the statements and the connection
    $check_stmt->close();
    $insert_stmt->close();
    $conn->close();

    // Send response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.1 405 Method Not Allowed");
}
?>
