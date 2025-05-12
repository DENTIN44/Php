<?php

require_once 'Models/Database.php';  // Include the database connection
require_once 'Controllers/UserController.php';  // Include the UserController

try {

      // Instantiate the Database class with the correct path to the .env file
      $database = new Database(__DIR__ . '/../');  // Update to use the root directory
      $conn = $database->getConnection();  // Get the connection
      

    // Ensure the database connection is established
    if (!isset($conn) || !$conn instanceof mysqli) {
        throw new Exception("Invalid or missing database connection.");
    }

    // Instantiate the User class
    $serviceHandler = new ServiceHandler($conn);

    $idReceived = 0;

   // Fetch service details if service_id is provided (via GET or POST)
if (isset($_GET['service_id'])) {
    // Load the form: service_id is in the query string
    $idReceived = intval($_GET['service_id']);
} elseif (isset($_POST['service_id'])) {
    // Form submission: service_id is in the POST data
    $idReceived = intval($_POST['service_id']);
} else {
    // No service_id provided
    echo "Service ID not provided.";
    exit;
}

// Fetch the service details
$service = $serviceHandler->fetchServices($idReceived);

if ($service) {
    $name = $service['name'];
    $description = $service['description'];
    $price = $service['price'];
} else {
    echo "Service not found.";
    exit;
}

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
        $serviceId = intval($_POST['service_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);

        // Validate inputs
        if (empty($name) || empty($description) || $price <= 0) {
            throw new Exception("All fields are required, and price must be greater than 0.");
        }

        // Call the updateService method with all 4 arguments
        if ($serviceHandler->updateService($serviceId, $name, $description, $price)) {
            header('Location: services-list.php'); // Redirect to the services list
            exit;
        }
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}
?>



    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            overflow-x: auto;
            padding: 20px;
        }


        h2 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 20px;
            color: #333;
        }

        .container {
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-width: 50%;
            margin: 50px auto;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            background-color: #FF5722;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            border-radius: 4px;
        }

        button:hover {
            background-color: #E64A19;
        }

        a {
            text-align: left;
            margin-top: 20px;
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
        
    </style>    

<div class="form-group">
<a href="index.php">Back to Home</a>
</div>

    <div class="container">
        <h2>Edit Service</h2>
        <form action="perfil-update.php" method="POST">
            <input type='hidden' name='service_id' value='<?php echo htmlspecialchars($idReceived); ?>'>

            <label for='name'>Name:</label>
            <input type='text' name='name' value='<?php echo htmlspecialchars($name); ?>' required>

            <label for='description'>Description:</label>
            <textarea name='description' required><?php echo htmlspecialchars($description); ?></textarea>

            <label for='price'>Price:</label>
            <input type='number' step="0.01" name='price' value='<?php echo htmlspecialchars($price); ?>' required>

            <button type='submit' name='update_service'>Update Service</button>
        </form>

    </div>


