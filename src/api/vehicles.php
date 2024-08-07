<?php
require_once '../config/database.php';
require_once '../utils/functions.php';

class Vehicles {
    private $conn;
    private $table_name = "vehicles";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, make, model, year, vin) VALUES (:user_id, :make, :model, :year, :vin)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':make', $data['make']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':vin', $data['vin']);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET user_id = :user_id, make = :make, model = :model, year = :year, vin = :vin WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':make', $data['make']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':vin', $data['vin']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}

// Usage
$database = new Database();
$db = $database->getConnection();

$vehicles = new Vehicles($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => validateInput($_POST['user_id']),
        'make' => validateInput($_POST['make']),
        'model' => validateInput($_POST['model']),
        'year' => validateInput($_POST['year']),
        'vin' => validateInput($_POST['vin'])
    ];

    if ($vehicles->create($data)) {
        jsonResponse(['message' => 'Vehicle created successfully.']);
    } else {
        jsonResponse(['message' => 'Vehicle creation failed.'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $vehicles->read();
    $vehicles_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($vehicles_arr);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $id = validateInput($_PUT['id']);
    $data = [
        'user_id' => validateInput($_PUT['user_id']),
        'make' => validateInput($_PUT['make']),
        'model' => validateInput($_PUT['model']),
        'year' => validateInput($_PUT['year']),
        'vin' => validateInput($_PUT['vin'])
    ];

    if ($vehicles->update($id, $data)) {
        jsonResponse(['message' => 'Vehicle updated successfully.']);
    } else {
        jsonResponse(['message' => 'Vehicle update failed.'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = validateInput($_DELETE['id']);

    if ($vehicles->delete($id)) {
        jsonResponse(['message' => 'Vehicle deleted successfully.']);
    } else {
        jsonResponse(['message' => 'Vehicle deletion failed.'], 400);
    }
}
