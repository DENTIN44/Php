<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'Models/Database.php';  // Include the database connection
require_once 'Controllers/UserController.php';  // Include the UserController

try {
    // Instantiate the Database class with the correct path to the .env file
    $database = new Database(__DIR__ . '/../');  // Update to use the root directory
    $conn = $database->getConnection();  // Get the connection
    
    // Create the database if it doesn't exist
    // $database->createDatabase();

    // Create tables if they don't exist (ensure this method is defined in Database.php)
    $database->createTables();

    // Check the connection (you can enable this for debugging purposes)
    // var_dump($conn);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Create a new instance of the ServiceRegistration class
$serviceRegistration = new ServiceRegistration($conn);

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // Default photo if no upload occurs
    $photo = 'default.jpg';

    // Check if the photo field is set and no errors exist
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Allowed file extensions and max file size
        $allowed_extensions = ['png', 'jpg', 'jpeg'];
        $upload_dir = './uploads/'; // Ensure this directory exists
        $max_file_size = 5 * 1024 * 1024; // 5MB

        // Check if the upload directory exists, if not create it
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                die("Failed to create upload directory.");
            }
        }

        // Sanitize and validate the file name
        $file_name = basename($_FILES['photo']['name']); // Prevent directory traversal
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // Get file extension
        $file_size = $_FILES['photo']['size'];

        // Check for allowed file extensions
        if (!in_array($file_ext, $allowed_extensions)) {
            die("Invalid file type. Only PNG, JPG, and JPEG are allowed.");
        }

        // Check for file size limit
        if ($file_size > $max_file_size) {
            die("File is too large. Maximum size is 5MB.");
        }

        // Generate a unique name for the file to avoid collisions
        $new_name = uniqid('', true) . '.' . $file_ext; // More unique ID with additional entropy
        $file_path = $upload_dir . $new_name;

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
            $photo = $new_name; // Use the new filename for the database
        } else {
            echo "Failed to move the uploaded file.";
            exit;
        }
    }

    try {
        // Register the service
        $serviceRegistration->registerService($name, $description, $price, $photo);
    } catch (Exception $e) {
        // Display error message if something goes wrong
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Service</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            overflow-x: auto;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 480px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 0 auto; /* Add auto margin */
        }


        h2 {
            text-align: center;
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            border-color: #FF5722;
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group span {
            font-size: 14px;
            color: red;
        }

        .form-group button {
            background-color: #FF5722;
            color: white;
            border: none;
            padding: 12px 20px;
            width: 100%;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #E64A19;
        }

        .form-group a {
            text-align: center;
            color: #007BFF;
            margin-top: 20px;
            text-decoration: none;
        }

        .form-group a:hover {
            text-decoration: underline;
        }

        /* Flash Message Styles */
        .alert {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 600px;
            padding: 15px;
            border-radius: 5px;
            z-index: 1000;
            display: none;
            margin-top: 20px; /* Optional to add space between alert and top */
        }


        .alert-success {
            background-color: #4CAF50;
            color: white;
        }

        .alert-danger {
            background-color: #f44336;
            color: white;
        }

        .alert button {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 10px;
        }}

    </style>
</head>
<body>

            <div class="form-group">
                <a href="index.php">Back to Home</a>
            </div>

            <div class="container">
    <h2>Register Service</h2>
    <form method="post" action="register.php" enctype="multipart/form-data">
        <?php
        session_start();

        // CSRF Token Generation
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        function old($key) {
            return $_SESSION['old'][$key] ?? '';
        }

        function error($key) {
            return $_SESSION['errors'][$key] ?? null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF Token
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                die("Invalid CSRF token.");
            }

            // Default photo if no upload occurs
            $photo = 'default.jpg';

            // Check if the photo field is set and no errors exist
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                // Allowed file extensions and max file size
                $allowed_extensions = ['png', 'jpg', 'jpeg'];
                $upload_dir = './uploads/'; // Ensure this directory exists
                $max_file_size = 5 * 1024 * 1024; // 5MB

                // Check if the upload directory exists, if not create it
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true); // Create the directory with proper permissions
                }

                // Sanitize and validate the file name
                $file_name = basename($_FILES['photo']['name']); // Prevent directory traversal
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // Get file extension
                $file_size = $_FILES['photo']['size'];

                // Generate a unique name for the file to avoid collisions
                $new_name = uniqid('', true) . '.' . $file_ext; // More unique ID with additional entropy
                $file_path = $upload_dir . $new_name;

                // Check for allowed file extensions
                if (!in_array($file_ext, $allowed_extensions)) {
                    die("Invalid file type. Only PNG, JPG, and JPEG are allowed.");
                }

                // Check for file size limit
                if ($file_size > $max_file_size) {
                    die("File is too large. Maximum size is 5MB.");
                }

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
                    // Store the filename in the database
                    $stmt = $conn->prepare("INSERT INTO services (photo) VALUES (?)");
                    $stmt->bind_param("s", $new_name);
                
                    if ($stmt->execute()) {
                        echo "File uploaded successfully!";
                    } else {
                        echo "Database error: " . $stmt->error;
                    }
                
                    $stmt->close();
                } else {
                    echo "Failed to move the uploaded file.";
                }
            }

            // if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            //     $tmp_name = $_FILES['photo']['tmp_name'];
            //     $destination = './uploads/' . basename($_FILES['photo']['name']);
            
            //     if (move_uploaded_file($tmp_name, $destination)) {
            //         echo "File uploaded successfully!";
            //     } else {
            //         echo "Failed to move the file. Error: " . error_get_last()['message'];
            //     }
            // } else {
            //     echo "Upload error: " . $_FILES['photo']['error'];
            // }

            }

            ?>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="photo">Photo <span>*</span></label>
                <input type="file" name="photo" required accept="image/png, image/jpeg">
            </div>

            <div class="form-group">
                <label for="name">Service Name<span>*</span></label>
                <input type="text" name="name" required value="<?php echo old('name'); ?>">
                <?php if ($error = error('name')): ?>
                    <span><?php echo $error; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Description<span>*</span></label>
                <input type="text" name="description" required value="<?php echo old('description'); ?>">
                <?php if ($error = error('description')): ?>
                    <span><?php echo $error; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="price">Price<span>*</span></label>
                <input type="number" name="price" required value="<?php echo old('price'); ?>">
                <?php if ($error = error('price')): ?>
                    <span><?php echo $error; ?></span>
                <?php endif; ?>
            </div>
            

            
            <div class="form-group">
                <button type="submit">Add Service</button>
            </div>

        </form>
    </div>

    <?php
    unset($_SESSION['errors'], $_SESSION['old']);
    ?>

</body>
</html>


