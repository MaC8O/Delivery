<?php 
namespace DELIVERY\Classes\Admin;

require_once 'Classes/User.php';
require_once 'Database/Database.php';

use DELIVERY\User\User;
use DELIVERY\Database\Database;
use Exception;
use PDO;

class Admin extends User {
    // Login method
    public function login($email, $password) {
        $conn = Database::getInstance()->getConnection();
    
        try {
            $query = "SELECT * FROM user WHERE email = :email";
            $statement = $conn->prepare($query);
            $statement->bindParam(':email', $email);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
    
            if ($result) {
                if (password_verify($password, $result['password'])) {
                    // Store user information in session
                    $_SESSION['user'] = $result;
                    return "Logged in successfully.";
                } else {
                    return "Error: Invalid password.";
                }
            } else {
                return "Error: User not found.";
            }
        } catch (Exception $ex) {
            return "Error: " . $ex->getMessage();
        }
    }

    // Method to create a new admin account
    public function createAdmin($email, $password, $fullname) {
        $conn = Database::getInstance()->getConnection();

        // Check if the admin already exists
        $query = "SELECT * FROM user WHERE email = :email";
        $statement = $conn->prepare($query);
        $statement->bindParam(':email', $email);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            return "Error: Admin account already exists with this email.";
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $query = "INSERT INTO user (email, password, fullname, permission, created_at) 
                      VALUES (:email, :password, :fullname, 'admin', NOW())";
            $statement = $conn->prepare($query);
            $statement->bindParam(':email', $email);
            $statement->bindParam(':password', $hashedPassword);
            $statement->bindParam(':fullname', $fullname);

            if ($statement->execute()) {
                return "Admin account created successfully.";
            } else {
                return "Error: Unable to create admin account.";
            }
        } catch (Exception $ex) {
            return "Error: " . $ex->getMessage();
        }
    }

    // Placeholder for other admin functionalities
    public function createOrder($clientId, $address, $details) {
        // Implement order creation logic here
    }

    public function assignOrderToDriver($orderId, $userId) {
        // Implement order assignment logic here
    }
}
