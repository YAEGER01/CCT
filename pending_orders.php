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
            /* Background Style */
            background: radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 0% 0% / 64px 64px,
                radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 32px 32px / 64px 64px,
                linear-gradient(#a43fc6 2px, transparent 2px) 0px -1px / 32px 32px,
                linear-gradient(90deg, #a43fc6 2px, #ffffff 2px) -1px 0px / 32px 32px #ffffff;
            background-size: 64px 64px, 64px 64px, 32px 32px, 32px 32px;
            background-color: #ffffff;
            animation: scroll-diagonal 10s linear infinite;
            text-align: center;
        }

        /* Keyframes for Diagonal Scrolling */
        @keyframes scroll-diagonal {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 64px 64px;
            }
        }

        h1 {
            color: #333;
            /* Purple headings */
            text-align: center;
            background-color: white;
            border-radius: 10px;
            width: 250px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            color: #333;
            background-color: white;
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
            background-color: #4500b5;
            /* Purple header */
            color: white;
        }

        tr:nth-child(even) {
            background-color: white;
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

        /* Button Styles with Animation */
        .btn {
            padding: 0.5em 2em;
            background: none;
            border: 2px solid #fff;
            font-size: 15px;
            color: #131313;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            border-radius: 12px;
            background-color: #4500b5;
            font-weight: bolder;
            box-shadow: 0 2px 0 2px #000;
            width: 250px;
            margin: 10px;
            text-decoration: none;
        }

        .btn:before {
            content: "";
            position: absolute;
            width: 100px;
            height: 120%;
            background-color: #ff6700;
            top: 50%;
            transform: skewX(30deg) translate(-110%, -50%);
            transition: all 0.5s;
        }

        .btn:hover {
            background-color: #4500b5;
            color: #fff;
            box-shadow: 0 2px 0 2px #0d3b66;
        }

        .btn:hover::before {
            transform: skewX(30deg) translate(110%, -50%);
            transition-delay: 0.1s;
        }

        .btn:active {
            transform: scale(0.9);
        }

        /* Button Styles with Animation */
        .back-btn {
            padding: 0.5em 2em;
            background: none;
            border: 2px solid #fff;
            font-size: 15px;
            color: #131313;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            border-radius: 12px;
            background-color: #4500b5;
            font-weight: bolder;
            box-shadow: 0 2px 0 2px #000;
            width: 250px;
            margin: 10px;
            text-decoration: none;
        }

        .back-btn:before {
            content: "";
            position: absolute;
            width: 100px;
            height: 120%;
            background-color: #ff6700;
            top: 50%;
            transform: skewX(30deg) translate(-110%, -50%);
            transition: all 0.5s;
        }

        .back-btn:hover {
            background-color: #4500b5;
            color: #fff;
            box-shadow: 0 2px 0 2px #0d3b66;
        }

        .back-btn:hover::before {
            transform: skewX(30deg) translate(160%, -50%);
            transition-delay: 0.1s;
        }

        .back-btn:active {
            transform: scale(0.9);
        }
    </style>
</head>

<body>
    <a href="seller_dashboard.php"><button class="back-btn">Back to Dashboard</button></a>
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
                                <button type='submit 'class='back-btn' name='mark_completed'>Mark as Completed</button>
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