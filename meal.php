<?php
// Start session and include database connection
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Check if 'seller_id' is provided in the URL
if (!isset($_GET['seller_id'])) {
    echo "No store selected!";
    exit();
}

$seller_id = intval($_GET['seller_id']); // Get seller ID from URL
$user_id = $_SESSION['user_id']; // Logged-in user ID

// Query to fetch the store (seller) information
$sellerQuery = "SELECT username FROM users WHERE id = ? AND role = 'seller'";
$stmt = $conn->prepare($sellerQuery);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$sellerResult = $stmt->get_result();

if ($sellerResult->num_rows === 0) {
    echo "Store not found!";
    exit();
}

// Fetch seller data
$sellerData = $sellerResult->fetch_assoc();
$seller_name = $sellerData['username'];

// Query to fetch meals uploaded by this store (seller)
$mealsQuery = "SELECT id, name, description, price, image FROM meals WHERE seller_id = ?";
$stmt = $conn->prepare($mealsQuery);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$mealsResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($seller_name); ?>'s Meals</title>
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
        .back-button {
            margin: 20px;
            background-color: #007BFF;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
        }
        .meal-container {
            margin: 20px;
        }
        .meal {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .meal img {
            max-width: 150px;
            height: auto;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Meals from <?php echo htmlspecialchars($seller_name); ?></h1>
    </div>

    <a href="user_dashboard.php" class="back-button">Back to Stores</a>

    <!-- Meals Section -->
    <div class="meal-container">
        <h2>Available Meals</h2>
        <?php
        if ($mealsResult->num_rows > 0) {
            // Display meals uploaded by this seller
            while ($meal = $mealsResult->fetch_assoc()) {
                echo "<div class='meal'>";
                echo "<h3>" . htmlspecialchars($meal['name']) . "</h3>";
                echo "<img src='" . htmlspecialchars($meal['image']) . "' alt='" . htmlspecialchars($meal['name']) . "'>";
                echo "<p>" . htmlspecialchars($meal['description']) . "</p>";
                echo "<p><strong>Price: $" . htmlspecialchars($meal['price']) . "</strong></p>";

                // Add to Cart Form
                echo "<form method='POST' action='cart.php'>";
                echo "<input type='hidden' name='meal_id' value='" . $meal['id'] . "'>";
                echo "<label for='quantity'>Quantity:</label>";
                echo "<input type='number' name='quantity' min='1' value='1' required>";
                echo "<button type='submit'>Add to Cart</button>";
                echo "</form>";

                echo "</div>";
            }
        } else {
            echo "<p>No meals available from this store at the moment.</p>";
        }

        // Close the result set and connection
        $mealsResult->close();
        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
