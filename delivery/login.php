<?php
// Start a session
session_start();

// Include necessary classes
require_once 'Classes/User.php';
require_once 'Classes/Admin.php';
require_once 'Classes/Driver.php';
require_once 'Database/Database.php';

use DELIVERY\Database\Database;
use DELIVERY\User\User;
use DELIVERY\Classes\Admin\Admin;
use DELIVERY\Driver\Driver;

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Create a database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Prepare the query based on the selected role
    $query = "SELECT * FROM user WHERE email = :email AND permission = :role";
    $statement = $conn->prepare($query);
    $statement->bindParam(':email', $email);
    $statement->bindParam(':role', $role);
    
    // Execute the query
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // Verify the user credentials
    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['user'] = $result; // Store user info in session
        $_SESSION['success'] = "Login successful! Welcome, " . $result['fullname'];
        
        // Debugging output
        error_log("User Role: " . $result['permission']); // Log the user role for debugging
        error_log("Session Data: " . print_r($_SESSION, true)); // Log session data for debugging
    
        // Redirect based on user role
        switch ($result['permission']) {
            case 'admin':
                header('Location: admin.php'); // Redirect to admin page
                break;
            case 'driver':
                header('Location: driver.php'); // Redirect to driver page
                break;
            case 'client':
                header('Location: user.php'); // Redirect to user page
                break;
            default:
                header('Location: dashboard.php'); // Redirect to a dashboard or home page if role is not recognized
                break;
        }
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password!";
    }
}    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-family: 'Arial', sans-serif;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            font-weight: bold;
            color: #4e54c8;
        }
        .btn-primary {
            background-color: #4e54c8;
            border: none;
        }
        .btn-primary:hover {
            background-color: #3c429b;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2 class="text-center">Login</h2>
    
    <!-- Display Success or Error Messages -->
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

    <!-- Login Form -->
    <form action="login.php" method="POST">
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
                <option value="client">User</option>
                <option value="driver">Driver</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
