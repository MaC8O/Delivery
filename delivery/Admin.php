<?php
session_start(); // Ensure this is at the top
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/Classes/Admin.php';
require_once __DIR__ . '/Database/Database.php'; 
use DELIVERY\Admin\Admin;
use DELIVERY\Database\Database;

// Check if the session role is set
if (!isset($_SESSION['role'])) {
    echo "Session role not set!";
    exit();
}

if ($_SESSION['role'] != 'admin') {
    echo "You do not have admin access!";
    exit();
}

// Handle form submission for creating orders
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_order'])) {
    $clientEmail = $_POST['client_email'];
    $address = $_POST['address'];
    $contactInfo = $_POST['contact_info'];

    if (empty($clientEmail) || empty($address) || empty($contactInfo)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Insert into the database
        try {
            $db = new Database();
            // Get the client ID from the user table using email
            $clientStmt = $db->getConnection()->prepare("SELECT id FROM user WHERE email = :email");
            $clientStmt->bindParam(':email', $clientEmail, PDO::PARAM_STR);
            $clientStmt->execute();
            $clientId = $clientStmt->fetchColumn();

            if ($clientId) {
                $query = "INSERT INTO orders (client_id, address, contact_info) VALUES (:client_id, :address, :contact_info)";
                $stmt = $db->getConnection()->prepare($query);
                $stmt->bindParam(':client_id', $clientId);
                $stmt->bindParam(':address', $address);
                $stmt->bindParam(':contact_info', $contactInfo);
                $stmt->execute();
                $_SESSION['success'] = "Order created successfully!";
            } else {
                $_SESSION['error'] = "Client email not found.";
            }
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

// Search functionality
$searchClient = isset($_GET['client_name']) ? $_GET['client_name'] : '';
$searchDriver = isset($_GET['driver_name']) ? $_GET['driver_name'] : '';
$searchStatus = isset($_GET['status']) ? $_GET['status'] : '';

// Fetch orders based on search
try {
    $db = new Database();
    $query = "SELECT o.*, u.email AS client_email, d.fullname AS driver_name
              FROM orders o
              JOIN user u ON o.client_id = u.id
              LEFT JOIN drivers d ON o.driver_id = d.id
              WHERE (u.email LIKE :client OR :client = '')
              AND (d.fullname LIKE :driver OR :driver = '')
              AND (o.status = :status OR :status = '')";
    $stmt = $db->getConnection()->prepare($query);
    $stmt->bindValue(':client', '%' . $searchClient . '%');
    $stmt->bindValue(':driver', '%' . $searchDriver . '%');
    $stmt->bindValue(':status', $searchStatus);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    $orders = [];
}

// Fetch available drivers for assignment
try {
    $db = new Database();
    $driverQuery = "SELECT id, fullname FROM drivers WHERE id NOT IN (SELECT driver_id FROM orders WHERE driver_id IS NOT NULL)";
    $driverStmt = $db->getConnection()->prepare($driverQuery);
    $driverStmt->execute();
    $availableDrivers = $driverStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    $availableDrivers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-buttons {
            position: absolute;
            right: 20px;
            top: 20px;
        }
        .nav-buttons .btn {
            margin-left: 10px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>Admin Dashboard</h2>

    <!-- Logout and Check Detail Buttons -->
    <div class="nav-buttons">
        <a href="login.php" class="btn btn-danger">Logout</a>
        <a href="check.php" class="btn btn-info">Check Details</a>
    </div>

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
            <label for="client_email" class="form-label">Client Email</label>
            <input type="email" class="form-control" id="client_email" name="client_email" required>
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

    <!-- Search Orders -->
    <form method="GET" action="admin.php" class="mt-5">
        <h4>Search Orders</h4>
        <div class="row">
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" name="client_name" placeholder="Search by Client Name" value="<?= htmlspecialchars($searchClient); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <input type="text" class="form-control" name="driver_name" placeholder="Search by Driver Name" value="<?= htmlspecialchars($searchDriver); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $searchStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="assigned" <?= $searchStatus === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                    <option value="pickedup" <?= $searchStatus === 'pickedup' ? 'selected' : ''; ?>>Picked Up</option>
                    <option value="intransit" <?= $searchStatus === 'intransit' ? 'selected' : ''; ?>>In Transit</option>
                    <option value="delivered" <?= $searchStatus === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?= $searchStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-secondary">Search</button>
    </form>

    <!-- Orders Table -->
    <h4 class="mt-5">Orders List</h4>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client Email</th>
                <th>Status</th>
                <th>Driver Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']); ?></td>
                    <td><?= htmlspecialchars($order['client_email']); ?></td>
                    <td><?= htmlspecialchars($order['status']); ?></td>
                    <td><?= htmlspecialchars($order['driver_name'] ?: 'Unassigned'); ?></td>
                    <td>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?= $order['id']; ?>">Update</button>

                        <!-- Update Modal -->
                        <div class="modal fade" id="updateModal<?= $order['id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="updateModalLabel">Update Order Status</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="admin.php">
                                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" name="status" required>
                                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="assigned" <?= $order['status'] === 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                                    <option value="pickedup" <?= $order['status'] === 'pickedup' ? 'selected' : ''; ?>>Picked Up</option>
                                                    <option value="intransit" <?= $order['status'] === 'intransit' ? 'selected' : ''; ?>>In Transit</option>
                                                    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="driver_id" class="form-label">Assign Driver</label>
                                                <select class="form-select" name="driver_id" required>
                                                    <option value="">Select Driver</option>
                                                    <?php foreach ($availableDrivers as $driver): ?>
                                                        <option value="<?= $driver['id']; ?>"><?= htmlspecialchars($driver['fullname']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary" name="update_order">Update Order</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
