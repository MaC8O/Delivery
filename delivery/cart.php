<?php
session_start();
require_once __DIR__ . '/Database/Database.php';
use DELIVERY\Database\Database;

$db = new Database();

// Handle order confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_order'])) {
    $orderItems = $_SESSION['cart'];
    $email = $_SESSION['user']['email'];
    $totalAmount = 0;

    // Calculate total amount
    foreach ($orderItems as $itemId) {
        $itemQuery = "SELECT price FROM items WHERE id = :item_id";
        $itemStmt = $db->getConnection()->prepare($itemQuery);
        $itemStmt->bindParam(':item_id', $itemId);
        $itemStmt->execute();
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            $totalAmount += $item['price'];
        }
    }

    // Insert order into orders table
    $insertOrderQuery = "INSERT INTO orders (client_id, total_amount, status) VALUES ((SELECT id FROM user WHERE email = :email), :total_amount, 'pending')";
    $insertOrderStmt = $db->getConnection()->prepare($insertOrderQuery);
    $insertOrderStmt->bindParam(':email', $email);
    $insertOrderStmt->bindParam(':total_amount', $totalAmount);
    $insertOrderStmt->execute();

    $orderId = $db->getConnection()->lastInsertId();

    // Insert ordered items
    foreach ($orderItems as $itemId) {
        // Check if the item exists
        $checkItemQuery = "SELECT id FROM items WHERE id = :item_id";
        $checkItemStmt = $db->getConnection()->prepare($checkItemQuery);
        $checkItemStmt->bindParam(':item_id', $itemId);
        $checkItemStmt->execute();
        $itemExists = $checkItemStmt->fetch(PDO::FETCH_ASSOC);

        if ($itemExists) {
            // Item exists, insert into order_items table
            $insertOrderItemsQuery = "INSERT INTO order_items (order_id, item_id) VALUES (:order_id, :item_id)";
            $insertOrderItemsStmt = $db->getConnection()->prepare($insertOrderItemsQuery);
            $insertOrderItemsStmt->bindParam(':order_id', $orderId);
            $insertOrderItemsStmt->bindParam(':item_id', $itemId);
            $insertOrderItemsStmt->execute();
        } else {
            // Log or handle the situation where the item doesn't exist
            error_log("Item ID {$itemId} does not exist in the items table.");
        }
    }

    unset($_SESSION['cart']);
    $_SESSION['success'] = "Order placed successfully!";
    header('Location: user.php');
    exit();
}

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order'])) {
    unset($_SESSION['cart']);
    header('Location: user.php');
    exit();
}

// Handle item removal from the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    $itemIdToRemove = $_POST['remove_item'];
    if (($key = array_search($itemIdToRemove, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
    }
    $_SESSION['success'] = "Item removed from cart.";
    header('Location: cart.php'); // Redirect back to the cart
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Your Cart</h2>
    
    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalAmount = 0;
                foreach ($_SESSION['cart'] as $itemId): 
                    // Fetch item price
                    $itemQuery = "SELECT name, price FROM items WHERE id = :item_id";
                    $itemStmt = $db->getConnection()->prepare($itemQuery);
                    $itemStmt->bindParam(':item_id', $itemId);
                    $itemStmt->execute();
                    $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Check if item exists
                    if ($item) {
                        $totalAmount += $item['price'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($itemId); ?></td>
                        <td>$<?= number_format($item['price'], 2); ?></td>
                        <td>
                            <form method="POST" action="cart.php" style="display:inline;">
                                <input type="hidden" name="remove_item" value="<?= htmlspecialchars($itemId); ?>">
                                <button type="submit" class="btn btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php
                    } else {
                        // Item not found, display a message
                        echo "<tr><td colspan='3'>Item ID {$itemId} not found.</td></tr>";
                    }
                endforeach; 
                ?>
            </tbody>
        </table>

        <div class="alert alert-info">
            <strong>Total Amount: $<?= number_format($totalAmount, 2); ?></strong>
        </div>
        
        <form method="POST" action="cart.php">
            <button type="submit" name="confirm_order" class="btn btn-success">Confirm Order</button>
            <button type="submit" name="cancel_order" class="btn btn-secondary">Cancel Order</button>
        </form>
        
    <?php else: ?>
        <p>No items in the cart.</p>
    <?php endif; ?>

    <!-- Success message after placing an order or removing an item -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; 
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
