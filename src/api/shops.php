<?php
//AUTHOR-MADE BY TUĞBA YALVAÇ MOHAMMED
require_once '../config/database.php';
require_once '../utils/functions.php';

class Shops {
    private $conn;
    private $table_name = "shops";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (name, location) VALUES (:name, :location)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':location', $data['location']);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Shop creation failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET name = :name, location = :location WHERE shopID = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Shop update failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE shopID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => validateInput($_POST['name']),
        'location' => validateInput($_POST['location'])
    ];

    if ($shops->create($data)) {
        echo json_encode(["message" => "Shop created successfully!"]);
    } else {
        echo json_encode(["message" => "Shop creation failed!"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($shops->read());
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $id = validateInput($_PUT['shopID']);
    $data = [
        'name' => validateInput($_PUT['name']),
        'location' => validateInput($_PUT['location'])
    ];

    if ($shops->update($id, $data)) {
        echo json_encode(["message" => "Shop updated successfully!"]);
    } else {
        echo json_encode(["message" => "Shop update failed!"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = validateInput($_DELETE['shopID']);

    if ($shops->delete($id)) {
        echo json_encode(["message" => "Shop deleted successfully!"]);
    } else {
        echo json_encode(["message" => "Shop deletion failed!"]);
    }
}
