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
$transactionQuery = "SELECT t.transaction_date, u.username AS customer_name, m.meal_name AS meal_name, t.quantity, t.total_price
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            /* Grayish-black background */
            color: white;
            /* White text for contrast */
            margin: 0;
            padding: 20px;
            /* Background Style */
        }

        h1 {
            color: #6a0dad;
            /* Purple headings */
            text-align: center;
            background-color: white;
        }

        .tbl-container {
            border-radius: 20px;
            overflow-x: auto;
            /* Enable horizontal scrolling */
            white-space: nowrap;
            /* Prevent text wrapping */

        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #ffffff;
            color: #333;
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
            background-color: #ffffff;
            /* Darker row background */
        }

        tr:hover {
            background-color: #b792f2;
            /* Hover effect for rows */
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

        /* Header */
        .header {
            background-color: #ffffff;
            /* Light gray */
            color: #333;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 95vw;
            border-radius: 15px;
            margin-top: 15px;
            margin-left: 5px;
        }

        @media (max-width: 768px) {
            .header {
                width: 90%;
            }
        }

        .header h2 {
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 24px;
            margin: 0;
            color: #d056ef;
            /* Accent color */
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .header h2 {
                font-size: 15px;
            }
        }

        .header p {
            font-family: 'MyCustomFont1', sans-serif;
            font-size: 12px;
            font-weight: 690;
            color: #555;
            text-align: center;
        }

        .nav-dropdown {
            position: relative;
            display: inline-block;
        }

        #options-dropdown {
            appearance: none;
            /* Remove default appearance */
            -webkit-appearance: none;
            /* For Safari */
            -moz-appearance: none;
            /* For Firefox */
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #f2f2f2;
            color: #333;
            border-radius: 8px;
            cursor: pointer;
        }

        #options-dropdown:focus {
            outline: none;
            box-shadow: 0 0 0 2px #007bff;
        }

        #options-dropdown option {
            padding: 10px;
            font-size: 16px;

        }
    </style>
    <script>
        function navigateToPage(selectElement) {
            const selectedValue = selectElement.value;
            if (selectedValue) {
                window.location.href = selectedValue;
            }
        }
    </script>
</head>

<body>
    <div class="header">
        <h2 class="welcome-message desktop-only">Store username: <?php echo htmlspecialchars($username); ?></h2>
        <div class="nav-dropdown">
            <select id="options-dropdown" onchange="navigateToPage(this)">
                <option value="" style="display:none">Options</option>
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
                <?php
                if (!empty($transactions)) {
                    foreach ($transactions as $transaction) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($transaction['transaction_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($transaction['customer_name']) . "</td>";
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
    </div>
</body>

</html>