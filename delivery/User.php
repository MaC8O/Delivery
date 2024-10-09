<?php
session_start();
require_once __DIR__ . '/Database/Database.php';
use DELIVERY\Database\Database;

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user']; // Logged-in user details
$email = $user['email']; // Use email to fetch order history
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 5; // Number of orders per page

// Fetch user order history with pagination
function fetchUserOrders($email, $currentPage, $recordsPerPage) {
    $db = new Database();
    $conn = $db->getConnection();
    $offset = ($currentPage - 1) * $recordsPerPage;
    $orders = [];

    if ($conn) {
        // Count total records for pagination
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE client_id = (SELECT id FROM user WHERE email = :email)");
        $countStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $countStmt->execute();
        $totalRecords = $countStmt->fetchColumn();

        // Fetch records with limit and offset for pagination
        $stmt = $conn->prepare("
            SELECT id, status, total_amount, created_at
            FROM orders
            WHERE client_id = (SELECT id FROM user WHERE email = :email)
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $recordsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [$orders, $totalRecords];
    }
}

list($orders, $totalRecords) = fetchUserOrders($email, $currentPage, $recordsPerPage);
$totalPages = ceil($totalRecords / $recordsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <title>Order History</title>
    <style>
        body {
            background: linear-gradient(135deg, #ffafbd, #ffc3a0);
            font-family: 'Roboto', sans-serif;
            color: #333;
        }
        .container {
            margin-top: 50px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        h2 {
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
            color: #4e54c8;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 12px; /* Smaller font size for the logout button */
            padding: 5px 10px; /* Smaller padding */
        }
        table {
            margin-top: 20px;
        }
        th, td {
            text-align: center;
        }
        .pagination {
            justify-content: center;
        }
        .page-link {
            color: #4e54c8;
        }
        .page-item.active .page-link {
            background-color: #4e54c8;
            border-color: #4e54c8;
            color: white;
        }
        .page-item.disabled .page-link {
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Order History for <?= htmlspecialchars($user['fullname']) ?></h2>

    <a href="login.php" class="btn btn-danger logout-btn">Logout</a>

    <!-- Order History Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Status</th>
                <th>Total Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td><?= htmlspecialchars($order['total_amount']) ?></td>
                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 