<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if (isset($_POST['delete_meal'])) {
    $meal_id = $_POST['meal_id'];
    $delete_query = "DELETE FROM meals WHERE id = '$meal_id' AND seller_id = {$_SESSION['user_id']}";
    mysqli_query($conn, $delete_query);
    header("Location: seller_dashboard.php");
    exit();
}

// Fetch orders made to the seller
$orderQuery = "
    SELECT o.id AS order_id, o.status, m.meal_name AS meal_name, o.quantity, m.price, o.rice_option, o.drinks, u.username AS customer_name 
    FROM orders o
    JOIN meals m ON o.meal_id = m.id
    JOIN users u ON o.user_id = u.id
    WHERE m.seller_id = ? AND o.status = 'pending'
    ORDER BY o.id DESC";

$stmt = $conn->prepare($orderQuery);
$seller_id = $_SESSION['user_id']; // Make sure to set the seller_id correctly
$stmt->bind_param('i', $seller_id);
$stmt->execute();
$orderResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
    <style>
        @font-face {
            font-family: 'MyCustomFont1';
            /* Give your font a name */
            src: url('fonts/nexa/Nexa-ExtraLight.ttf') format('truetype');
            /* Path to the TTF file */
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'MyCustomFont2';
            /* Give your font a name */
            src: url('fonts/nexa/Nexa-Heavy.ttf') format('truetype');
            /* Path to the TTF file */
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #F2F2F2;
        }

        .content-container {
            display: flex;
            flex-direction: column;
            /* Stack elements vertically */
            padding: 20px;
            max-width: 1600px;
            margin: auto;
            border-radius: 20px;
            gap: 20px;
            /* Background Style */
            background: radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 0% 0% / 64px 64px,
                radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 32px 32px / 64px 64px,
                linear-gradient(#4500b5 2px, transparent 2px) 0px -1px / 32px 32px,
                linear-gradient(90deg, #4500b5 2px, #ffffff 2px) -1px 0px / 32px 32px #ffffff;
            background-size: 64px 64px, 64px 64px, 32px 32px, 32px 32px;
            background-color: #ffffff;
            animation: scroll-diagonal 10s linear infinite;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 600px) {
            .content-container {
                width: 80%;
                margin: 20px;
            }
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

        .meal_section,
        .messages-sidebar {
            flex-grow: 1;
        }

        @media (min-width: 769px) {
            .content-container {
                flex-direction: row;
                /* Aligns elements side-by-side on larger screens */
                align-items: flex-start;
            }
        }



        .meal_section {
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            flex: 3;
        }

        .meal_section h2 {
            font-family: 'MyCustomFont2';
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .meal-container {

            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            /* Align items to the start */
            gap: 20px;
            /* Add gap between meal items */
        }

        .meal {
            width: 260px;
            background-color: #f8f8f8;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease;
        }

        .meal p {
            font-family: 'MyCustomFont1';
        }

        .meal:hover {
            transform: scale(1.03);
        }

        .meal h3 {
            font-family: 'MyCustomFont2';
            font-size: 20px;
            color: #555;
            margin: 0 0 8px 0;
            /* Adjusted margin */
        }

        .meal img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            align-self: center;
        }

        .description-box {
            max-height: 80px;
            overflow-y: auto;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f0f0f0;
            border-radius: 6px;
            word-break: break-word;
        }

        .description-box p {
            font-family: 'MyCustomFont1';
            margin: 0;
            color: #666;
            line-height: 1.4;
        }

        .meal-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .meal-actions button {
            background-color: #6200ea;
            color: #fff;
            border: none;
            padding: 8px 12px;
            /* Increased padding for buttons */
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .meal-actions button:hover {
            background-color: #4500b5;
        }

        @media (max-width: 768px) {
            .meal {
                width: 100%;
            }
        }

        .orders-sidebar {
            flex: 1;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-height: 850px;
            overflow-y: auto;
        }

        .orders-sidebar h2 {
            margin: 0 0 15px;
            /* Adjusted margin */
        }

        .orders-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .orders-item:last-child {
            border-bottom: none;
        }

        .orders strong {
            display: block;
            margin-bottom: 5px;
            color: #6200ea;
        }

        .orders p {
            margin: 0;
            color: #555;
        }

        .message time {
            font-size: 12px;
            color: #999;
        }

        /* Base styles */

        .mobile-only {
            display: none;
            /* Hide by default */
        }

        .desktop-only {
            display: block;
            /* Show by default */
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
            background-color: #d056ef;
            font-weight: bolder;
            box-shadow: 0 2px 0 2px #000;
            width: 100px;
            margin: 10px;
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
            transform: skewX(30deg) translate(80%, -50%);
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

    <div class="content-container">
        <div class="meal_section">
            <h2>Your Uploaded Meals</h2>
            <div class="meal-container">
                <?php
                // Fetch meals from the database
                $meals_query = "SELECT * FROM meals WHERE seller_id = {$_SESSION['user_id']}";
                $meals_result = mysqli_query($conn, $meals_query);

                while ($meal = mysqli_fetch_assoc($meals_result)): ?>
                    <div class="meal">
                        <h3><?php echo htmlspecialchars($meal['meal_name']); ?></h3>
                        <hr style="border: 1px solid #333; width: 80%;">
                        <?php if (!empty($meal['image'])): ?>
                            <img src="<?php echo htmlspecialchars($meal['image']); ?>" alt="Meal Image"
                                style="width:100px;height:100px;object-fit:cover;">
                        <?php endif; ?>
                        <br>
                        <p>Description: </p>
                        <div class="description-box">
                            <p><?php echo htmlspecialchars($meal['description']); ?></p>
                        </div>

                        <!-- Display Rice Option -->
                        <?php if (!empty($meal['rice_options'])): ?>
                            <p><strong>Rice Option:</strong> <?php echo htmlspecialchars($meal['rice_options']); ?></p>
                        <?php endif; ?>

                        <!-- Display Drink Option -->
                        <?php if (!empty($meal['drinks'])): ?>
                            <p><strong>Drink Option:</strong> <?php echo htmlspecialchars($meal['drinks']); ?></p>
                        <?php endif; ?>
                        <p><strong>Price:</strong> ₱<?php echo htmlspecialchars($meal['price']); ?></p>
                        <div class="meal-actions">
                            <form method="POST" action="seller_dashboard.php" style="display:inline;">
                                <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
                                <button class="btn" type="submit" name="delete_meal"
                                    onclick="return confirm('Are you sure you want to delete this meal?')">Delete</button>
                            </form>
                            <form method="GET" action="edit_meal.php" style="display:inline;">
                                <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
                                <button class="btn" type="submit">Edit</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="orders-sidebar desktop-only">
            <h2>Orders Preview</h2>
            <div class="orders-list">
                <?php while ($order = $orderResult->fetch_assoc()): ?>
                    <div class="orders-item">

                        <p><strong>Meal:</strong> <?php echo htmlspecialchars($order['meal_name']); ?></p>
                        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
                        <p><strong>Rice:</strong> <?php echo htmlspecialchars($order['rice_option']); ?></p>
                        <p><strong>Drinks:</strong> <?php echo htmlspecialchars($order['drinks']); ?></p>
                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

</body>

</html>