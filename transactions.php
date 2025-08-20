<?php
session_start();
include 'db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Fetch seller username
$stmtUser = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmtUser->bind_param('i', $seller_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$seller = $resultUser->fetch_assoc();
$username = $seller ? $seller['username'] : 'Unknown';

// Fetch transactions for the seller
$transactionQuery = "
    SELECT 
        t.transaction_date, 
        u.username AS customer_name, 
        m.meal_name AS meal_name, 
        t.quantity, 
        t.total_price
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN meals m ON t.meal_id = m.meal_id
    WHERE t.seller_id = ?
    ORDER BY t.transaction_date DESC
";

$stmt = $conn->prepare($transactionQuery);
$stmt->bind_param('i', $seller_id);
$stmt->execute();
$transactionResult = $stmt->get_result();

// Fetch transactions into an array
$transactions = $transactionResult->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Transactions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h2 {
            color: #6a0dad;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #555;
        }

        th {
            background-color: #4500b5;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #b792f2;
        }

        .tbl-container {
            border-radius: 20px;
            overflow-x: auto;
            white-space: nowrap;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .nav-dropdown select {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
    <script>
        function navigateToPage(selectElement) {
            const val = selectElement.value;
            if (val) window.location.href = val;
        }
    </script>
</head>

<body>
    <div class="header">
        <h2>Store username: <?php echo htmlspecialchars($username); ?></h2>
        <div class="nav-dropdown">
            <select onchange="navigateToPage(this)">
                <option value="">Options</option>
                <option value="seller_dashboard.php">Home</option>
                <option value="meal_upload.php">Upload Meal</option>
                <option value="track_orders.php">Orders</option>
                <option value="pending_orders.php">Accepted Orders</option>
                <option value="transactions.php">Transactions</option>
                <option value="user_edit.php">Edit User</option>
                <option value="logout.php">Logout</option>
            </select>
        </div>
    </div>
    <div class="tbl-container">
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
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['transaction_date']); ?></td>
                            <td><?php echo htmlspecialchars($t['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($t['meal_name']); ?></td>
                            <td><?php echo htmlspecialchars($t['quantity']); ?></td>
                            <td>₱<?php echo htmlspecialchars($t['total_price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>