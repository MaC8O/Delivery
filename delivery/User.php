<?php
session_start();
require_once __DIR__ . '/Database/Database.php';
use DELIVERY\Database\Database;

// Initialize Database connection
$db = new Database();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Product categories
$categories = [
    'Cosmetics' => [
        ['id' => 1, 'name' => 'Lipstick', 'price' => 12.99],
        ['id' => 2, 'name' => 'Korean Makeup', 'price' => 29.99],
        ['id' => 3, 'name' => 'Lotion', 'price' => 9.99],
        ['id' => 4, 'name' => 'Shampoo', 'price' => 7.99],
    ],
    'Clothes' => [
        ['id' => 5, 'name' => 'Jeans', 'price' => 40.00],
        ['id' => 6, 'name' => 'T-Shirt', 'price' => 15.00],
        ['id' => 7, 'name' => 'Pants', 'price' => 35.00],
        ['id' => 8, 'name' => 'Jacket', 'price' => 55.00],
    ],
    'Shoes' => [
        ['id' => 9, 'name' => 'Sneakers', 'price' => 60.00],
        ['id' => 10, 'name' => 'Flipflops', 'price' => 10.00],
        ['id' => 11, 'name' => 'Sport Shoes', 'price' => 80.00],
    ],
];

// Initialize session variables
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if (!isset($_SESSION['ordered_items'])) {
    $_SESSION['ordered_items'] = [];
}

// Handle adding items to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $itemId = (int)$_POST['item_id']; // Ensure it's an integer
        if (!in_array($itemId, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $itemId;
            $_SESSION['success'] = 'Item added to cart successfully!';
        } else {
            $_SESSION['error'] = 'Item is already in the cart!';
        }
        header('Location: user.php');
        exit();
    }

    // Handle order confirmation or cancellation
    if (isset($_POST['action']) && isset($_POST['order_item'])) {
        $itemId = (int)$_POST['order_item']; // Ensure it's an integer
        $userId = (int)$_SESSION['user_id']; // Ensure it's an integer

        if ($_POST['action'] === 'confirm') {
            // Insert order into the database
            $stmt = $db->getConnection()->prepare("INSERT INTO orders (client_id, item_id, status) VALUES (?, ?, 'pending')");
            if ($stmt->execute([$userId, $itemId])) {
                $_SESSION['success'] = 'Order confirmed!';
                $_SESSION['ordered_items'][] = $itemId; // Add to ordered items for display
            } else {
                $_SESSION['error'] = 'Failed to confirm order!';
            }
        } elseif ($_POST['action'] === 'cancel') {
            $_SESSION['success'] = 'Order cancelled!';
        }
        header('Location: user.php');
        exit();
    }
}

// Fetch user's past orders
$userId = (int)$_SESSION['user_id']; // Ensure it's an integer
$stmt = $db->getConnection()->prepare("SELECT * FROM orders WHERE client_id = ?");
$stmt->execute([$userId]);
$pastOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Enjoy your shopping with us...</h2>

    <?php foreach ($categories as $categoryName => $items): ?>
        <h4><?= htmlspecialchars($categoryName); ?></h4>
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($item['name']); ?></h5>
                            <p class="card-text">$<?= number_format($item['price'], 2); ?></p>
                            <form method="POST" action="user.php">
                                <input type="hidden" name="item_id" value="<?= $item['id']; ?>">
                                <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                            </form>
                            <?php if (in_array($item['id'], $_SESSION['ordered_items'])): ?>
                                <span class="text-success">&#10003; Ordered</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <!-- Success and error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Cart Section -->
    <h2 class="mt-5">Your Cart</h2>
    <?php if (!empty($_SESSION['cart'])): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $itemId): ?>
                    <tr>
                        <td><?= htmlspecialchars($itemId); ?></td>
                        <td>
                            <form method="POST" action="user.php" style="display:inline;">
                                <input type="hidden" name="order_item" value="<?= $itemId; ?>">
                                <button type="submit" name="action" value="confirm" class="btn btn-success">Confirm</button>
                            </form>
                            <form method="POST" action="user.php" style="display:inline;">
                                <input type="hidden" name="order_item" value="<?= $itemId; ?>">
                                <button type="submit" name="action" value="cancel" class="btn btn-danger">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No items in the cart.</p>
    <?php endif; ?>

    <!-- Display Past Orders -->
    <h2 class="mt-5">Your Past Orders</h2>
    <?php if ($pastOrders): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Item ID</th>
                    <th>Status</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pastOrders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']); ?></td>
                        <td><?= htmlspecialchars($order['item_id']); ?></td>
                        <td><?= htmlspecialchars($order['status']); ?></td>
                        <td><?= htmlspecialchars($order['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no past orders.</p>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
