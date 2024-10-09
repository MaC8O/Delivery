<?php
namespace DELIVERY\Database;

require_once 'Configuration/config.php'; // Assuming you have your constants defined here

use PDO;
use PDOException;

class Database {
    private $connection;

    // Constructor to establish a database connection
    public function __construct() {
        try {
            // Use the provided database credentials (DEL, 1234)
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER, 
                DB_PASSWORD
            );

            // Set PDO error mode to exception for proper error handling
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            // Handle connection errors
            die("Connection failed: " . $ex->getMessage());
        }
    }

    // Function to return the database connection
    public function getConnection() {        
        return $this->connection;
    }
}
