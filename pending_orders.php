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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Pending Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F2F2F2;
            /* Grayish-black background */
            color: white;
            /* White text for contrast */
            margin: 0;
            padding: 10px;
        }


        table {
            width: 98.5%;
            border-collapse: collapse;
            margin-left: 15px;
            margin-right: 10px;
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

        @media (max-width: 600px) {
            table {
                width: 70vw;
                margin-left: 15px;
            }
        }

        th,
        td {
            padding: 12px;
            text-align: center;
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
            background-color: #ffffff;
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

        .modal-content {
            background-color: white;
            /* Match your table's background */
            border-radius: 10px;
            /* Rounded corners */
            color: #333;
            /* Dark text for contrast */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            /* Shadow for depth */
            padding: 20px;
        }

        .modal-header {
            background-color: #4500b5;
            /* Purple header */
            color: white;
            border-bottom: none;
            /* Remove default border */
            border-radius: 10px 10px 0 0;
            text-align: center;
        }

        .modal-footer {
            border-top: none;
            /* Remove default border */
            text-align: center;
        }

        .modal-body p {
            margin: 10px 0;
            /* Spacing between paragraphs */
            font-size: 14px;
        }

        @media (max-width: 768px) {
            table {
                font-size: 14px;
                /* Slightly smaller text for the table */
            }

            button {
                padding: 8px;
                /* Smaller padding for buttons */
                font-size: 12px;
                /* Reduced font size */
            }

            .back-btn {
                width: auto;
                /* Allow buttons to adjust width dynamically */
                padding: 5px 10px;
                /* Compact padding */
                font-size: 12px;
            }

            th,
            td {
                padding: 8px;
                /* Reduce padding in table cells */
            }

            .modal-content {
                font-size: 14px;
                /* Smaller text inside modals */
            }
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
            width: 97.5vw;
            border-radius: 15px;
            margin: 15px;
        }

        @media (max-width: 768px) {
            .header {
                width: 93%;
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
                    $modalId = "orderModal" . $order['id']; // Unique modal ID
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($order['user_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['meal_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['quantity']) . "</td>";
                    echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                    echo "<td>
                    <button type='button' class='back-btn' data-bs-toggle='modal' data-bs-target='#$modalId'>See More</button>
                    <form method='POST' action='pending_orders.php' style='display:inline;'>
                        <input type='hidden' name='order_id' value='" . $order['id'] . "'>
                        <button type='submit' class='back-btn' name='mark_completed'>Mark as Completed</button>
                    </form>
                  </td>";
                    echo "</tr>";

                    // Modal for showing detailed information
                    echo "
            <div class='modal fade' id='$modalId' tabindex='-1' aria-labelledby='{$modalId}Label' aria-hidden='true'>
                <div class='modal-dialog modal-dialog-centered'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title' id='{$modalId}Label'>Order Details</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <div class='modal-body'>
                            <p><strong>Meal Name:</strong> " . htmlspecialchars($order['meal_name']) . "</p>
                            <p><strong>Quantity:</strong> " . htmlspecialchars($order['quantity']) . "</p>
                            <p><strong>Rice Option:</strong> " . htmlspecialchars($order['rice_option']) . "</p>
                            <p><strong>Rice Price:</strong> ₱" . number_format($order['rice_price'], 2) . "</p>
                            <p><strong>Drinks:</strong> " . htmlspecialchars($order['drinks']) . "</p>
                            <p><strong>Drinks Price:</strong> ₱" . number_format($order['drinks_price'], 2) . "</p>
                            <p><strong>Total Price:</strong> ₱" . number_format($order['quantity'] * ($order['price'] + $order['rice_price'] + $order['drinks_price']), 2) . "</p>
                        </div>
                        
                    </div>
                </div>
            </div>";
                }
            } else {
                echo "<tr><td colspan='5'>No pending orders.</td></tr>";
            }
            ?>
        </tbody>


    </table>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>