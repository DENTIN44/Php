<?php
require_once 'Database.php';

class ServiceRegistration {
    private $conn; // Private property to hold the database connection

    // Constructor to initialize the database connection
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function registerService($name, $description, $price, $photo) {
        // Validate input
        if (empty($name) || empty($description) || empty($price) || empty($photo)) {
            throw new Exception("All fields are required.");
        }
    
        if (!is_numeric($price) || $price <= 0) {
            throw new Exception("Price must be a positive number.");
        }
    
        // Sanitize inputs to prevent SQL injection
        $name = $this->conn->real_escape_string($name);
        $description = $this->conn->real_escape_string($description);
        $price = (float)$price; // Ensure price is a float
        $photo = $this->conn->real_escape_string($photo);
    
        // Prepare the SQL statement
        $stmt = $this->conn->prepare("INSERT INTO services (name, description, price, photo) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
    
        // Bind parameters
        $stmt->bind_param("ssds", $name, $description, $price, $photo);
    
        // Execute the statement and check for success
        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $stmt->error);
        }
    
        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            echo "Service registered successfully.";
        } else {
            echo "No rows affected. Check the query or data.";
        }
    
        // Close the statement
        $stmt->close();
    
        // Redirect after successful registration
        header("Location: register.php");
        exit; // Ensure the script stops after redirect
    }
}

class ServiceHandler {
    private $conn; // Private property to hold the database connection

    // Constructor to initialize the database connection
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to fetch all services or a single service by ID or search term
    public function fetchServices($serviceId = null, $search = null) {
        if ($this->conn === null) {
            die('Database connection not established.');
        }

        if ($serviceId === null && $search === null) {
            // SQL query to select all services ordered by creation date in descending order
            $sql = "SELECT * FROM services ORDER BY createdAt DESC";
            $result = $this->conn->query($sql);

            // Initialize an empty array to store the services
            $services = [];

            // Check if there was an error with the query
            if ($result === false) {
                echo "SQL Error: " . $this->conn->error;
                return $services; // Return an empty array on error
            }

            // Loop through each row and add it to the services array
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }

            return $services; // Return the array of services
        } elseif ($serviceId !== null) {
            // SQL query to select a service by ID
            $sql = "SELECT * FROM services WHERE id = ?";
            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("i", $serviceId);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    return $result->fetch_assoc(); // Return service data if found
                } else {
                    return null; // Return null if no service found
                }
            } else {
                throw new Exception("Error preparing the statement: " . $this->conn->error);
            }
        } else {
            // SQL query to select services by name or first letter
            $sql = "SELECT * FROM services WHERE name LIKE ?";
            if ($search) {
                // If search is a single letter, use LIKE with the first letter
                if (strlen($search) == 1) {
                    $search = $search . '%'; // Filter by the first letter of the service name
                } else {
                    $search = '%' . $search . '%'; // Filter by service name containing the search term
                }
            } else {
                $search = '%'; // No search, return all services
            }

            if ($stmt = $this->conn->prepare($sql)) {
                $stmt->bind_param("s", $search);
                $stmt->execute();
                $result = $stmt->get_result();

                // Initialize an empty array to store the services
                $services = [];

                // Loop through each row and add it to the services array
                while ($row = $result->fetch_assoc()) {
                    $services[] = $row;
                }

                return $services; // Return the array of filtered services
            } else {
                throw new Exception("Error preparing the statement: " . $this->conn->error);
            }
        }
    }


    public function searchServices($searchTerm) {
        try {
            // Return an empty list if the search term is empty
            if (empty(trim($searchTerm))) {
                return [];
            }
    
            // Determine the search pattern based on the length of the search term
            if (strlen($searchTerm) == 1) {
                // If the search term is a single letter, search for services starting with that letter
                $searchPattern = $searchTerm . '%';
            } else {
                // If the search term is more than one character, search for services containing the term
                $searchPattern = '%' . $searchTerm . '%';
            }
    
            // SQL query to select services by name or description
            $sql = "SELECT * FROM services WHERE name LIKE ? OR description LIKE ?";
            $stmt = $this->conn->prepare($sql);
    
            // Bind parameters and execute
            $stmt->bind_param('ss', $searchPattern, $searchPattern);
            $stmt->execute();
    
            // Fetch results
            $result = $stmt->get_result();
            $services = $result->fetch_all(MYSQLI_ASSOC);
    
            // Free result and return services
            $stmt->close();
            return $services;
        } catch (Exception $e) {
            echo "Error fetching services: " . $e->getMessage();
            return [];
        }
    }
    
    // Update service by ID
    public function updateService($serviceId, $name, $description, $price) {
        // Validate input
        if (empty($name) || empty($description) || empty($price)) {
            throw new Exception("All fields are required.");
        }

        if (!is_numeric($price) || $price <= 0) {
            throw new Exception("Price must be a positive number.");
        }

        // Prepare the SQL statement
        $sql = "UPDATE services SET name = ?, description = ?, price = ? WHERE id = ?";
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param("ssdi", $name, $description, $price, $serviceId);
            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception("Error executing the query: " . $stmt->error);
            }
        } else {
            throw new Exception("Error preparing the statement: " . $this->conn->error);
        }
    }

    // Method to delete a service by ID
    public function deleteService($serviceId) {
        if ($this->conn === null) {
            throw new Exception("Database connection not established.");
        }

        // Prepare the SQL DELETE statement
        $sql = "DELETE FROM services WHERE id = ?";

        // Prepare the statement
        if ($stmt = $this->conn->prepare($sql)) {
            // Bind the service ID parameter
            $stmt->bind_param("i", $serviceId);

            // Execute the statement
            if ($stmt->execute()) {
                // Check if any rows were affected
                if ($stmt->affected_rows > 0) {
                    echo "Service with ID $serviceId has been deleted.";
                    header('Location: services-list.php'); // Redirect after successful deletion
                    exit;
                } else {
                    echo "No service found with ID $serviceId.";
                }
            } else {
                throw new Exception("Error executing the query: " . $stmt->error);
            }

            // Close the statement
            $stmt->close();
        } else {
            throw new Exception("Error preparing the statement: " . $this->conn->error);
        }
    }
}

class PhotoModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllPhotos() {
        $sql = "SELECT id, photo FROM your_table";
        $result = $this->conn->query($sql);

        $photos = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $photos[] = $row;
            }
        }
        return $photos;
    }
}

// Close the database connection
// $conn->close();

?>