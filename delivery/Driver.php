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
$driverId = $_SESSION['user_id']; // Assuming driver ID is stored in session
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
</head>
<body>

<div class="container mt-5">
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
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Client Name</th>
                <th>Address</th>
                <th>Contact Info</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignedOrders as $order): ?>
                <tr>
                    <td><?= $order['id']; ?></td>
                    <td><?= $order['client_name']; ?></td>
                    <td><?= $order['address']; ?></td>
                    <td><?= $order['contact_info']; ?></td>
                    <td><?= ucfirst($order['status']); ?></td>
                    <td>
                        <form method="POST" action="driver.php">
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                            <select name="status" class="form-select d-inline w-50" required>
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="picked up" <?= $order['status'] == 'picked up' ? 'selected' : ''; ?>>Picked Up</option>
                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                            <button type="submit" class="btn btn-success" name="update_status">Update Status</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
