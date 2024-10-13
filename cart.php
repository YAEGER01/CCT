
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
        // Add new meal to the cart
        $insertQuery = "INSERT INTO cart (user_id, meal_id, quantity) VALUES ($user_id, $meal_id, $quantity)";
        mysqli_query($conn, $insertQuery);
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
    }
}

// Query to fetch the user's cart items
$cartQuery = "SELECT c.meal_id, c.quantity, m.name, m.price, m.image 
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
            
            // Insert each cart item as a new order
            $orderQuery = "INSERT INTO orders (user_id, meal_id, quantity, status) 
                           VALUES ($user_id, $meal_id, $quantity, 'pending')";
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
            background-color: #2b2b2b;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #6a0dad;
            color: white;
            padding: 15px;
            text-align: center;
            border-bottom: 5px solid #4b0082;
            border-radius: 0 0 15px 15px;
        }
        .meal-container {
            margin: 20px;
            background-color: #333;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .meal {
            background-color: #444;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #555;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .meal img {
            max-width: 100px;
            height: auto;
            margin-right: 15px;
            border-radius: 10px;
        }
        .button {
            background-color: #6a0dad;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #4b0082;
        }
        .back-button {
            margin: 20px;
            background-color: #6a0dad;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 10px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #4b0082;
        }
        input[type='checkbox'] {
            transform: scale(1.3);
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
                    echo "<div class='meal'>";
                    echo "<input type='checkbox' name='selected_meals[]' value='" . htmlspecialchars($cartItem['meal_id']) . "'>";
                    echo "<img src='" . htmlspecialchars($cartItem['image']) . "' alt='" . htmlspecialchars($cartItem['name']) . "'>";
                    echo "<div>";
                    echo "<h3>" . htmlspecialchars($cartItem['name']) . "</h3>";
                    echo "<p>Quantity: " . htmlspecialchars($cartItem['quantity']) . "</p>";
                    echo "<p><strong>Price: $" . htmlspecialchars($cartItem['price']) . "</strong></p>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>Your cart is empty.</p>";
            }
            ?>
            <button type="submit" name="delete" class="button">Delete Selected</button>
            <button type="submit" name="checkout" class="button">Checkout</button>
        </form>
    </div>
</body>
</html>
