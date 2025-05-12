
<?php
//services-list.php

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

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service List</title>
    <link rel="stylesheet" href="Assets/service-list.css">
</head>
<body>
    <!-- Displaying results -->
    <div id="services-list"></div>
    <div class="form-group">
        <a href="index.php">Back to Home</a>
    </div>
    <br>
    <!-- Search form for services -->
    <input type="text" id="search-input" placeholder="Search for a service..." />

    <h2>Latest Registered Services</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Photos</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Loop through users to display them -->
            <?php
            $serviceController = new ServiceController($conn);
            $services = $serviceController->index(); 
            
              // Loop through the $users array and display each user's data in a table row
        foreach ($services as $row) {
            echo "<tr>
                <td>" . htmlspecialchars($row['id'] ?? '') . "</td>
                <td>" . htmlspecialchars($row['name'] ?? '') . "</td>
                <td class='description-cell' onclick='showFullDescription(\"" . htmlspecialchars($row['description'] ?? '') . "\")'>" . htmlspecialchars($row['description'] ?? '') . "</td>
                <td>" . htmlspecialchars($row['price'] ?? '') . "</td>
                <td>" . htmlspecialchars($row['created_at'] ?? '') . "</td>
                <td>" . htmlspecialchars($row['updated_at'] ?? '') . "</td>
                <td>
                    <a href='./uploads/" . htmlspecialchars($row['photo'] ?? '') . "' target='_blank'>
                        " . htmlspecialchars($row['photo'] ?? '') . "
                    </a>
                </td>
                <td>
                    <!-- Form to delete the user -->
                    <form method='POST' action='delete.php' style='display:inline;'>
                        <input type='hidden' name='service_id' value='" . htmlspecialchars($row['id'] ?? '') . "'>
                        <button type='submit' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</button>
                    </form>
                    <!-- Form to edit the user -->
                    <form method='GET' action='perfil-update.php' style='display:inline;'>
                        <input type='hidden' name='service_id' value='" . htmlspecialchars($row['id'] ?? '') . "'>
                        <button type='submit'>Edit</button>
                    </form>
                </td>
            </tr>";
            }
            ?>
        </tbody>
    </table>
    
    <div id="overlay" onclick="closeModal()"></div>
    <div id="descriptionModal"></div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showFullDescription(description) {
            const modal = document.getElementById('descriptionModal');
            const overlay = document.getElementById('overlay');
            
            modal.textContent = description;
            modal.style.display = 'block';
            overlay.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('descriptionModal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        $(document).ready(function() {
            $('#search-input').on('keyup', function() {
                var searchTerm = $(this).val();

                // Make AJAX request to the server with the search term
                $.ajax({
                    url: 'fetch-services.php', // This will return the filtered services as table rows
                    method: 'GET',
                    data: { search: searchTerm },
                    success: function(response) {
                        // Overwrite the existing table body with the new response
                        $('table tbody').html(response);
                    }
                });
            });
        });
    </script>
</body>
</html>
