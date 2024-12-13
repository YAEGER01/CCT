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

// Handle deletion of selected items 
if (isset($_POST['delete'])) {
    if (!empty($_POST['selected_meals'])) {
        foreach ($_POST['selected_meals'] as $meal_id) {
            $meal_id = intval($meal_id);
            $deleteQuery = "DELETE FROM cart WHERE user_id = $user_id AND meal_id = $meal_id";
            mysqli_query($conn, $deleteQuery);
        }
        // Redirect to avoid form resubmission
        header("Location: cart.php");
        exit();
    }
}

// Query to fetch the user's cart items
$cartQuery = "SELECT c.meal_id, c.quantity, c.meal_name, c.rice_option, c.rice_price, c.drinks, c.drink_price, m.price, m.image
              FROM cart c 
              JOIN meals m ON c.meal_id = m.id 
              WHERE c.user_id = $user_id";
$cartResult = mysqli_query($conn, $cartQuery);

// Initialize total price variable
$totalPrice = 0;

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
    } else {
        echo '<script>alert("Please select at least one item to proceed to checkout.");</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CART</title>
    <style>
        @font-face {
            font-family: 'MyCustomFont1';
            /* Give your font a name */
            src: url('fonts/nexa/Nexa-ExtraLight.ttf') format('truetype');
            /* Path to the TTF file */
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'MyCustomFont2';
            /* Give your font a name */
            src: url('fonts/nexa/Nexa-Heavy.ttf') format('truetype');
            /* Path to the TTF file */
            font-weight: normal;
            font-style: normal;
        }

        :root {
            --primary-color: #6A5ACD;
            --secondary-color: #F2F2F2;
            --font-primary: 'Roboto', sans-serif;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Background Style */
            background: radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 0% 0% / 64px 64px,
                radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 32px 32px / 64px 64px,
                linear-gradient(#a43fc6 2px, transparent 2px) 0px -1px / 32px 32px,
                linear-gradient(90deg, #a43fc6 2px, #ffffff 2px) -1px 0px / 32px 32px #ffffff;
            background-size: 64px 64px, 64px 64px, 32px 32px, 32px 32px;
            background-color: #ffffff;
            animation: scroll-diagonal 10s linear infinite;

        }

        @keyframes scroll-diagonal {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 64px 64px;
            }
        }


        .back-button {
            text-decoration: none;
            color: #fff;
            text-decoration: underline;
            font-size: 1rem;
            margin-top: 0.5rem;
            display: inline-block;
        }

        .meal-container {
            width: 90%;
            max-width: 800px;
            margin: 1.5rem auto;
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .meal-container {
                width: 80vw;
            }
        }

        .meal {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #ddd;
        }

        .meal img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            margin-right: 1.5rem;
            object-fit: cover;
            box-shadow: 0 2px 6px var(--shadow-color);
        }

        .meal h3 {
            font-size: 1.2rem;
            margin: 0;
            color: #333;
            text-align: left;
        }

        .meal p {
            margin: 0.2rem 0;
            color: #666;
            font-size: 0.95rem;
        }

        .meal:last-child {
            border-bottom: none;
        }

        .btn-group {
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            background-color: #fff;
            padding: 10px;
            /* Adjusted padding */
            position: fixed;
            bottom: 0;
            left: 37.5%;
            right: 37.5%;
            width: 25%;
            max-width: 100%;
            margin: 1.5rem auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .btn-group {
                width: 100%;
                left: 0;
                right: 0;
                justify-content: space-around;
            }
        }

        /* Button Styles with Animation */
        .btn {
            font-family: 'MyCustomFont2', sans-serif;
            padding: 0.5em 2em;
            background: none;
            border: 2px solid #fff;
            font-size: 15px;
            color: #131313;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            border-radius: 12px;
            background-color: #d056ef;
            font-weight: bolder;
            box-shadow: 0 2px 0 2px #000;
            width: 250px;
            margin: 10px;
        }

        .btn:before {
            content: "";
            position: absolute;
            width: 100px;
            height: 120%;
            background-color: #ff6700;
            top: 50%;
            transform: skewX(30deg) translate(-110%, -50%);
            transition: all 0.5s;
        }

        .btn:hover {
            background-color: #4500b5;
            color: #fff;
            box-shadow: 0 2px 0 2px #0d3b66;
        }

        .btn:hover::before {
            transform: skewX(30deg) translate(90%, -50%);
            transition-delay: 0.1s;
        }

        .btn:active {
            transform: scale(0.9);
        }



        /* END OF BUTTONS */



        h3 {
            font-family: 'MyCustomFont1', sans-serif;
            font-weight: 690;
            color: #333;
            text-align: right;
        }

        input[type="checkbox"] {
            margin-right: 1rem;
            accent-color: var(--primary-color);
        }

        h3.total {
            color: var(--text-color);
            text-align: right;
            margin-top: 1rem;
            font-size: 1.2rem;
            font-weight: bold;
            font-family: 'MyCustomFont2', sans-serif;
        }

        select {
            font-family: 'MyCustomFont2', sans-serif;
            border-radius: 10px;
            background-color: #d056ef;
            border: 1px solid #d056ef;
            color: #fff;
        }

        .action-select {
            padding: 10px;
            font-size: 16px;
            margin-left: 10px;
            border-radius: 10px;
            background-color: #d056ef;
            border: 1px solid var(--primary-color);
            color: #fff;
        }



        /* Header */
        .header {
            background-color: #ffffff;
            /* Light gray */
            color: #333;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 95vw;
            border-radius: 15px;
            margin-top: 15px;
            margin: 20px;
        }

        @media (max-width: 768px) {
            .header {
                width: 80vw;
            }
        }

        .header h2 {
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 24px;
            margin: 0;
            color: #d056ef;
            /* Accent color */
        }

        .header p {
            font-family: 'MyCustomFont1', sans-serif;
            font-size: 12px;
            font-weight: 690;
            color: #555;
            text-align: center;
        }

        .nav-dropdown {
            position: relative;
            display: inline-block;
        }

        #options-dropdown {
            appearance: none;
            /* Remove default appearance */
            -webkit-appearance: none;
            /* For Safari */
            -moz-appearance: none;
            /* For Firefox */
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #f2f2f2;
            color: #333;
            border-radius: 8px;
            cursor: pointer;
        }

        #options-dropdown:focus {
            outline: none;
            box-shadow: 0 0 0 2px #007bff;
        }

        #options-dropdown option {
            padding: 10px;
            font-size: 16px;

        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            const totalSelectedPriceDisplay = document.createElement("h3");
            totalSelectedPriceDisplay.style.color = "#333";
            totalSelectedPriceDisplay.innerText = "Total: ₱0.00";
            document.querySelector(".meal-container").appendChild(totalSelectedPriceDisplay);

            function updateSelectedTotalPrice() {
                let selectedTotalPrice = 0;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const itemTotal = parseFloat(checkbox.dataset.price) || 0;
                        selectedTotalPrice += itemTotal;
                    }
                });
                totalSelectedPriceDisplay.innerText = "Total: ₱" + selectedTotalPrice.toFixed(2);
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener("change", updateSelectedTotalPrice);
            });

            document.querySelector("button[name='checkout']").addEventListener("click", function (e) {
                const isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

                if (!isChecked) {
                    e.preventDefault(); // Prevent form submission
                    alert("Please select at least one item to proceed to checkout.");
                }
            });

            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });
    </script>
    <script>
        function checkoutSelectedItems() {
            const selectedMeals = Array.from(document.querySelectorAll("input[name='selected_meals[]']:checked"))
                .map(meal => meal.value);

            if (selectedMeals.length === 0) {

                return;
            }

            // Convert array of meal IDs to a JSON string
            const data = new URLSearchParams();
            data.append("selected_meals", JSON.stringify(selectedMeals));

            // Send selected meal IDs to PHP
            fetch("checkout_process.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: data
            })
                .then(response => response.text())
                .then(response => {
                    console.log("Checkout processed:", response);
                    window.location.href = "confirmation.php";
                })
                .catch(error => console.error("Error:", error));
        }
    </script>
    <script>
        function navigateToPage(selectElement) {
            const selectedValue = selectElement.value;
            if (selectedValue) {
                window.location.href = selectedValue;
            }
        }
    </script>

