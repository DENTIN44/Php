<?php

require_once 'Models/Database.php';  // Include the database connection
require_once 'Controllers/UserController.php';  // Include the UserController



// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
    $serviceId = intval($_POST['service_id']); // Convert user ID to an integer to prevent SQL injection

    try {

        // Instantiate the Database class with the correct path to the .env file
      $database = new Database(__DIR__ . '/../');  // Update to use the root directory
      $conn = $database->getConnection();  // Get the connection

        // Create an instance of the UserManager class
        $serviceManager = new ServiceHandler($conn);

        // Call the deleteUser method
        $serviceManager->deleteService($serviceId);
    } catch (Exception $e) {
        // Handle exceptions (e.g., log errors, display user-friendly messages)
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
