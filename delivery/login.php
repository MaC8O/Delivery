<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    $conn = Database::getInstance()->getConnection();

    if (!$conn) {
        $_SESSION['error'] = "Database connection failed!";
        header('Location: login.php');
        exit();
    }

    error_log("Login attempt: email = $email, role = $role");

    // Ensure the role is set
    if (empty($role)) {
        $_SESSION['error'] = "Role is required!";
        header('Location: login.php');
        exit();
    }

    // Query to fetch user data based on email and role
    $query = "SELECT * FROM user WHERE email = :email";
    $statement = $conn->prepare($query);
    $statement->bindParam(':email', $email);

    if (!$statement->execute()) {
        $_SESSION['error'] = "Query execution failed!";
        header('Location: login.php');
        exit();
    }

    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and role matches
    if ($result && $result['permission'] === $role) {
        // Verify the password
        if (password_verify($password, $result['password'])) {
            $_SESSION['user'] = $result;
            $_SESSION['success'] = "Login successful! Welcome, " . $result['fullname'];
            $_SESSION['role'] = $result['permission']; // Set the role in session

            switch ($result['permission']) {
                case 'admin':
                    header('Location: Admin.php');
                    exit();
                case 'driver':
                    header('Location: Driver.php');
                    exit();
                case 'client':
                    header('Location: User.php');
                    exit();
                default:
                    $_SESSION['error'] = "Invalid user role!";
                    header('Location: login.php');
                    exit();
            }
        } else {
            $_SESSION['error'] = "Invalid email or password!";
            error_log("Password verification failed for email: " . $email);
        }
    } else {
        $_SESSION['error'] = "User not found or role mismatch!";
        error_log("No user found for email: " . $email . " with role: " . $role);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
