<?php
// Start session and include database connection
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
// Fetch meals uploaded by this store (seller)
$stmt = $conn->prepare("SELECT id, meal_name, description, price, image, rice_options, drinks, rice_price_1, rice_price_2, drinks_price FROM meals WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$mealsResult = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meal_id = intval($_POST['meal_id']);
    $quantity = intval($_POST['quantity']);
    $rice_option = htmlspecialchars($_POST['rice_option']);
    $drink_option = htmlspecialchars($_POST['drink_option']);

    // Fetch the meal details
    $stmt = $conn->prepare("SELECT meal_name, price, rice_price_1, rice_price_2, drinks_price FROM meals WHERE id = ?");
    $stmt->bind_param("i", $meal_id);
    $stmt->execute();
    $mealData = $stmt->get_result()->fetch_assoc();

    if ($mealData) {
        $meal_name = htmlspecialchars($mealData['meal_name']);
        $meal_price = floatval($mealData['price']);
        $rice_price = ($rice_option === '1 cup') ? floatval($mealData['rice_price_1']) : (($rice_option === '2 cups') ? floatval($mealData['rice_price_2']) : 0);
        $drink_price = ($drink_option) ? floatval($mealData['drinks_price']) : 0;
        $total_price = ($meal_price * $quantity) + $rice_price + $drink_price;

        $user_id = $_SESSION['user_id'];
        $meal_id = $conn->real_escape_string($meal_id);

        // Check if item with same meal, rice, and drink already exists in the cart
        $checkQuery = "SELECT id, quantity FROM cart WHERE user_id = '$user_id' AND meal_id = '$meal_id' AND rice_option = '$rice_option' AND drinks = '$drink_option'";
        $checkResult = $conn->query($checkQuery);

        if ($checkResult->num_rows > 0) {
            // Update quantity of existing cart item
            $existingItem = $checkResult->fetch_assoc();
            $new_quantity = $existingItem['quantity'] + $quantity;

            // Calculate the new total price without modifying rice or drink prices
            $updated_total_price = ($meal_price * $new_quantity) + $rice_price + $drink_price;

            $updateQuery = "UPDATE cart 
                SET quantity = '$new_quantity', total_price = '$updated_total_price' 
                WHERE id = '{$existingItem['id']}'";
            $conn->query($updateQuery);

            echo "<script>alert('Cart item updated successfully!');</script>";
        } else {
            // Insert new item into cart
            $sql = "INSERT INTO cart (user_id, meal_id, meal_name, quantity, price, rice_option, rice_price, drinks, drink_price, total_price) 
                    VALUES ('$user_id', '$meal_id', '$meal_name', '$quantity', '$meal_price', '$rice_option', '$rice_price', '$drink_option', '$drink_price', '$total_price')";
            $conn->query($sql);
            echo "<script>alert('Item added to cart successfully!');</script>";
        }
    } else {
        echo "<p>Error: Meal not found!</p>";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['user_action'])) {
        $action = $_POST['user_action'];

        switch ($action) {
            case 'Home':
                header("Location: user_dashboard.php");
                exit();
            case 'view_cart':
                header("Location: cart.php");
                exit();
            case 'edit_profile':
                header("Location: user_edit.php");
                exit();
            case 'logout':
                session_destroy();
                header("Location: login.php");
                exit();
            default:
                echo "Invalid action!";
        }
        // Redirect to confirmation page
        header("Location: confirmation.php");
        exit();
    }
}

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
        :root {
            --primary-color: #6A5ACD;
            --secondary-color: #F2F2F2;
            --font-primary: 'Roboto', sans-serif;
        }

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #F2F2F2;
            background: -webkit-linear-gradient(
            to right,
            #24243e,
            #302b63,
            #0f0c29
            ); /* Chrome 10-25, Safari 5.1-6 */
  background: linear-gradient(
    to right,
    #24243e,
    #302b63,
    #0f0c29
  );
        }

        .header {
            background-color: #ffffff;
            color: black;
            padding: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
        }

        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: auto;
        }

        select {
            border-radius: 10px;
            background-color: #6A5ACD;
            border: 1px solid #6A5ACD;
            color: #fff;
        }

        .action-select {
            padding: 10px;
            font-size: 16px;
            margin-left: 10px;
            border-radius: 10px;
            background-color: var(--primary-color);
            border: 1px solid var(--primary-color);
            color: #fff;
        }

        .message-button {
            padding: 10px 15px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;

            padding: 10px 20px;
            background-color: var(--primary-color);
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .message-button:hover {
            background-color: #555;
        }

        .back-button {
            display: block;
            margin: 20px auto;
            padding: 10px 15px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            width: fit-content;

            padding: 10px 20px;
            background-color: var(--primary-color);
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #555;
        }

        .meal-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .meal-container h2 {
            color: black;
            text-align: center;
        }

        .meal {
            display: flex;
            flex-direction: column;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .meal img {
            max-width: 100%;
            border-radius: 10px;
            height: auto;
        }

        .meal-details {
            text-align: center;
        }

        .meal h3 {
            margin: 10px 0;
            color: #333;
        }

        .meal p {
            margin: 5px 0;
            color: #555;
        }

        .meal-actions {
            margin-top: 10px;
            text-align: center;
        }

        .meal-actions input[type="number"] {
            width: 50px;
            margin-right: 10px;
            border-radius: 5px;
            padding: 5px;
            border: 1px solid #ccc;
        }

        .meal-actions select {
            margin-right: 10px;
            border-radius: 5px;
            padding: 5px;
            border: 1px solid #ccc;
        }

        .meal-actions button {
            padding: 5px 10px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;

            padding: 10px 20px;
            background-color: var(--primary-color);
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .meal-actions button:hover {
            background-color: #555;
        }

        .no-meals {
            text-align: center;
            font-weight: bold;
            color: #777;
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            .meal {
                width: 90%;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>STORE: <?php echo $seller_name; ?></h1>
        <div class="form-container">
            <form action="user_dashboard.php" method="post">
                <select name="user_action" class="action-select" onchange="this.form.submit()">
                    <option value="">Options</option>
                    <option value="Home">Home</option>
                    <option value="view_cart">Cart</option>
                    <option value="edit_profile">Edit Profile</option>
                    <option value="logout">Logout</option>
                </select>
            </form>
        </div>
    </div>

    <a href="user_dashboard.php" class="back-button">Back to Stores</a>

    <!-- Meals Section -->
    <div class="meal-container">
        <h2 style="color: white;">Available Meals</h2>
        <?php if ($mealsResult->num_rows > 0): ?>
            <?php while ($meal = $mealsResult->fetch_assoc()): ?>

                <div class="meal">
                    <img src="<?php echo htmlspecialchars($meal['image']); ?>" alt="<?php echo htmlspecialchars($meal['meal_name']); ?>">
                    <div class="meal-details">
                        <h3><?php echo htmlspecialchars($meal['meal_name']); ?></h3>
                        <p><?php echo htmlspecialchars($meal['description']); ?></p>
                        <p><strong>Price: ₱<?php echo htmlspecialchars($meal['price']); ?></strong></p>
                    </div>
                    <div class="meal-actions">
                        <form method="POST" action="meal.php?seller_id=<?php echo $seller_id; ?>">
                            <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">

                            <!-- Quantity input -->
                            <label for="quantity">Qty:</label>
                            <input type="number" name="quantity" required min="1" value="1">

                            <!-- Rice options dropdown -->
                            <?php if (!empty($meal['rice_options'])): ?>
                                <label for="rice_option">Rice:</label>
                                <select name="rice_option">
                                    <?php
                                    $riceOptions = explode(',', $meal['rice_options']);
                                    foreach ($riceOptions as $option) {
                                        echo "<option value='" . htmlspecialchars(trim($option)) . "'>" . htmlspecialchars(trim($option)) . "</option>";
                                    }
                                    ?>
                                </select>
                            <?php endif; ?>

                            <!-- Drink options dropdown -->
                            <?php if (!empty($meal['drinks'])): ?>
                                <label for="drink_option">Drink:</label>
                                <select name="drink_option">
                                    <?php
                                    $drinkOptions = explode(',', $meal['drinks']);
                                    foreach ($drinkOptions as $option) {
                                        echo "<option value='" . htmlspecialchars(trim($option)) . "'>" . htmlspecialchars(trim($option)) . "</option>";
                                    }
                                    ?>
                                </select>
                            <?php endif; ?>

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