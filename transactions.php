<?php
session_start();
include 'db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Fetch transactions for the seller
$transactionQuery = "SELECT t.transaction_date, u.username AS customer_name, m.name AS meal_name, t.quantity, t.total_price
                     FROM transactions t
                     JOIN users u ON t.user_id = u.id
                     JOIN meals m ON t.meal_id = m.id
                     WHERE t.seller_id = $seller_id
                     ORDER BY t.transaction_date DESC";
$transactionResult = mysqli_query($conn, $transactionQuery);

// Fetch transactions into an array
$transactions = mysqli_fetch_all($transactionResult, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Transactions</title>
    <style>
        /* Add your styles here */
    </style>
</head>
<body>
    <a href="seller_dashboard.php">Dashboard</a>
    <h1>Recent Transactions</h1>
    <table>
        <thead>
            <tr>
                <th>Transaction Date</th>
                <th>Customer Name</th>
                <th>Meal Name</th>
                <th>Quantity</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($transactions)) {
                foreach ($transactions as $transaction) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($transaction['transaction_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($transaction['customer_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($transaction['meal_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($transaction['quantity']) . "</td>";
                    echo "<td>$" . htmlspecialchars($transaction['total_price']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No transactions found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
