<?php
session_start();
include 'db.php';

if (isset($_POST['selected_meals']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $selectedMeals = json_decode($_POST['selected_meals'], true);

    foreach ($selectedMeals as $meal_id) {
        $meal_id = intval($meal_id);

        // Fetch item details
        $itemQuery = "SELECT quantity, price, rice_option, rice_price, drinks, drink_price 
                      FROM cart WHERE user_id = $user_id AND meal_id = $meal_id";
        $itemResult = mysqli_query($conn, $itemQuery);

        if ($itemResult && mysqli_num_rows($itemResult) > 0) {
            $cartItem = mysqli_fetch_assoc($itemResult);
            $quantity = intval($cartItem['quantity']);
            $price = floatval($cartItem['price']);
            $ricePrice = floatval($cartItem['rice_price']);
            $drinkPrice = floatval($cartItem['drink_price']);
            $totalItemPrice = ($price * $quantity) + $ricePrice + $drinkPrice;

            // Insert into orders table
            $orderQuery = "INSERT INTO orders (user_id, meal_id, quantity, status, price, rice_option, rice_price, drinks, drinks_price) 
                           VALUES ($user_id, $meal_id, $quantity, 'pending', $totalItemPrice, '{$cartItem['rice_option']}', $ricePrice, '{$cartItem['drinks']}', $drinkPrice)";
            mysqli_query($conn, $orderQuery);
        }
    }

    // Clear selected items from cart
    $selectedMealsList = implode(',', array_map('intval', $selectedMeals));
    $clearSelectedQuery = "DELETE FROM cart WHERE user_id = $user_id AND meal_id IN ($selectedMealsList)";
    mysqli_query($conn, $clearSelectedQuery);

    echo "Checkout successful";
}
