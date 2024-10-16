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
$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : null;
if (!$seller_id) {
    echo "<p>No store selected!</p>";
    exit();
}

// Fetch seller information
$sellerQuery = "SELECT username FROM users WHERE id = ? AND role = 'seller'";
$stmt = $conn->prepare($sellerQuery);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$sellerResult = $stmt->get_result();

if ($sellerResult->num_rows === 0) {
    echo "<p>Store not found!</p>";
    exit();
}

// Fetch seller data
$sellerData = $sellerResult->fetch_assoc();
$seller_name = htmlspecialchars($sellerData['username']);

// Fetch meals uploaded by this store (seller)
$mealsQuery = "SELECT id, name, description, price, image FROM meals WHERE seller_id = ?";
$stmt = $conn->prepare($mealsQuery);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$mealsResult = $stmt->get_result();

// Close the seller result set
$sellerResult->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "$seller_name's Meals"; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #2E2E2E;
            /* Grayish black background */
        }

        .header {
            background-color: #6A5ACD;
            /* Purple header */
            color: white;
            padding: 15px;
            text-align: center;
        }

        .back-button {
            display: inline-block;
            margin: 20px;
            background-color: #6A5ACD;
            /* Purple button */
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        .meal-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 15px;
            background-color: #383838;
            /* Dark grayish black */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .meal {
            border: 1px solid #6A5ACD;
            /* Purple border for meal card */
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            background-color: #444;
            /* Slightly lighter grayish black */
        }

        .meal img {
            max-width: 150px;
            height: auto;
            margin-right: 15px;
        }

        .meal-details {
            flex-grow: 1;
            color: white;
        }

        .meal-details h3 {
            margin: 0;
        }

        .meal-details p {
            margin: 5px 0;
        }

        .meal-actions {
            display: flex;
            align-items: center;
        }

        .meal-actions input[type='number'] {
            width: 60px;
            margin-right: 10px;
        }

        button {
            padding: 10px 15px;
            background-color: #6A5ACD;
            /* Purple button */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #5a4db1;
            /* Darker purple on hover */
        }

        .no-meals {
            text-align: center;
            color: #888;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>Meals from <?php echo $seller_name; ?></h1>
        <a href="user_opened_convo.php?seller_id=<?php echo $seller_id; ?>" class="message-button">Message Seller</a>
    </div>

    <a href="user_dashboard.php" class="back-button">Back to Stores</a>

    <!-- Meals Section -->
    <div class="meal-container">
        <h2 style="color: white;">Available Meals</h2>
        <?php if ($mealsResult->num_rows > 0): ?>
            <?php while ($meal = $mealsResult->fetch_assoc()): ?>
                <div class="meal">
                    <img src="<?php echo htmlspecialchars($meal['image']); ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>">
                    <div class="meal-details">
                        <h3><?php echo htmlspecialchars($meal['name']); ?></h3>
                        <p><?php echo htmlspecialchars($meal['description']); ?></p>
                        <p><strong>Price: ₱<?php echo htmlspecialchars($meal['price']); ?></strong></p>
                    </div>
                    <div class="meal-actions">
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
                            <label for="quantity">Qty:</label>
                            <input type="number" name="quantity" required>
                            <button type="submit">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-meals">No meals available from this store at the moment.</p>
        <?php endif; ?>

        <!-- Close the meals result set -->
        <?php $mealsResult->close(); ?>
    </div>

    <?php
    // Close the database connection
    $stmt->close();
    $conn->close();
    ?>
</body>

</html>