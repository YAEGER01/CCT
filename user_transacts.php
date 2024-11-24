<?php
session_start();
include 'db.php';

// Check if user is logged in as a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch transactions for the buyer
$transactionQuery = "SELECT t.transaction_date, s.username AS seller_name, m.meal_name AS meal_name, t.quantity, t.total_price
                     FROM transactions t
                     JOIN users s ON t.seller_id = s.id
                     JOIN meals m ON t.meal_id = m.id
                     WHERE t.user_id = $user_id
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
    <title>My Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #2b2b2b;
            color: white;
            margin: 0;
            padding: 20px;
            background: radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 0% 0% / 64px 64px,
                radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 32px 32px / 64px 64px,
                linear-gradient(#a43fc6 2px, transparent 2px) 0px -1px / 32px 32px,
                linear-gradient(90deg, #a43fc6 2px, #ffffff 2px) -1px 0px / 32px 32px #ffffff;
            background-size: 64px 64px, 64px 64px, 32px 32px, 32px 32px;
            background-color: #ffffff;
            animation: scroll-diagonal 10s linear infinite;
        }

        @keyframes scroll-diagonal {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 64px 64px;
            }
        }

        h1 {
            color: #6a0dad;
            text-align: center;
            background-color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #ffffff;
            color: #333;
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
            background-color: #6a0dad;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #ffffff;
        }

        tr:hover {
            background-color: #b792f2;
        }

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
    <a href="user_dashboard.php"><button class="back-btn">Back to Dashboard</button></a>
    <h1>My Recent Orders</h1>
    <table>
        <thead>
            <tr>
                <th>Transaction Date</th>
                <th>Seller Name</th>
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
                    echo "<td>" . htmlspecialchars($transaction['seller_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($transaction['meal_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($transaction['quantity']) . "</td>";
                    echo "<td>₱" . htmlspecialchars($transaction['total_price']) . "</td>";
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