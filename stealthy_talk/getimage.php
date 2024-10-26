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
    // Check if image name is provided as a parameter
    if (isset($_GET['imagename'])) {
        $imageName = $_GET['imagename'];

        // Define the path to the image directory
        $imageDir = 'stego_uploads/';

        // Get the full path to the requested image
        $imagePath = $imageDir . $imageName;

        // Check if the image file exists
        if (file_exists($imagePath)) {
            // Get the MIME type of the image
            $imageType = mime_content_type($imagePath);

            // Set the appropriate Content-Type header
            header('Content-Type: ' . $imageType);

            // Output the image content
            readfile($imagePath);
            exit();
        } else {
            // Image not found
            header("HTTP/1.1 404 Not Found");
            exit();
        }
    } else {
        // Image name not provided
        $response = array('status' => 'error', 'message' => 'Image name not provided');
        header('Content-Type: application/json');
        echo json_encode($response);
    }
} else {
    // Invalid request method
    header("HTTP/1.1 405 Method Not Allowed");
}
?>
