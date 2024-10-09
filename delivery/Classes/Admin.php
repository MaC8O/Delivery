<?php 
namespace DELIVERY\Classes\Admin;
require_once 'Classes/User.php';
require_once 'Database/Database.php';

use DELIVERY\User\User;
use DELIVERY\Database\Database;
use Exception;
use PDO;

class Admin extends User {
    public function login($email, $password) {
        $conn = new Database();
    
        try {
            $query = "SELECT * FROM user WHERE email = :email";
    
            // Use the correct method to get the connection
            $statement = $conn->getConnection()->prepare($query);
            $statement->bindParam(':email', $email);
            
            $statement->execute();
    
            //fetch the result
            $result = $statement->fetch(PDO::FETCH_ASSOC);
    
            if ($result) {
                if (password_verify($password, $result['password'])) {
                    echo "logged in";
                } else {
                    echo "error: Invalid password.";
                }
            } else {
                echo "error: User not found.";
            }
        } catch (Exception $ex) {
            echo "Error: " . $ex->getMessage();
        }
    }
    public function createOrder($clientid, $address, $details){}

    public function assignOrderToDriver($order_id, $user_id){}

    
}