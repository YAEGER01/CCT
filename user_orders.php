<?php
session_start();
include 'db.php';

// Debugging session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo "Session is invalid or expired.";
    var_dump($_SESSION); // Debugging session details
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get user's ID

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        $order_id = intval($_POST['order_id']); // Get the order ID

        // Fetch order details along with seller_id from the meals table
        $orderQuery = "
            SELECT o.*, m.seller_id, m.price AS meal_price 
            FROM orders o
            JOIN meals m ON o.meal_id = m.id
            WHERE o.id = $order_id AND o.user_id = $user_id";

        $orderResult = mysqli_query($conn, $orderQuery);

        if (!$orderResult || mysqli_num_rows($orderResult) === 0) {
            die("Error fetching order: " . mysqli_error($conn));
        }

        $order = mysqli_fetch_assoc($orderResult);

        // Prepare data for transactions table
        $meal_id = $order['meal_id'];
        $seller_id = $order['seller_id'];
        $quantity = $order['quantity'];
        $rice_option = $order['rice_option'];
        $rice_price = $order['rice_price'];
        $drink_option = $order['drinks'];
        $drink_price = $order['drinks_price'];
        $meal_price = $order['meal_price'];
        $total_price = ($meal_price * $quantity) + $rice_price + $drink_price;

        // Insert into transactions table
        $transactionQuery = "
            INSERT INTO transactions (user_id, seller_id, order_id, meal_id, quantity, rice_option, rice_price, drinks, drinks_price, total_price, transaction_date)
            VALUES ($user_id, $seller_id, $order_id, $meal_id, $quantity, 
                    '$rice_option', $rice_price, '$drink_option', $drink_price, $total_price, NOW())";

        if (!mysqli_query($conn, $transactionQuery)) {
            die("Error inserting into transactions: " . mysqli_error($conn));
        }

        // Delete the order from orders table
        $deleteQuery = "DELETE FROM orders WHERE id = $order_id AND user_id = $user_id";
        if (!mysqli_query($conn, $deleteQuery)) {
            die("Error deleting order: " . mysqli_error($conn));
        }

        // Check if deletion was successful
        if (mysqli_affected_rows($conn) > 0) {
            header("Location: user_orders.php");
            exit();
        } else {
            echo "Error canceling order. Please try again.";
        }

        header("Location: user_orders.php"); // Redirect back to the orders page
        exit();
    }
}

// Fetch orders made by the user
$orderQuery = "
    SELECT o.id AS order_id, o.status, m.meal_name AS meal_name, o.quantity, m.price, o.rice_option, o.drinks 
    FROM orders o
    JOIN meals m ON o.meal_id = m.id
    WHERE o.user_id = ? AND o.status = 'pending'
    ORDER BY o.id DESC";

$stmt = $conn->prepare($orderQuery);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param('i', $user_id);

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$orderResult = $stmt->get_result();
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
            /* Grayish-black background */
            color: white;
            /* White text for contrast */
            /* Background Style */
            background: radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 0% 0% / 64px 64px,
                radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 32px 32px / 64px 64px,
                linear-gradient(#a43fc6 2px, transparent 2px) 0px -1px / 32px 32px,
                linear-gradient(90deg, #a43fc6 2px, #ffffff 2px) -1px 0px / 32px 32px #ffffff;
            background-size: 64px 64px, 64px 64px, 32px 32px, 32px 32px;
            background-color: #ffffff;
            animation: scroll-diagonal 10s linear infinite;

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


        .header a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .header a:hover {
            color: #ddd;
            /* Lighter shade on hover */
        }

        .order-container {
            margin: 20px;
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
                width: 80vw;
            }
        }

        .header h2 {
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 24px;
            margin: 0;
            color: #d056ef;
            /* Accent color */
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
        <div class="site_name">
            <h2>You Chews</h2>
            <p>IKAW BAHALA</p>
        </div>
        <div class="nav-dropdown">
            <select id="options-dropdown" onchange="navigateToPage(this)">
                <option style="display: none" value="">Options</option>
                <option value="user_dashboard.php">Home</option>
                <option value="cart.php">Cart</option>
                <option value="user_orders.php">My Orders</option>
                <option value="user_transacts.php">Transactions</option>
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
                    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
                    <p><strong>Rice Option:</strong> <?php echo htmlspecialchars($order['rice_option']); ?></p>
                    <p><strong>Drink:</strong> <?php echo htmlspecialchars($order['drinks']); ?></p>
                    <p><strong>Total Price:</strong> ₱<?php echo htmlspecialchars($order['price'] * $order['quantity']); ?></p>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>

                    <form method="POST" action="">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <?php if (isset($_POST['action']) && $_POST['action'] === 'cancel'): ?>
                        <?php else: ?>
                            <button type="submit" name="action" value="cancel" class="button cancel">Cancel Order</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>

</body>

</html>