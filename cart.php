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

    // Check if the meal is already in the cart
    $checkQuery = "SELECT * FROM cart WHERE user_id = ? AND meal_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $user_id, $meal_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if meal is already in the cart
        $updateQuery = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND meal_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("iii", $quantity, $user_id, $meal_id);
        $stmt->execute();
    } else {
        // Add new meal to the cart
        $insertQuery = "INSERT INTO cart (user_id, meal_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iii", $user_id, $meal_id, $quantity);
        $stmt->execute();
    }
}

// Handle deletion of selected items
if (isset($_POST['delete'])) {
    if (!empty($_POST['selected_meals'])) {
        foreach ($_POST['selected_meals'] as $meal_id) {
            // Use meal_id for deletion instead of cart item ID
            $deleteQuery = "DELETE FROM cart WHERE user_id = ? AND meal_id = ?";
            $stmt = $conn->prepare($deleteQuery);

            // Store intval result in a variable for safety
            $meal_id_int = intval($meal_id);

            // Bind the correct parameter (meal_id)
            $stmt->bind_param("ii", $user_id, $meal_id_int);
            $stmt->execute();
        }
    }
}

// Query to fetch the user's cart items
$cartQuery = "SELECT c.meal_id, c.quantity, m.name, m.price, m.image 
              FROM cart c 
              JOIN meals m ON c.meal_id = m.id 
              WHERE c.user_id = ?";
$stmt = $conn->prepare($cartQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cartResult = $stmt->get_result();

// Handle checkout process
if (isset($_POST['checkout'])) {
    // Loop through the cart items and insert them into the orders table
    while ($cartItem = $cartResult->fetch_assoc()) {
        $orderQuery = "INSERT INTO orders (user_id, meal_id, status) VALUES (?, ?, 'pending')";
        $orderStmt = $conn->prepare($orderQuery);
        $orderStmt->bind_param("ii", $user_id, $cartItem['meal_id']);
        $orderStmt->execute();
    }

    // Clear the cart after checkout
    $clearCartQuery = "DELETE FROM cart WHERE user_id = ?";
    $clearStmt = $conn->prepare($clearCartQuery);
    $clearStmt->bind_param("i", $user_id);
    $clearStmt->execute();

    // Redirect to confirmation page
    header("Location: confirmation.php");
    exit();
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
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .meal-container {
            margin: 20px;
        }
        .meal {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .meal img {
            max-width: 100px;
            height: auto;
            margin-right: 15px;
        }
        .button {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .back-button {
            margin: 20px;
            background-color: #007BFF;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #0056b3;
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
            if ($cartResult->num_rows > 0) {
                // Display cart items
                while ($cartItem = $cartResult->fetch_assoc()) {
                    echo "<div class='meal'>";
                    // Use meal_id for deletion instead of cart item ID
                    echo "<input type='checkbox' name='selected_meals[]' value='" . $cartItem['meal_id'] . "'>";
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
