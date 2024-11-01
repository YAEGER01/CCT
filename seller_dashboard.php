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
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #F2F2F2;
        }

        .header {
            background-color: #ffffff;
            color: black;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .nav-dropdown {
            position: relative;
        }

        .nav-dropdown select {
            border-radius: 10px;
            padding: 10px;
            font-size: 16px;
            background-color: #6200ea;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            appearance: none;
            background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"%3E%3Cpath fill="white" d="M7 10l5 5 5-5H7z"/%3E%3C/svg%3E');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
        }

        .nav-dropdown select:hover {
            background-color: #4500b5;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header h1 {
                display: none;
            }

            .nav-dropdown {
                width: 100%;
            }

            .nav-dropdown select {
                width: 100%;
                padding: 15px;
            }
        }

        .content-container {
            display: flex;
            flex-direction: column;
            /* Stack elements vertically */
            padding: 20px;
            max-width: 1600px;
            margin: auto;
            gap: 20px;
            outline: 1px dashed #333;
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

        .meal:hover {
            transform: scale(1.03);
        }

        .meal h3 {
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

        /* Media query for mobile */
        @media (max-width: 768px) {
            .mobile-only {
                display: block;
                /* Show on mobile */
            }

            .desktop-only {
                display: none;
                /* Hide on mobile */
            }

            .orders-sidebar {
                display: none;
                /* Hide messages sidebar on mobile */
            }
        }

        /* Media query for desktop */
        @media (min-width: 769px) {
            .mobile-only {
                display: none;
                /* Hide on desktop */
            }

            .orders-sidebar {
                display: block;
                /* Show sidebar on desktop */
            }
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
        <h1 class="welcome-message desktop-only">Welcome, <?php echo htmlspecialchars($username); ?></h1>
        <div class="nav-dropdown">
            <select id="options-dropdown" onchange="navigateToPage(this)">
                <option value="">OPTIONS</option>
                <option value="meal_upload.php">Upload Meal</option>
                <option value="track_orders.php">Orders</option>
                <option value="pending_orders.php">Accepted Orders</option>
                <option value="messages.php" class="mobile-only">Messages</option> <!-- Messages link for mobile -->
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
                                <button type="submit" name="delete_meal"
                                    onclick="return confirm('Are you sure you want to delete this meal?')">Delete
                                    Meal</button>
                            </form>
                            <form method="GET" action="edit_meal.php" style="display:inline;">
                                <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
                                <button type="submit">Edit Meal</button>
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