<?php
session_start();
include 'db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php"); // Redirect to login if not a seller
    exit();
}

$seller_id = $_SESSION['user_id']; // Get seller's ID

// Handle accepting and declining orders
if (isset($_POST['action']) && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = ($_POST['action'] === 'accept') ? 'accepted' : 'declined';

    // Update the order status in the database
    $updateQuery = "UPDATE orders SET status = ? WHERE id = ? AND meal_id IN (SELECT id FROM meals WHERE seller_id = ?)";
    $stmt = $conn->prepare($updateQuery);

    // Check if prepare() failed and output error if it did
    if ($stmt === false) {
        die("Error in query preparation: " . $conn->error);
    }

    // Bind parameters and execute the query
    $stmt->bind_param('sii', $new_status, $order_id, $seller_id);
    $stmt->execute();

    // Check if the status update was successful
    if ($stmt->affected_rows > 0) {
        echo "Order successfully " . htmlspecialchars($new_status);
    } else {
        echo "Failed to update order status. Please try again.";
    }

    // Redirect back to the track_orders page after updating the status
    header("Location: track_orders.php");
    exit();
}

// Fetch orders made to the seller
$orderQuery = "
    SELECT o.id AS order_id, o.status, m.name AS meal_name, m.price, u.username AS customer_name 
    FROM orders o
    JOIN meals m ON o.meal_id = m.id
    JOIN users u ON o.user_id = u.id
    WHERE m.seller_id = ? AND (o.status = 'pending' OR o.status = 'accepted' OR o.status = 'declined')
    ORDER BY o.id DESC";
    
$stmt = $conn->prepare($orderQuery);

// Check if prepare() failed and output error if it did
if ($stmt === false) {
    die("Error in query preparation: " . $conn->error);
}

$stmt->bind_param('i', $seller_id);
$stmt->execute();
$orderResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .order-container {
            margin: 20px;
        }
        .order {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button.accept {
            background-color: #28a745;
        }
        .button.decline {
            background-color: #dc3545;
        }
        .button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Track Orders</h1>
    <a href="seller_dashboard.php" style="color: white;">Back to Dashboard</a>
</div>

<div class="order-container">
    <?php if ($orderResult->num_rows > 0): ?>
        <?php while ($order = $orderResult->fetch_assoc()): ?>
            <div class="order">
                <h3>Meal: <?php echo htmlspecialchars($order['meal_name']); ?></h3>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                <p><strong>Total Price:</strong> $<?php echo htmlspecialchars($order['price']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>

                <!-- Action buttons for accepting or declining orders -->
                <?php if ($order['status'] === 'pending'): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <button type="submit" name="action" value="accept" class="button accept">Accept Order</button>
                        <button type="submit" name="action" value="decline" class="button decline">Decline Order</button>
                    </form>
                <?php else: ?>
                    <p><strong>This order has been <?php echo htmlspecialchars($order['status']); ?>.</strong></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>

</body>
</html>
