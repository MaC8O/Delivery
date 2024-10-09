<?php 

namespace DELIVERY\User;
require_once 'Database/Database.php';
use delivery\Database\Database;

 class User {
    private $id;
    private $email;
    private $password;
    private $fullName;
    private $permission;

    public function __construct($email, $password, $fullName){
        $this->email = $email;
        $this->password = $password;
        $this->fullName = $fullName;
    }

    public function createUser($email, $password, $fullName){
        // Connect
        $conn = new Database();
        
        // Prepare the request
        $query = "INSERT INTO user (email, password, fullname) VALUES (:email, :password, :fullname)";
        
        // Use the correct method to get the connection
        $statement = $conn->getConnection()->prepare($query);
    
        // Encryption
        $encrypted_password = password_hash($password, PASSWORD_BCRYPT);
    
        // Bind parameters
        $statement->bindParam(":email", $email);
        $statement->bindParam(":password", $encrypted_password);
        $statement->bindParam(":fullname", $fullName);
    
        // Execute the statement
        $statement->execute();
    }

    // abstract public function login($email, $password);
}