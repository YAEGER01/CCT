<?php
session_start();
include 'db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php"); // Redirect to login if not seller
    exit();
}

// Fetch seller username
$username = $_SESSION['username'];
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
            font-family: Arial, sans-serif;
            background-color: #2b2b2b;
            /* Grayish-black background */
            color: white;
            /* White text for contrast */
        }

        .header {
            background-color: #6a0dad;
            /* Purple header */
            color: white;
            padding: 15px;
            text-align: center;
            border-bottom: 5px solid #4b0082;
            /* Darker purple border */
            border-radius: 0 0 15px 15px;
            /* Rounded bottom corners */
        }

        .nav-dropdown {
            background-color: #6a0dad;
            /* Purple dropdown */
            color: white;
            padding: 10px;
            border-radius: 5px;
            border: none;
            font-size: 16px;
            cursor: pointer;
        }

        .nav-dropdown select {
            background-color: #6a0dad;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
        }

        .logout {
            background-color: red;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            float: right;
            transition: background-color 0.3s ease;
        }

        .logout:hover {
            background-color: darkred;
        }

        .section {
            margin: 20px;
            background-color: #333;
            /* Dark gray background */
            padding: 20px;
            border-radius: 15px;
            /* Rounded corners for sections */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            /* Subtle shadow for depth */
        }

        .meal {
            background-color: #444;
            /* Dark background for meal items */
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 12px;
            /* Rounded corners for meal items */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            /* Subtle shadow for depth */
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
    </style>
</head>

<body>

    <!-- Header with dropdown for navigation -->
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>

        <!-- Dropdown Menu for Navigation -->
        <div class="nav-dropdown">
            <select onchange="navigateToPage(this)">
                <option value="">OPTIONS</option>
                <option value="meal_upload.php">Upload Meal</option>
                <option value="track_orders.php">Orders</option>

                <option value="pending_orders.php">Accepted Orders</option>
                <option value="transactions.php">Transactions</option>
                <option value="user_edit.php">Edit User</option>
                <option value="logout.php">Logout</option>
            </select>
        </div>

    </div>

    <!-- JavaScript to redirect based on dropdown selection -->
    <script>
        function navigateToPage(select) {
            const value = select.value;
            if (value) {
                window.location.href = value; // Redirect to the selected page
            }
        }
    </script>

    <!-- Display uploaded meals (this section remains the same as before) -->
    <div class="section">
        <h2>Your Uploaded Meals</h2>
        <?php
        $meals_query = "SELECT * FROM meals WHERE seller_id = {$_SESSION['user_id']}";
        $meals_result = mysqli_query($conn, $meals_query);

        while ($meal = mysqli_fetch_assoc($meals_result)): ?>
            <div class="meal">
                <h3><?php echo htmlspecialchars($meal['name']); ?></h3>
                <p><?php echo htmlspecialchars($meal['description']); ?></p>
                <p><strong>Price:</strong> ₱<?php echo htmlspecialchars($meal['price']); ?></p>
                <?php if (!empty($meal['image'])): ?>
                    <img src="<?php echo htmlspecialchars($meal['image']); ?>" alt="Meal Image" style="width:100px;height:100px;object-fit:cover;">
                <?php endif; ?>

                <!-- Meal Actions (Delete/Edit) -->
                <div class="meal-actions">
                    <form method="POST" action="seller_dashboard.php" style="display:inline;">
                        <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
                        <button type="submit" name="delete_meal">Delete Meal</button>
                    </form>
                    <form method="GET" action="edit_meal.php" style="display:inline;">
                        <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
                        <button type="submit">Edit Meal</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</body>

</html>