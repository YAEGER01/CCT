<?php
// Start session and include database connection
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user ID
$username = $_SESSION['username']; // Get the logged-in user's username

// Handle adding a meal to the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['meal_id'])) {
    $meal_id = intval($_POST['meal_id']);
    $quantity = intval($_POST['quantity']);

    // Validate quantity to be greater than 0
    if ($quantity <= 0) {
        echo "<p>Please enter a valid quantity.</p>";
        exit();
    }

    // Escape inputs to prevent SQL injection
    $meal_id = mysqli_real_escape_string($conn, $meal_id);
    $quantity = mysqli_real_escape_string($conn, $quantity);

    // Fetch meal details (name, seller name, and price) from meals table
    $mealQuery = "SELECT name AS meal_name, seller_id, price FROM meals WHERE id = $meal_id";
    $mealResult = mysqli_query($conn, $mealQuery);

    if ($mealResult && mysqli_num_rows($mealResult) > 0) {
        $mealData = mysqli_fetch_assoc($mealResult);
        $meal_name = mysqli_real_escape_string($conn, $mealData['meal_name']);
        $price = floatval($mealData['price']); // Fetch price here

        // Fetch seller name based on seller_id
        $seller_id = $mealData['seller_id'];
        $sellerQuery = "SELECT username AS seller_name FROM users WHERE id = $seller_id";
        $sellerResult = mysqli_query($conn, $sellerQuery);
        $sellerData = mysqli_fetch_assoc($sellerResult);
        $seller_name = mysqli_real_escape_string($conn, $sellerData['seller_name']);

        // Check if the meal is already in the cart
        $checkQuery = "SELECT quantity FROM cart WHERE user_id = $user_id AND meal_id = $meal_id";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            // Update quantity if meal is already in the cart
            $existingCartItem = mysqli_fetch_assoc($result);
            $newQuantity = $existingCartItem['quantity'] + $quantity; // Add the new quantity to the existing one

            $updateQuery = "UPDATE cart SET quantity = $newQuantity WHERE user_id = $user_id AND meal_id = $meal_id";
            mysqli_query($conn, $updateQuery);
        } else {
            // Add new meal to the cart with additional details
            $insertQuery = "INSERT INTO cart (user_id, meal_id, meal_name, seller_name, username, quantity, price) 
                            VALUES ($user_id, $meal_id, '$meal_name', '$seller_name', '$username', $quantity, $price)";
            mysqli_query($conn, $insertQuery);
        }

        // Redirect to avoid form resubmission
        header("Location: cart.php");
        exit();
    }
}


// Handle deletion of selected items
if (isset($_POST['delete'])) {
    if (!empty($_POST['selected_meals'])) {
        foreach ($_POST['selected_meals'] as $meal_id) {
            // Use meal_id for deletion instead of cart item ID
            $meal_id = intval($meal_id);
            $deleteQuery = "DELETE FROM cart WHERE user_id = $user_id AND meal_id = $meal_id";
            mysqli_query($conn, $deleteQuery);
        }
        // Redirect to avoid form resubmission
        header("Location: cart.php");
        exit();
    }
}

// Query to fetch the user's cart items including rice and drinks
$cartQuery = "SELECT c.meal_id, c.quantity, c.meal_name, c.seller_name, c.username, m.price, m.image, 
                     c.rice_option, c.drinks
              FROM cart c 
              JOIN meals m ON c.meal_id = m.id 
              WHERE c.user_id = $user_id";
$cartResult = mysqli_query($conn, $cartQuery);
// Initialize total price variable
$totalPrice = 0;

// Query to fetch the user's cart items including rice and drinks
$cartQuery = "SELECT c.meal_id, c.quantity, c.meal_name, c.seller_name, c.username, m.price, m.image, 
                     c.rice_option, c.drinks
              FROM cart c 
              JOIN meals m ON c.meal_id = m.id 
              WHERE c.user_id = $user_id";
$cartResult = mysqli_query($conn, $cartQuery);

// Handle checkout process
if (isset($_POST['checkout'])) {
    if (mysqli_num_rows($cartResult) > 0) {
        // Loop through the cart items and insert them into the orders table
        while ($cartItem = mysqli_fetch_assoc($cartResult)) {
            $meal_id = intval($cartItem['meal_id']);
            $quantity = intval($cartItem['quantity']);
            $price = floatval($cartItem['price'] * $cartItem['quantity']); // Fetch price here

            // Insert each cart item as a new order
            $orderQuery
                = "INSERT INTO orders (user_id, meal_id, quantity, status, price) VALUES ($user_id, $meal_id, $quantity, 'pending', $price)";
            mysqli_query($conn, $orderQuery);
        }

        // Clear the cart after checkout
        $clearCartQuery = "DELETE FROM cart WHERE user_id = $user_id";
        mysqli_query($conn, $clearCartQuery);

        // Redirect to confirmation page
        header("Location: confirmation.php");
        exit();
    } else {
        echo "<p>Your cart is empty. Please add some items before checking out.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: #333;
        }

        .back-button {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            background-color: #6c63ff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #5a54e2;
        }

        .meal-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .meal {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            background-color: #f7f7f7;
            transition: background-color 0.3s;
        }

        .meal:hover {
            background-color: #ebebeb;
        }

        .meal img {
            width: 100px;
            height: 100px;
            border-radius: 5px;
            margin-right: 15px;
        }

        .meal h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .meal p {
            margin: 5px 0;
            color: #555;
        }

        .button {
            background-color: #6c63ff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #5a54e2;
        }

        @media (max-width: 600px) {
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
    <div class="header">
        <h1>Your Cart</h1>
        <a href="user_dashboard.php" class="back-button">Back to Stores</a>
    </div>

    <div class="meal-container">
        <form method="POST">
            <?php
            if (mysqli_num_rows($cartResult) > 0) {
                // Display cart items
                while ($cartItem = mysqli_fetch_assoc($cartResult)) {
                    $itemTotal = $cartItem['price'] * $cartItem['quantity']; // Calculate total for this item
                    $totalPrice += $itemTotal; // Add to total price

                    echo "<div class='meal'>";
                    echo "<input type='checkbox' name='selected_meals[]' value='" . htmlspecialchars($cartItem['meal_id']) . "'>";
                    echo "<img src='" . htmlspecialchars($cartItem['image']) . "' alt='" . htmlspecialchars($cartItem['meal_name']) . "'>";
                    echo "<div>";
                    echo "<h3>" . htmlspecialchars($cartItem['meal_name']) . "</h3>";
                    echo "<p>Seller: " . htmlspecialchars($cartItem['seller_name']) . "</p>";
                    echo "<p>Quantity: " . htmlspecialchars($cartItem['quantity']) . "</p>";
                    echo "<p>Rice Option: " . htmlspecialchars($cartItem['rice_option'] ?? 'N/A') . "</p>";
                    echo "<p>Drink Option: " . htmlspecialchars($cartItem['drinks'] ?? 'N/A') . "</p>";
                    echo "<p>Price: $" . htmlspecialchars($cartItem['price']) . "</p>";
                    echo "<p>Total: $" . number_format($itemTotal, 2) . "</p>"; // Display total for this item
                    echo "</div>";
                    echo "</div>";
                }
                // Display total price for all items in the cart
                echo "<h3>Total Price: $" . number_format($totalPrice, 2) . "</h3>";
            } else {
                echo "<p>Your cart is empty.</p>";
            }
            ?>
            <br>
            <button type="submit" name="delete" class="button">Delete Selected</button>
            <button type="submit" name="checkout" class="button">Proceed to Checkout</button>
        </form>
    </div>
</body>

</html>