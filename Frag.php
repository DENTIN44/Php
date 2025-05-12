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
                $new_name = uniqid() . '.' . $file_ext; // More unique ID with additional entropy
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