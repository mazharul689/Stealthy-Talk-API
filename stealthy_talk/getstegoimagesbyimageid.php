<?php
// Enable CORS (Cross-Origin Resource Sharing) headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

// Check if the image ID is provided in the query string
if (isset($_GET['image_id'])) {
    // Get the image ID from the query string
    $imageId = $_GET['image_id'];

    // Database connection details
    $dbHost = 'localhost';  // Change this to your database host
    $dbUsername = 'root';   // Change this to your database username
    $dbPassword = '';       // Change this to your database password
    $dbName = 'stealthy_talk';// Change this to your database name
    $dbPort = 3306;         // Change this to your database port (if different from default)

    // Create a new database connection
    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare a SELECT query to fetch the stego image data by image ID
    $sql = "SELECT * FROM stego_images WHERE image_id = ?";
    $stmt = $conn->prepare($sql);

    // Bind the image ID parameter
    $stmt->bind_param("i", $imageId);

    // Execute the query
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if a row was found
    if ($result->num_rows > 0) {
        // Fetch the row as an associative array
        $row = $result->fetch_assoc();

        // Return the row data as JSON
        echo json_encode($row);
    } else {
        // No row found with the provided image ID
        echo json_encode(array('error' => 'Image not found'));
    }

    // Close database connection
    $conn->close();
} else {
    // Image ID not provided in the query string
    echo json_encode(array('error' => 'Image ID not provided'));
}
?>
