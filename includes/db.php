<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            // Check if PDO MySQL driver is available
            if (!in_array('mysql', PDO::getAvailableDrivers())) {
                throw new PDOException('MySQL PDO driver is not installed');
            }
            
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            // Log error and show user-friendly message
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Check for specific errors
            if ($e->getCode() == 1049) {
                die("Database '" . $this->db_name . "' does not exist. Please create it first.");
            } elseif ($e->getCode() == 1045) {
                die("Database access denied. Please check your username and password.");
            } elseif (strpos($e->getMessage(), 'driver') !== false) {
                die("MySQL PDO driver is not installed. Please install php-mysql extension.");
            } else {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->conn;
    }
    
    // Helper method to check connection
    public function testConnection() {
        try {
            $conn = $this->connect();
            if ($conn) {
                return true;
            }
            return false;
        } catch(Exception $e) {
            return false;
        }
    }
}

// Test connection on include
$db = new Database();
$db->testConnection();
?>
