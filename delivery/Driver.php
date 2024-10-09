<?php
session_start();
require_once __DIR__ . '/Classes/Driver.php';
require_once __DIR__ . '/Database/Database.php';

use DELIVERY\Driver\Driver;
use DELIVERY\Database\Database;

// Ensure only drivers can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'driver') {
    header('Location: login.php');
    exit();
}

// Fetch assigned orders for this driver
$driverId = $_SESSION['user']['id']; // Assuming driver ID is stored in session
$db = new Database();
$orders = $db->getConnection()->prepare("SELECT * FROM orders WHERE driver_id = :driver_id");
$orders->bindParam(':driver_id', $driverId);
$orders->execute();
$assignedOrders = $orders->fetchAll(PDO::FETCH_ASSOC);

// Handle status update for an order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];

    try {
        // Ensure the status is a valid option
        $validStatuses = ['pending', 'delivered'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid status update.');
        }

        $updateQuery = "UPDATE orders SET status = :status WHERE id = :id";
        $updateStmt = $db->getConnection()->prepare($updateQuery);
        $updateStmt->bindParam(':status', $status);
        $updateStmt->bindParam(':id', $orderId);
        $updateStmt->execute();
        $_SESSION['success'] = "Order status updated successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Refresh the orders after an update
$orders = $db->getConnection()->prepare("SELECT * FROM orders WHERE driver_id = :driver_id");
$orders->bindParam(':driver_id', $driverId);
$orders->execute();
$assignedOrders = $orders->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: #007bff;
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
        }
        .nav-link:hover {
            color: #e0e0e0 !important;
        }
        .container {
            margin-top: 30px;
        }
        h2 {
            margin-bottom: 20px;
            color: #343a40;
        }
        .alert {
            margin-bottom: 20px;
        }
        .table {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #007bff;
            color: #ffffff;
        }
        .status-dropdown {
            width: 150px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        footer {
            margin-top: 40px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Delivery System</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h2>Assigned Deliveries</h2>

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

    <!-- List of Assigned Orders -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Client Name</th>
                <th>Contact Info</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignedOrders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']); ?></td>
                    <td><?= htmlspecialchars($order['client_name']); ?></td>
                    <td><?= htmlspecialchars($order['contact_info']); ?></td>
                    <td><?= ucfirst(htmlspecialchars($order['status'])); ?></td>
                    <td>
                        <form method="POST" action="driver.php">
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                            <select name="status" class="form-select status-dropdown" required>
                                <option value="">Change Status</option>
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary mt-2">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<footer>
    <p>&copy; <?= date("Y"); ?> Delivery System. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
