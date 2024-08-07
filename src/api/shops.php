<?php
require_once '../config/database.php';
require_once '../utils/functions.php';

class Shops {
    private $conn;
    private $table_name = "shops";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (name, address, phone, email) VALUES (:name, :address, :phone, :email)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);

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
        $query = "UPDATE " . $this->table_name . " SET name = :name, address = :address, phone = :phone, email = :email WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);
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

$shops = new Shops($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => validateInput($_POST['name']),
        'address' => validateInput($_POST['address']),
        'phone' => validateInput($_POST['phone']),
        'email' => validateInput($_POST['email'])
    ];

    if ($shops->create($data)) {
        jsonResponse(['message' => 'Shop created successfully.']);
    } else {
        jsonResponse(['message' => 'Shop creation failed.'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $shops->read();
    $shops_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($shops_arr);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $id = validateInput($_PUT['id']);
    $data = [
        'name' => validateInput($_PUT['name']),
        'address' => validateInput($_PUT['address']),
        'phone' => validateInput($_PUT['phone']),
        'email' => validateInput($_PUT['email'])
    ];

    if ($shops->update($id, $data)) {
        jsonResponse(['message' => 'Shop updated successfully.']);
    } else {
        jsonResponse(['message' => 'Shop update failed.'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = validateInput($_DELETE['id']);

    if ($shops->delete($id)) {
        jsonResponse(['message' => 'Shop deleted successfully.']);
    } else {
        jsonResponse(['message' => 'Shop deletion failed.'], 400);
    }
}
