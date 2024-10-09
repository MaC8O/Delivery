<?php

namespace DELIVERY\Driver;

require_once __DIR__ . '/User.php'; // Ensure this path is correct
use DELIVERY\User\User;
use DELIVERY\Database\Database;

class Driver extends User {
    private $db;

    public function __construct($email, $password, $fullName) {
        parent::__construct($email, $password, $fullName, 'driver'); // Set permission to 'driver'
        $this->db = new Database(); // Initialize the database connection
    }

    public function login($email, $password) {
        $query = "SELECT * FROM user WHERE email = :email AND permission = 'driver'";
        $statement = $this->db->getConnection()->prepare($query);
        $statement->bindParam(':email', $email);
        $statement->execute();
        
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['driver_id'] = $user['id'];
            $_SESSION['driver_email'] = $user['email'];
            return true; // Login successful
        }

        return false; // Login failed
    }

    public function updateOrderStatus($order_id, $status) {
        $query = "UPDATE orders SET status = :status WHERE id = :order_id";
        $statement = $this->db->getConnection()->prepare($query);
        
        $statement->bindParam(':status', $status);
        $statement->bindParam(':order_id', $order_id);
        
        return $statement->execute(); // Return true if successful, false otherwise
    }

    public function viewAssignedOrders() {
        $driver_id = $_SESSION['driver_id']; // Assuming driver ID is stored in session
        $query = "SELECT * FROM orders WHERE driver_id = :driver_id";
        $statement = $this->db->getConnection()->prepare($query);
        
        $statement->bindParam(':driver_id', $driver_id);
        $statement->execute();
        
        return $statement->fetchAll(); // Return all assigned orders
    }
}
