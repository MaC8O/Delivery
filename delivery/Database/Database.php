<?php

namespace DELIVERY\Database;

require_once 'Configuration/config.php'; // Assuming you have your constants defined here

use PDO;
use PDOException;

class Database {
    private static $instance = null; // Singleton instance
    private $connection;

    // Private constructor to prevent direct instantiation
    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASSWORD
            );
            // Set error mode to exceptions
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // Test the connection
            error_log("Database connection successful!");
    
        } catch (PDOException $ex) {
            error_log("Database connection failed: " . $ex->getMessage());
            die("Connection failed: " . $ex->getMessage());
        }
    }
    

    // Function to get the Singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Function to return the database connection
    public function getConnection() {        
        return $this->connection;
    }
}
