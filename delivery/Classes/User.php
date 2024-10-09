<?php

namespace DELIVERY\User;

require_once 'Database/Database.php';
use DELIVERY\Database\Database;

class User {
    private $id;
    private $email;
    private $password;
    private $fullName;
    private $permission;

    public function __construct($email, $password, $fullName, $permission = 'client') {
        $this->email = $email;
        $this->password = $password;
        $this->fullName = $fullName;
        $this->permission = $permission; // Default to 'client'
    }

    public function createUser() {
        // Connect
        $conn = Database::getInstance(); // Use Singleton pattern to get the instance

        // Prepare the request
        $query = "INSERT INTO user (email, password, fullname, permission) VALUES (:email, :password, :fullname, :permission)";
        
        // Prepare the statement
        $statement = $conn->getConnection()->prepare($query);

        // Encrypt the password
        $encrypted_password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind parameters
        $statement->bindParam(":email", $this->email);
        $statement->bindParam(":password", $encrypted_password);
        $statement->bindParam(":fullname", $this->fullName);
        $statement->bindParam(":permission", $this->permission);

        // Execute the statement and handle any exceptions
        try {
            $statement->execute();
        } catch (\PDOException $e) {
            // Handle exception if user creation fails (e.g., duplicate email)
            throw new \Exception("Error creating user: " . $e->getMessage());
        }
    }


    
    
    // You may also add other user-related methods here, such as login, etc.
}
