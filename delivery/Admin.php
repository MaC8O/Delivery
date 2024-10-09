<?php
session_start();
require_once __DIR__ . '/Classes/Admin.php';
require_once __DIR__ . '/Database/Database.php'; 
use DELIVERY\Admin\Admin;
use DELIVERY\Database\Database;

// Ensure only admins can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Handle form submission for creating orders
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_order'])) {
    $clientName = $_POST['client_name'];
    $address = $_POST['address'];
    $contactInfo = $_POST['contact_info'];

    if (empty($clientName) || empty($address) || empty($contactInfo)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Insert into the database
        try {
            $db = new Database();
            $query = "INSERT INTO orders (client_name, address, contact_info) VALUES (:client_name, :address, :contact_info)";
            $stmt = $db->getConnection()->prepare($query);
            $stmt->bindParam(':client_name', $clientName);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':contact_info', $contactInfo);
            $stmt->execute();
            $_SESSION['success'] = "Order created successfully!";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
}

// Handle status updates and driver assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_order'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    $driverId = $_POST['driver_id'];

    try {
        $db = new Database();
        $query = "UPDATE orders SET status = :status, driver_id = :driver_id WHERE id = :id";
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':driver_id', $driverId);
        $stmt->bindParam(':id', $orderId);
        $stmt->execute();
        $_SESSION['success'] = "Order updated successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Fetch all orders for display
$db = new Database();
$orders = $db->getConnection()->query("SELECT * FROM orders")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>

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

    <!-- Order Creation Form -->
    <form method="POST" action="admin.php">
        <h4>Create New Order</h4>
        <div class="mb-3">
            <label for="client_name" class="form-label">Client Name</label>
            <input type="text" class="form-control" id="client_name" name="client_name" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" required></textarea>
        </div>
        <div class="mb-3">
            <label for="contact_info" class="form-label">Contact Information</label>
            <input type="text" class="form-control" id="contact_info" name="contact_info" required>
        </div>
        <button type="submit" class="btn btn-primary" name="create_order">Create Order</button>
    </form>

    <!-- List of Orders -->
    <h4 class="mt-5">Manage Orders</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Client Name</th>
                <th>Address</th>
                <th>Contact Info</th>
                <th>Status</th>
                <th>Assigned Driver</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id']; ?></td>
                    <td><?= $order['client_name']; ?></td>
                    <td><?= $order['address']; ?></td>
                    <td><?= $order['contact_info']; ?></td>
                    <td><?= ucfirst($order['status']); ?></td>
                    <td><?= $order['driver_id'] ? $order['driver_id'] : 'Not Assigned'; ?></td>
                    <td>
                        <form method="POST" action="admin.php" class="d-inline">
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                            <select name="status" class="form-select d-inline w-50" required>
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="picked up" <?= $order['status'] == 'picked up' ? 'selected' : ''; ?>>Picked Up</option>
                                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            </select>
                            <select name="driver_id" class="form-select d-inline w-50">
                                <option value="">Assign Driver</option>
                                <?php
                                $drivers = $db->getConnection()->query("SELECT id, fullname FROM drivers")->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($drivers as $driver) {
                                    echo "<option value='{$driver['id']}'" . ($order['driver_id'] == $driver['id'] ? ' selected' : '') . ">{$driver['fullname']}</option>";
                                }
                                ?>
                            </select>
                            <button type="submit" class="btn btn-success" name="update_order">Update</button>
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
