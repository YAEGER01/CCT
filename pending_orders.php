<?php
session_start();
include 'db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id']; // Use the seller's user ID

// Fetch accepted orders from the database, including rice and drinks options
$orderQuery = "SELECT a.id, u.username AS user_name, m.meal_name, a.quantity, a.status, m.price, 
                      a.rice_option, a.rice_price, a.drinks, a.drinks_price 
               FROM accepted_orders a 
               JOIN users u ON a.user_id = u.id 
               JOIN meals m ON a.meal_id = m.id";
$orderResult = mysqli_query($conn, $orderQuery);

$orders = [];
if ($orderResult) {
    while ($row = mysqli_fetch_assoc($orderResult)) {
        $orders[] = $row;
    }
}

// Handle marking an order as completed
if (isset($_POST['mark_completed'])) {
    $order_id = intval($_POST['order_id']);

    // Fetch the order details, including rice and drinks options
    $orderQuery = "SELECT a.*, m.price 
                   FROM accepted_orders a 
                   JOIN meals m ON a.meal_id = m.id 
                   WHERE a.id = $order_id";
    $orderResult = mysqli_query($conn, $orderQuery);
    $order = mysqli_fetch_assoc($orderResult);

    if ($order) {
        // Calculate the total price, including rice and drinks prices
        $total_price = $order['quantity'] * ($order['price'] + $order['rice_price'] + $order['drinks_price']);

        // Insert into transactions table with rice and drinks details
        $insertQuery = "INSERT INTO transactions (order_id, user_id, meal_id, quantity, total_price, seller_id, transaction_date, rice_option, rice_price, drinks, drinks_price)
                        VALUES ($order_id, {$order['user_id']}, {$order['meal_id']}, {$order['quantity']}, 
                                $total_price, $seller_id, NOW(), '{$order['rice_option']}', {$order['rice_price']}, '{$order['drinks']}', {$order['drinks_price']})";
        mysqli_query($conn, $insertQuery);

        // Delete the order from accepted_orders once it's moved to transactions
        $deleteQuery = "DELETE FROM accepted_orders WHERE id = $order_id";
        mysqli_query($conn, $deleteQuery);

        // Redirect after marking the order as completed
        header("Location: pending_orders.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #2b2b2b;
            /* Grayish-black background */
            color: white;
            /* White text for contrast */
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #6a0dad;
            /* Purple headings */
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #333;
            /* Dark gray background for table */
            border-radius: 15px;
            /* Rounded corners for the table */
            overflow: hidden;
            /* Clip the corners */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            /* Shadow for depth */
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #555;
            /* Grayish border */
        }

        th {
            background-color: #6a0dad;
            /* Purple header */
            color: white;
        }

        tr:nth-child(even) {
            background-color: #444;
            /* Darker row background */
        }

        tr:hover {
            background-color: #555;
            /* Hover effect for rows */
        }

        button {
            background-color: #6a0dad;
            /* Purple button */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            /* Rounded corners for buttons */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #4b0082;
            /* Darker purple on hover */
        }
    </style>
</head>

<body>
    <a href="seller_dashboard.php">Dashboard</a>
    <h1>Pending Orders</h1>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Meal Name</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($order['user_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['meal_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['quantity']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                    echo "<td>
                            <form method='POST' action='pending_orders.php'>
                                <input type='hidden' name='order_id' value='" . $order['id'] . "'>
                                <button type='submit' name='mark_completed'>Mark as Completed</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No pending orders.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>

</html>