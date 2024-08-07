<?php
require_once '../config/database.php';
require_once '../utils/functions.php';

class Appointments {
    private $conn;
    private $table_name = "appointments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, service_id, appointment_date) VALUES (:user_id, :service_id, :appointment_date)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':service_id', $data['service_id']);
        $stmt->bindParam(':appointment_date', $data['appointment_date']);

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
        $query = "UPDATE " . $this->table_name . " SET user_id = :user_id, service_id = :service_id, appointment_date = :appointment_date WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':service_id', $data['service_id']);
        $stmt->bindParam(':appointment_date', $data['appointment_date']);
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

$appointments = new Appointments($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => validateInput($_POST['user_id']),
        'service_id' => validateInput($_POST['service_id']),
        'appointment_date' => validateInput($_POST['appointment_date'])
    ];

    if ($appointments->create($data)) {
        jsonResponse(['message' => 'Appointment created successfully.']);
    } else {
        jsonResponse(['message' => 'Appointment creation failed.'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $appointments->read();
    $appointments_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse($appointments_arr);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $id = validateInput($_PUT['id']);
    $data = [
        'user_id' => validateInput($_PUT['user_id']),
        'service_id' => validateInput($_PUT['service_id']),
        'appointment_date' => validateInput($_PUT['appointment_date'])
    ];

    if ($appointments->update($id, $data)) {
        jsonResponse(['message' => 'Appointment updated successfully.']);
    } else {
        jsonResponse(['message' => 'Appointment update failed.'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = validateInput($_DELETE['id']);

    if ($appointments->delete($id)) {
        jsonResponse(['message' => 'Appointment deleted successfully.']);
    } else {
        jsonResponse(['message' => 'Appointment deletion failed.'], 400);
    }
}
