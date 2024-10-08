<?php
// Start session and include database connection
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch the username from the session
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id']; // User ID for potential future use

// Query to get distinct sellers (stores) from the database
$sql = "SELECT DISTINCT u.id, u.username FROM users u WHERE u.role = 'seller'";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .logout {
            float: right;
            margin-top: -35px;
            margin-right: 15px;
            background-color: red;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
        }
        .store-container {
            margin: 20px;
        }
        .store {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .view-meals {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <a href="logout.php" class="logout">Logout</a>
        <a href="cart.php" class="logout">Cart</a>
    </div>

    <!-- Stores Section -->
    <div class="store-container">
        <h2>Available Stores</h2>
        <?php
        if ($result->num_rows > 0) {
            // Display stores
            while ($store = $result->fetch_assoc()) {
                echo "<div class='store'>";
                echo "<h3>" . htmlspecialchars($store['username']) . "</h3>";
                
                // Link to meals.php with store ID
                echo "<a href='meal.php?seller_id=" . htmlspecialchars($store['id']) . "' class='view-meals'>View Meals</a>";
                echo "</div>";
            }
        } else {
            echo "<p>No stores available at the moment.</p>";
        }

        // Close the result set and connection
        $result->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
