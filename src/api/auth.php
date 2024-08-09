<?php
//AUTHOR-MADE BY TUĞBA YALVAÇ MOHAMMED

// Include the database configuration file and utility functions
require_once '../config/database.php';
require_once '../utils/functions.php';

class Auth {
    // Declare private variables to store database connection and table name
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $email, $password) {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (:username, :email, :password)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', password_hash($password, PASSWORD_BCRYPT));

        return $stmt->execute();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    if ($action === 'register') {
        $username = isset($request->username) ? $request->username : '';
        $email = isset($request->email) ? $request->email : '';
        $password = isset($request->password) ? $request->password : '';

        if ($auth->register($username, $email, $password)) {
            echo json_encode(["message" => "User registered successfully!"]);
        } else {
            echo json_encode(["message" => "User registration failed!"]);
        }
    } elseif ($action === 'login') {
        $username = isset($request->username) ? $request->username : '';
        $password = isset($request->password) ? $request->password : '';

        $user = $auth->login($username, $password);
        if ($user) {
            echo json_encode(["message" => "Login successful!", "user" => $user]);
        } else {
            echo json_encode(["message" => "Login failed!"]);
        }
    } else {
        echo json_encode(["message" => "Invalid action!"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method!"]);
}
