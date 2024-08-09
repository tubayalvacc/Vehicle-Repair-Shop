<?php
//AUTHOR-MADE BY TUĞBA YALVAÇ MOHAMMED

// Include the database configuration file and utility functions
require_once '../config/database.php';
require_once '../utils/functions.php';

class Auth {
    // Declare private variables to store database connection and table name
    private $conn;
    private $table_name = "users";

    // Constructor method to initialize the database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to register a new user
    public function register($username, $email, $password) {
        // SQL query to insert a new user into the 'users' table
        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (:username, :email, :password)";

        // Prepare the query for execution
        $stmt = $this->conn->prepare($query);

        // Bind parameters to the prepared statement
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', password_hash($password, PASSWORD_BCRYPT));

        // Execute the query and return the result
        return $stmt->execute();
    }

    // Method to authenticate a user during login
    public function login($username, $password) {
        // SQL query to select a user from the 'users' table based on the username
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";

        // Prepare the query for execution
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Fetch the user record as an associative array
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password and return the user record if valid, otherwise return false
        return ($user && password_verify($password, $user['password'])) ? $user : false;
    }
}

// Instantiate Database and Auth classes
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Handle request
header('Content-Type: application/json');
$request = json_decode(file_get_contents("php://input"));

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the 'action' parameter from the query string, if present
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    // Handle different actions based on the 'action' parameter
    if ($action === 'register') {
        // Retrieve user details from the request, defaulting to empty strings if not provided
        $username = isset($request->username) ? $request->username : '';
        $email = isset($request->email) ? $request->email : '';
        $password = isset($request->password) ? $request->password : '';

        // Attempt to register the user and return a success message if successful
        if ($auth->register($username, $email, $password)) {
            echo json_encode(["message" => "User registered successfully!"]);
        } else {
            // Return an error message if registration failed
            echo json_encode(["message" => "User registration failed!"]);
        }
    } elseif ($action === 'login') {
        // Retrieve login details from the request, defaulting to empty strings if not provided
        $username = isset($request->username) ? $request->username : '';
        $password = isset($request->password) ? $request->password : '';

        // Attempt to log in the user and return a success message with user details if successful
        $user = $auth->login($username, $password);
        if ($user) {
            echo json_encode(["message" => "Login successful!", "user" => $user]);
        } else {
            // Return an error message if login failed
            echo json_encode(["message" => "Login failed!"]);
        }
    } else {
        // Return an error message for invalid actions
        echo json_encode(["message" => "Invalid action!"]);
    }
} else {
    // Return an error message for invalid request methods
    echo json_encode(["message" => "Invalid request method!"]);
}