</head>

<body>
    <!--div class="header">
        <h1>CART</h1>
        <a href="user_dashboard.php" class="back-button">Back to Stores</a>
    </div-->
    <div class="header">
        <div class="site_name">
            <h2>You Chews</h2>
            <p>IKAW BAHALA</p>
        </div>
        <div class="nav-dropdown">
            <select id="options-dropdown" onchange="navigateToPage(this)">
                <option style="display: none" value="">Options</option>
                <option value="user_dashboard.php">Home</option>
                <option value="cart.php">Cart</option>
                <option value="user_orders.php">My Orders</option>
                <option value="user_transacts.php">Transactions</option>
                <option value="user_edit.php">Edit User</option>
                <option value="logout.php">Logout</option>
            </select>
        </div>
    </div>
    <div class="meal-container">
        <form method="POST">
            <?php
            if (mysqli_num_rows($cartResult) > 0) {
                // Display cart items
                while ($cartItem = mysqli_fetch_assoc($cartResult)) {
                    $itemTotal = ($cartItem['price'] * $cartItem['quantity'] + $cartItem['rice_price'] + $cartItem['drink_price']); // Calculate total for this item
                    $totalPrice += $itemTotal; // Add to total price
            
                    echo "<div class='meal'>";
                    echo "<input type='checkbox' name='selected_meals[]' value='" . htmlspecialchars($cartItem['meal_id'] ?? '') . "' data-price='" . $itemTotal . "'>";
                    echo "<img src='" . htmlspecialchars($cartItem['image'] ?? '') . "' alt='" . htmlspecialchars($cartItem['meal_name'] ?? '') . "'>";
                    echo "<div>";
                    echo "<h3>Meal Name: " . htmlspecialchars($cartItem['meal_name'] ?? '') . "</h3>";
                    echo "<h3>Price: " . htmlspecialchars($cartItem['price'] ?? '') . "</h3>";
                    echo "<p>Quantity: " . htmlspecialchars($cartItem['quantity'] ?? '') . "</p>";
                    echo "<p>Rice: " . htmlspecialchars($cartItem['rice_option'] ?? 'None') . " <br> Rice Price: (₱" . htmlspecialchars($cartItem['rice_price'] ?? '—') . ")</p>";
                    echo "<p>Drink: " . htmlspecialchars($cartItem['drinks'] ?? 'None') . " <br> Drink Price (₱" . htmlspecialchars($cartItem['drink_price'] ?? '—') . ")</p>";
                    echo "<hr>";
                    echo "<strong>Total Price: ₱" . number_format($itemTotal, 2) . "</strong>";
                    echo "</div>";
                    echo "</div>";

                }
            } else {
                echo "<p>Your cart is empty!</p>";
            }
            ?>
            <div class="btn-group">
                <button class="btn" type="submit" name="delete" class="button">Delete</button>
                <button class="btn" type="button" name="checkout" class="button"
                    onclick="checkoutSelectedItems()">Checkout</button>

            </div>
        </form>
    </div>
</body>

</html>