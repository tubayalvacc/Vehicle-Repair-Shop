<?php
//AUTHOR-MADE BY TUĞBA YALVAÇ MOHAMMED
require_once '../config/database.php';
require_once '../utils/functions.php';

class Services {
    private $conn;
    private $table_name = "services";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (ServiceName, Description, Cost) VALUES (:ServiceName, :Description, :Cost)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':ServiceName', $data['ServiceName']);
        $stmt->bindParam(':Description', $data['Description']);
        $stmt->bindParam(':Cost', $data['Cost']);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Service creation failed!", "error" => $stmt->errorInfo()]);
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
        $query = "UPDATE " . $this->table_name . " SET ServiceName = :ServiceName, Description = :Description, Cost = :Cost WHERE ServiceID = :ServiceID";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':ServiceName', $data['ServiceName']);
        $stmt->bindParam(':Description', $data['Description']);
        $stmt->bindParam(':Cost', $data['Cost']);
        $stmt->bindParam(':ServiceID', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Service update failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE ServiceID = :ServiceID";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ServiceID', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Service deletion failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }
}

// Instantiate Database and Services classes
$database = new Database();
$db = $database->getConnection();
$services = new Services($db);

// Handle request
header('Content-Type: application/json');
$request = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'ServiceName' => validateInput($_POST['ServiceName']),
        'Description' => validateInput($_POST['Description']),
        'Cost' => validateInput($_POST['Cost'])
    ];

    // Convert cost to a float to ensure it's a valid decimal number
    $data['Cost'] = floatval($data['Cost']);

    if ($services->create($data)) {
        echo json_encode(["message" => "Service created successfully!"]);
    } else {
        echo json_encode(["message" => "Service creation failed!"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($services->read());
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $id = validateInput($_PUT['ServiceID']);
    $data = [
        'ServiceName' => validateInput($_PUT['ServiceName']),
        'Description' => validateInput($_PUT['Description']),
        'Cost' => validateInput($_PUT['Cost'])
    ];

    // Convert cost to a float to ensure it's a valid decimal number
    $data['Cost'] = floatval($data['Cost']);

    if ($services->update($id, $data)) {
        echo json_encode(["message" => "Service updated successfully!"]);
    } else {
        echo json_encode(["message" => "Service update failed!"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = validateInput($_DELETE['ServiceID']);

    if ($services->delete($id)) {
        echo json_encode(["message" => "Service deleted successfully!"]);
    } else {
        echo json_encode(["message" => "Service deletion failed!"]);
    }
}
