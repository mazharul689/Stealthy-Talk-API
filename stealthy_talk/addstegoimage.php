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
    // Check if file data is provided
    if (isset($_FILES['image'])) {
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

        // Retrieve image details
        $subject = $_POST['subject'] ?? '';
        $user_id = $_POST['user_id'] ?? '';

        // Handle file upload
        $uploadDir = 'stego_uploads/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        // Check if file is a valid image
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($fileType, $allowedTypes)) {
            // Upload file to server
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                // Insert image path into database
                $insertQuery = "INSERT INTO stego_images (user_id, subject, imagePath) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insertQuery);
                $stmt->bind_param("iss", $user_id, $subject, $targetFilePath);
                if ($stmt->execute()) {
                    // Get the ID of the inserted image
                    $image_id = $stmt->insert_id;
                    $response = array('status' => 'success', 'message' => 'Image uploaded successfully', 'image_id' => $image_id);
                } else {
                    $response = array('status' => 'error', 'message' => 'Error uploading image');
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Error moving uploaded file');
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Invalid file type');
        }

        // Close database connection
        $conn->close();
    } else {
        $response = array('status' => 'error', 'message' => 'Image file not provided');
    }

    // Send response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.1 405 Method Not Allowed");
}
?>
