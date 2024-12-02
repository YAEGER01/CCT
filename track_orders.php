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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $order_id = intval($_POST['order_id']); // Get the order ID

        // Fetch the order details first, including rice and drink options
        $orderQuery = "SELECT * FROM orders WHERE id = $order_id";
        $orderResult = mysqli_query($conn, $orderQuery);

        if (!$orderResult) {
            die("Error fetching order: " . mysqli_error($conn));
        }

        $order = mysqli_fetch_assoc($orderResult);

        if ($order) {
            $rice_option = $order['rice_option'];
            $rice_price = $order['rice_price'];
            $drink_option = $order['drinks'];
            $drink_price = $order['drinks_price'];
            $total_price = $order['price'] * $order['quantity'] + $rice_price + $drink_price;

            if ($_POST['action'] === 'accept') {
                // Insert into accepted_orders table
                $insertQuery = "INSERT INTO accepted_orders (order_id, user_id, meal_id, quantity, status, price, rice_option, rice_price, drinks, drinks_price) 
                                VALUES ($order_id, {$order['user_id']}, {$order['meal_id']}, {$order['quantity']}, 'accepted', {$order['price']}, 
                                        '$rice_option', $rice_price, '$drink_option', $drink_price)";

                if (!mysqli_query($conn, $insertQuery)) {
                    die("Error inserting into accepted_orders: " . mysqli_error($conn));
                }

                // Delete the order from orders table
                $deleteQuery = "DELETE FROM orders WHERE id = $order_id";
                mysqli_query($conn, $deleteQuery);

                header("Location: track_orders.php");
                exit();
            } elseif ($_POST['action'] === 'decline') {
                // Insert into transactions table with additional fields
                // Use $seller_id from the session
                $insertQuery = "INSERT INTO transactions (order_id, user_id, meal_id, quantity, total_price, seller_id, transaction_date, rice_option, rice_price, drinks, drinks_price)
                                VALUES ($order_id, {$order['user_id']}, {$order['meal_id']}, {$order['quantity']}, $total_price, $seller_id, NOW(), '$rice_option', $rice_price, '$drink_option', $drink_price)";

                if (!mysqli_query($conn, $insertQuery)) {
                    die("Error inserting into transactions: " . mysqli_error($conn));
                }

                // Delete the order from orders table
                $deleteQuery = "DELETE FROM orders WHERE id = $order_id";
                mysqli_query($conn, $deleteQuery);

                header("Location: track_orders.php");
                exit();
            }
        }
    }
}

// Fetch orders made to the seller
$orderQuery = "
    SELECT 
        o.id AS order_id, 
        o.status, 
        m.meal_name AS meal_name, 
        o.quantity, 
        m.price, 
        u.username AS customer_name,
        o.rice_option, 
        o.rice_price, 
        o.drinks, 
        o.drinks_price
    FROM orders o
    JOIN meals m ON o.meal_id = m.id
    JOIN users u ON o.user_id = u.id
    WHERE m.seller_id = ? AND o.status = 'pending'
    ORDER BY o.id DESC";

$stmt = $conn->prepare($orderQuery);
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
            background-color: #F2F2F2;
            /* Grayish-black background */
            color: white;
            /* White text for contrast */

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
            margin: 20px;
        }

        @media (max-width: 768px) {
            .header {
                width: 80%;
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

        .order-container {

            margin: 20px;
            backdrop-filter: blur(3px);
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 35px 35px 68px 0px rgba(198, 198, 198, 0.5), inset -6px -6px 16px 0px rgba(198, 198, 198, 0.6), inset 0px 11px 28px 0px rgb(255, 255, 255);
        }

        .order-container p {
            color: #000;
        }

        .order {
            background-color: #ffffff;
            color: #333;
            /* Dark background for orders */
            border: 1px solid #444;
            /* Slightly lighter gray for border */
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 12px;
            /* Rounded corners for orders */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            /* Subtle shadow for depth */
        }

        .order h3 {
            color: #6a0dad;
            /* Purple for meal names */
        }

        .button {
            padding: 10px;
            background-color: #6a0dad;
            /* Purple buttons */
            color: white;
            border: none;
            border-radius: 8px;
            /* Rounded corners */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button.accept {
            background-color: #28a745;
            /* Green for accept button */
        }

        .button.decline {
            background-color: #dc3545;
            /* Red for decline button */
        }

        .button:hover {
            opacity: 0.9;
        }

        button:focus {
            outline: none;
            box-shadow: 0 0 10px #6a0dad;
            /* Purple glow on focus */
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

        .more-info .details {
            background-color: #f7f7f7;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .see-more-btn {
            background-color: transparent;
            color: black;
            border: 0.5px solid #000;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        .see-more-btn:hover {
            background-color: #333;
            color: white;
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

    <div class="order-container">
        <?php if ($orderResult->num_rows > 0): ?>
            <?php while ($order = $orderResult->fetch_assoc()): ?>
                <div class="order">
                    <h3>Meal: <?php echo htmlspecialchars($order['meal_name']); ?></h3>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                    <div class="more-info">
                        <button class="see-more-btn">See More</button>
                        <div class="details" style="display: none;">
                            <p><strong>Rice Option:</strong> <?php echo htmlspecialchars($order['rice_option'] ?? 'None'); ?>
                            </p>
                            <p><strong>Drink Option:</strong> <?php echo htmlspecialchars($order['drinks'] ?? 'None'); ?></p>
                            <p><strong>Rice Price:</strong> ₱<?php echo htmlspecialchars($order['rice_price'] ?? '0'); ?></p>
                            <p><strong>Drink Price:</strong> ₱<?php echo htmlspecialchars($order['drinks_price'] ?? '0'); ?></p>
                            <p><strong>Total Price:</strong>
                                ₱<?php echo htmlspecialchars(($order['price'] * $order['quantity']) + ($order['rice_price'] ?? 0) + ($order['drinks_price'] ?? 0)); ?>
                            </p>

                        </div>
                    </div>
                    <!-- Action buttons for accepting or declining orders -->
                    <?php if ($order['status'] === 'pending'): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <button type="submit" name="action" value="accept" class="button accept btn">Accept Order</button>
                            <button type="submit" name="action" value="decline" class="button decline btn">Decline Order</button>
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

    <script>
        // JavaScript for the "See More" feature
        document.querySelectorAll('.see-more-btn').forEach(button => {
            button.addEventListener('click', () => {
                const details = button.nextElementSibling;
                if (details.style.display === 'none' || details.style.display === '') {
                    details.style.display = 'block';
                    button.textContent = 'See Less';
                } else {
                    details.style.display = 'none';
                    button.textContent = 'See More';
                }
            });
        });
    </script>

</body>

</html>