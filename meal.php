<?php
// Start session and include database connection
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate 'seller_id' in URL
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

$sellerData = $sellerResult->fetch_assoc();
$seller_name = htmlspecialchars($sellerData['username']);

// Fetch meals for this seller
$stmt = $conn->prepare("SELECT id, meal_name, description, price, image, rice_options, drinks, rice_price_1, rice_price_2, drinks_price FROM meals WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$mealsResult = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meal_id'], $_POST['quantity'])) {
    $meal_id = intval($_POST['meal_id']);
    $quantity = intval($_POST['quantity']);
    $rice_option = $_POST['rice_option'] ?? '';
    $drink_option = $_POST['drink_option'] ?? '';

    $stmt = $conn->prepare("SELECT meal_name, price, rice_price_1, rice_price_2, drinks_price FROM meals WHERE id = ?");
    $stmt->bind_param("i", $meal_id);
    $stmt->execute();
    $mealData = $stmt->get_result()->fetch_assoc();

    if ($mealData) {
        $meal_name = htmlspecialchars($mealData['meal_name']);
        $meal_price = floatval($mealData['price']);
        $rice_price = ($rice_option === '1 cup') ? floatval($mealData['rice_price_1']) : (($rice_option === '2 cups') ? floatval($mealData['rice_price_2']) : 0);
        $drink_price = $drink_option ? floatval($mealData['drinks_price']) : 0;
        $total_price = ($meal_price * $quantity) + $rice_price + $drink_price;

        $user_id = $_SESSION['user_id'];

        // Check if item with same meal, rice, and drink already exists in the cart
        $checkQuery = "SELECT id, quantity FROM cart WHERE user_id = ? AND meal_id = ? AND rice_option = ? AND drinks = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("iiss", $user_id, $meal_id, $rice_option, $drink_option);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            $existingItem = $checkResult->fetch_assoc();
            $new_quantity = $existingItem['quantity'] + $quantity;
            $updated_total_price = ($meal_price * $new_quantity) + $rice_price + $drink_price;

            $updateQuery = "UPDATE cart SET quantity = ?, total_price = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("idi", $new_quantity, $updated_total_price, $existingItem['id']);
            $stmt->execute();

            echo "<script>alert('Cart item updated successfully!');</script>";
        } else {
            $insertQuery = "INSERT INTO cart (user_id, meal_id, meal_name, quantity, price, rice_option, rice_price, drinks, drink_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("iisidssdds", $user_id, $meal_id, $meal_name, $quantity, $meal_price, $rice_option, $rice_price, $drink_option, $drink_price, $total_price);
            $stmt->execute();

            echo "<script>alert('Item added to cart successfully!');</script>";
        }
    } else {
        echo "<p>Error: Meal not found!</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_action'])) {
    $action = $_POST['user_action'];
    switch ($action) {
        case 'Home':
            header("Location: user_dashboard.php");
            break;
        case 'view_cart':
            header("Location: cart.php");
            break;
        case 'edit_profile':
            header("Location: user_edit.php");
            break;
        case 'logout':
            session_destroy();
            header("Location: login.php");
            break;
        default:
            echo "Invalid action!";
    }
    exit();
}

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