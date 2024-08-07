<?php
//AUTHOR-MADE BY TUĞBA YALVAÇ MOHAMMED
require_once '../config/database.php';
require_once '../utils/functions.php';

class Vehicles {
    private $conn;
    private $table_name = "vehicles";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (Make, Model, Year, CustomerID) VALUES (:make, :model, :year, :customerID)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':make', $data['make']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':customerID', $data['customerID']);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Vehicle creation failed!", "error" => $stmt->errorInfo()]);
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
        $query = "UPDATE " . $this->table_name . " SET Make = :make, Model = :model, Year = :year, CustomerID = :customerID WHERE VehicleID = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':make', $data['make']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':customerID', $data['customerID']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Vehicle update failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE VehicleID = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Vehicle deletion failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }
}

// Instantiate Database and Vehicles classes
$database = new Database();
$db = $database->getConnection();
$vehicles = new Vehicles($db);

// Handle request
header('Content-Type: application/json');
$request = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $data = [
        'make' => validateInput($input['make']),
        'model' => validateInput($input['model']),
        'year' => intval(validateInput($input['year'])),
        'customerID' => intval(validateInput($input['customerID']))
    ];

    if ($vehicles->create($data)) {
        echo json_encode(["message" => "Vehicle created successfully!"]);
    } else {
        echo json_encode(["message" => "Vehicle creation failed!"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($vehicles->read());
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $id = validateInput($_PUT['VehicleID']);
    $data = [
        'make' => validateInput($_PUT['make']),
        'model' => validateInput($_PUT['model']),
        'year' => intval(validateInput($_PUT['year'])),
        'customerID' => intval(validateInput($_PUT['customerID']))
    ];

    if ($vehicles->update($id, $data)) {
        echo json_encode(["message" => "Vehicle updated successfully!"]);
    } else {
        echo json_encode(["message" => "Vehicle update failed!"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = validateInput($_DELETE['VehicleID']);

    if ($vehicles->delete($id)) {
        echo json_encode(["message" => "Vehicle deleted successfully!"]);
    } else {
        echo json_encode(["message" => "Vehicle deletion failed!"]);
    }
}
