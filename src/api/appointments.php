<?php
//AUTHOR-MADE BY TUĞBA YALVAÇ MOHAMMED

//Include the database configuration file and utility functions
require_once '../config/database.php';
require_once '../utils/functions.php';

//Define the Appointments class
class Appointments {
    //Declare private variables to store database connection and table name
    private $conn;
    private $table_name = "Appointments";

    //Constructor method to initialize the database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    //Method to create a new appointment record
    public function create($CustomerID, $VehicleID, $Date, $Time, $ServiceID) {
        $query = "INSERT INTO " . $this->table_name . " (CustomerID, VehicleID, Date, Time, ServiceID) VALUES (:CustomerID, :VehicleID, :Date, :Time, :ServiceID)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':CustomerID', $CustomerID);
        $stmt->bindParam(':VehicleID', $VehicleID);
        $stmt->bindParam(':Date', $Date);
        $stmt->bindParam(':Time', $Time);
        $stmt->bindParam(':ServiceID', $ServiceID);

        //Execute the query and check if the operation was successful
        if ($stmt->execute()) {
            return true;
        } else {
            //If the operation failed, return an error message with details
            echo json_encode(["message" => "Appointment creation failed!", "error" => $stmt->errorInfo()]);
            return false;
        }
    }

    //Method to retrieve all appointment records from the database
    public function getAll() {
        //SQL query to select all records from the Appointments table
        $query = "SELECT * FROM " . $this->table_name;

        //Prepare the query for execution
        $stmt = $this->conn->prepare($query);

        //Execute the query
        $stmt->execute();

        //Fetch all results as an associative array and return them
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Instantiate Database and Appointments classes
$database = new Database();
$db = $database->getConnection();
//Instantiate the Appointments class with the database connection
$appointments = new Appointments($db);

//Set the content type of the response to JSON
header('Content-Type: application/json');
// Get the incoming request data and decode it from JSON format
$request = json_decode(file_get_contents("php://input"));


// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the 'action' parameter from the query string, if present
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    // If the action is 'create', proceed to create a new appointment
    if ($action === 'create') {
        // Get the data from the request and assign default empty values if not set
        $CustomerID = isset($request->CustomerID) ? $request->CustomerID : '';
        $VehicleID = isset($request->VehicleID) ? $request->VehicleID : '';
        $Date = isset($request->Date) ? $request->Date : '';
        $Time = isset($request->Time) ? $request->Time : '';
        $ServiceID = isset($request->ServiceID) ? $request->ServiceID : '';
        // Attempt to create the appointment and return a success message if successful
        if ($appointments->create($CustomerID, $VehicleID, $Date, $Time, $ServiceID)) {
            echo json_encode(["message" => "Appointment created successfully!"]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // If the request method is GET, retrieve and return all appointments
    echo json_encode($appointments->getAll());
} else {
    // If the request method is neither POST nor GET, return an error message
    echo json_encode(["message" => "Invalid request method!"]);
}
