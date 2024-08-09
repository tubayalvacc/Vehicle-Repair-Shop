<?php
//AUTHOR-MADE BY TUĞBA YALVAÇ MOHAMMED
require_once '../config/database.php';
require_once '../utils/functions.php';

class Shops {
    // Declare private variables to store database connection and table name
    private $conn;
    private $table_name = "shops";

    // Constructor method to initialize the database connection
    public function __construct($db) {
        $this->conn = $db;
    }


    // Method to create a new shop
    public function create($data) {
        // SQL query to insert a new shop into the 'shops' table
        $query = "INSERT INTO " . $this->table_name . " (name, location) VALUES (:name, :location)";
        $stmt = $this->conn->prepare($query);


        // Bind parameters to the prepared statement
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':location', $data['location']);

        // Execute the query and return true if successful, otherwise return false with an error message
        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Shop creation failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }

    // Method to read all shops
    public function read() {
        // SQL query to select all records from the 'shops' table
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        // Fetch all results as an associative array and return them
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Method to update an existing shop
    public function update($id, $data) {
        // SQL query to update a specific shop record
        $query = "UPDATE " . $this->table_name . " SET name = :name, location = :location WHERE shopID = :id";
        $stmt = $this->conn->prepare($query);

        // Bind parameters to the prepared statement
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':id', $id);

        // Execute the query and return true if successful, otherwise return false with an error message
        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Shop update failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }

    // Method to delete a shop
    public function delete($id) {
        // SQL query to delete a specific shop record
        $query = "DELETE FROM " . $this->table_name . " WHERE shopID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        // Execute the query and return true if successful, otherwise return false with an error message
        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Shop deletion failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }
}

// Instantiate Database and Shops classes
$database = new Database();
$db = $database->getConnection();
$shops = new Shops($db);

// Handle request
header('Content-Type: application/json');
$request = json_decode(file_get_contents("php://input"));

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve shop details from the request and validate input
    $data = [
        'name' => validateInput($_POST['name']),
        'location' => validateInput($_POST['location'])
    ];

    // Attempt to create the shop and return a success or failure message
    if ($shops->create($data)) {
        echo json_encode(["message" => "Shop created successfully!"]);
    } else {
        echo json_encode(["message" => "Shop creation failed!"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // If the request method is GET, retrieve and return all shops
    echo json_encode($shops->read());
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Parse the PUT request data
    parse_str(file_get_contents("php://input"), $_PUT);
    $id = validateInput($_PUT['shopID']);
    $data = [
        'name' => validateInput($_PUT['name']),
        'location' => validateInput($_PUT['location'])
    ];

    // Attempt to update the shop and return a success or failure message
    if ($shops->update($id, $data)) {
        echo json_encode(["message" => "Shop updated successfully!"]);
    } else {
        echo json_encode(["message" => "Shop update failed!"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Parse the DELETE request data
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = validateInput($_DELETE['shopID']);

    // Attempt to delete the shop and return a success or failure message
    if ($shops->delete($id)) {
        echo json_encode(["message" => "Shop deleted successfully!"]);
    } else {
        echo json_encode(["message" => "Shop deletion failed!"]);
    }
}
