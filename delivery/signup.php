<?php
// Start a session
session_start();

// Include necessary classes
require_once 'Classes/User.php';
require_once 'Classes/Admin.php';
require_once 'Classes/Driver.php';
require_once __DIR__ . '/Database/Database.php'; // Using absolute path

use DELIVERY\Database\Database;
use DELIVERY\User\User; 
use DELIVERY\Classes\Admin\Admin;
use DELIVERY\Driver\Driver;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $role = $_POST['role'];

    // Instantiate the appropriate class based on the role
    $user = null;
    $permission = null; // Initialize permission

    switch ($role) {
        case 'admin':
            $permission = 'admin'; // Set permission for admin
            $user = new Admin($email, $password, $fullname,$permission); // Admin instantiation
            break;
        case 'user': 
            $permission = 'client'; // Specify 'client' as the permission for user
            $user = new User($email, $password, $fullname, $permission); // User instantiation
            break;
        case 'driver':
            $permission = 'driver'; // Set permission for driver
            $user = new Driver($email, $password, $fullname,$permission); // Driver instantiation
            break;
        default:
            $_SESSION['error'] = "Invalid role selected";
            header('Location: signup.php');
            exit();
    }

    // Attempt to create the user in the database
    try {
        if ($user) {
            // Pass email, password, full name, and role (permission) to the createUser method
            if ($role === 'user') {
                $user->createUser($email, $password, $fullname, 'client'); // Call createUser for user with 'client' permission
            } else {
                $user->createUser($email, $password, $fullname, $permission); // Call createUser for admin/driver
            }

            $_SESSION['success'] = "User successfully registered!";
            header('Location: login.php'); // Redirect to login page after successful signup
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: signup.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .signup-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container signup-container">
    <h2 class="text-center mb-4">Signup</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <form action="signup.php" method="POST">
        <div class="mb-3">
            <label for="fullname" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="fullname" name="fullname" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Select Role</label>
            <select class="form-select" id="role" name="role" required>
                <option value="">Choose role</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
                <option value="driver">Driver</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign Up</button>
    </form>

    <div class="text-center mt-3">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
