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

// Handle meal deletion
if (isset($_POST['delete_meal'])) {
    $meal_id = $_POST['meal_id'];
    $delete_query = "DELETE FROM meals WHERE id = '$meal_id' AND seller_id = {$_SESSION['user_id']}";
    mysqli_query($conn, $delete_query);
    header("Location: seller_dashboard.php"); // Refresh the page after deletion
    exit();
}
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
        }

        .nav-dropdown select {
            border-radius: 10px;
            padding: 10px;
            font-size: 16px;
            background-color: #333;
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }

        .nav-dropdown select:hover {
            background-color: #555;
        }

        .section {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .meal {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .meal img {
            margin-right: 20px;
            border-radius: 10px;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        .meal h3 {
            margin: 0 0 10px;
        }

        .meal-actions {
            display: flex;
            gap: 10px;
        }

        .meal-actions button {
            padding: 8px 16px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .meal-actions button:hover {
            background-color: #555;
        }

        .meal:hover {
            transform: translateY(-5px);
        }

        @media (max-width: 768px) {
            .meal {
                flex-direction: column;
                align-items: flex-start;
            }

            .meal img {
                margin-bottom: 10px;
            }
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