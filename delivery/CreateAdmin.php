<?php
session_start();
require_once 'Classes/Admin.php';
require_once 'Database/Database.php';

use DELIVERY\Database\Database;
use DELIVERY\Classes\Admin\Admin;

$email = "admin@gmail.com";
$password = password_hash('1234', PASSWORD_DEFAULT); // Securely hashed password
$fullName = "Admin User";
$permission = 'admin';

try {
    $admin = new Admin($email, $password, $fullName, $permission);
    $db = Database::getInstance()->getConnection();

    // Check if admin already exists
    $query = "SELECT * FROM user WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        throw new Exception("Admin account already exists with this email.");
    }

    $admin->createUser($db);
    echo "Admin account successfully created!";
} catch (Exception $e) {
    echo "Error creating admin account: " . $e->getMessage();
}
?>
