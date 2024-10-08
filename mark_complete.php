<?php
include 'db.php';

if (isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $delivered_at = date("Y-m-d H:i:s");

    // Update the order status to 'delivered'
    $query = "UPDATE orders SET status = 'delivered', delivered_at = '$delivered_at' WHERE id = $order_id";
    
    if (mysqli_query($conn, $query)) {
        echo "Order marked as completed.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    header('Location: pending_orders.php'); // Redirect back to pending orders
    exit();
}

?>
