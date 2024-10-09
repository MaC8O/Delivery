<?php
session_start();
require_once __DIR__ . '/Database/Database.php';
use DELIVERY\Database\Database;

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['permission'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10; // Number of records per page

// Fetch users with pagination
function fetchUsers($currentPage, $recordsPerPage) {
    $db = new Database();
    $conn = $db->getConnection();
    $offset = ($currentPage - 1) * $recordsPerPage;
    $users = [];

    if ($conn) {
        // Count total user records for pagination
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM user");
        $countStmt->execute();
        $totalRecords = $countStmt->fetchColumn();

        // Fetch user records with limit and offset for pagination
        $stmt = $conn->prepare("
            SELECT id, email, fullname, permission, created_at
            FROM user
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':limit', $recordsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [$users, $totalRecords];
    }
}

// Fetch drivers without pagination
function fetchDrivers() {
    $db = new Database();
    $conn = $db->getConnection();
    $drivers = [];

    if ($conn) {
        // Fetch all driver records
        $stmt = $conn->prepare("SELECT id, email, fullname, permission, created_at FROM user WHERE permission = 'driver' ORDER BY created_at DESC");
        $stmt->execute();
        $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $drivers;
}

// Fetch users and drivers for display
list($users, $totalRecords) = fetchUsers($currentPage, $recordsPerPage);
$totalPages = ceil($totalRecords / $recordsPerPage);
$drivers = fetchDrivers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Admin User and Driver Management</title>
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
        h3 {
            margin-top: 40px;
            color: #4e54c8;
        }
        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
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
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>User and Driver Management</h2>

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

    <!-- Users Table -->
    <h3>Users</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Full Name</th>
                <th>Permission</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['fullname']) ?></td>
                        <td><?= htmlspecialchars($user['permission']) ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination for Users -->
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

    <!-- Drivers Table -->
    <h3>Drivers</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Full Name</th>
                <th>Permission</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($drivers)): ?>
                <?php foreach ($drivers as $driver): ?>
                    <tr>
                        <td><?= htmlspecialchars($driver['id']) ?></td>
                        <td><?= htmlspecialchars($driver['email']) ?></td>
                        <td><?= htmlspecialchars($driver['fullname']) ?></td>
                        <td><?= htmlspecialchars($driver['permission']) ?></td>
                        <td><?= htmlspecialchars($driver['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No drivers found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
