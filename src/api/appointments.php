<?php
//AUTHOR-MADE BY TUĞBA YALVAÇ MOHAMMED
require_once '../config/database.php';
require_once '../utils/functions.php';

class Appointments {
    private $conn;
    private $table_name = "Appointments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($CustomerID, $VehicleID, $Date, $Time, $ServiceID) {
        $query = "INSERT INTO " . $this->table_name . " (CustomerID, VehicleID, Date, Time, ServiceID) VALUES (:CustomerID, :VehicleID, :Date, :Time, :ServiceID)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':CustomerID', $CustomerID);
        $stmt->bindParam(':VehicleID', $VehicleID);
        $stmt->bindParam(':Date', $Date);
        $stmt->bindParam(':Time', $Time);
        $stmt->bindParam(':ServiceID', $ServiceID);

        if ($stmt->execute()) {
            return true;
        } else {
            echo json_encode(["message" => "Appointment creation failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Instantiate Database and Appointments classes
$database = new Database();
$db = $database->getConnection();
$appointments = new Appointments($db);

// Handle request
header('Content-Type: application/json');
$request = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    if ($action === 'create') {
        $CustomerID = isset($request->CustomerID) ? $request->CustomerID : '';
        $VehicleID = isset($request->VehicleID) ? $request->VehicleID : '';
        $Date = isset($request->Date) ? $request->Date : '';
        $Time = isset($request->Time) ? $request->Time : '';
        $ServiceID = isset($request->ServiceID) ? $request->ServiceID : '';

        if ($appointments->create($CustomerID, $VehicleID, $Date, $Time, $ServiceID)) {
            echo json_encode(["message" => "Appointment created successfully!"]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($appointments->getAll());
} else {
    echo json_encode(["message" => "Invalid request method!"]);
}
