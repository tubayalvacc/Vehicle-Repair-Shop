<?php
require_once '../config/database.php';
require_once '../utils/functions.php';

class Services {
    private $conn;
    private $table_name = "services";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (name, description, price) VALUES (:name, :description, :price)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);

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
        $query = "UPDATE " . $this->table_name . " SET name = :name, description = :description, price = :price WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
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

$services = new Services($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => validateInput($_POST['name']),
        'description' => validateInput($_POST['description']),
        'price' => validateInput($_POST['price'])
    ];

    if ($services->create($data)) {
        jsonResponse(['message' => 'Service created successfully.']);
    } else {
        jsonResponse(['message' => 'Service creation failed.'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $services->read();
    $services_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($services_arr);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $id = validateInput($_PUT['id']);
    $data = [
        'name' => validateInput($_PUT['name']),
        'description' => validateInput($_PUT['description']),
        'price' => validateInput($_PUT['price'])
    ];

    if ($services->update($id, $data)) {
        jsonResponse(['message' => 'Service updated successfully.']);
    } else {
        jsonResponse(['message' => 'Service update failed.'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = validateInput($_DELETE['id']);

    if ($services->delete($id)) {
        jsonResponse(['message' => 'Service deleted successfully.']);
    } else {
        jsonResponse(['message' => 'Service deletion failed.'], 400);
    }
}
