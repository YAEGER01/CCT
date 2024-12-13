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
    $rice_option = !empty($_POST['rice_option']) ? $_POST['rice_option'] : NULL;
    $drink_option = !empty($_POST['drink_option']) ? $_POST['drink_option'] : NULL;

    $stmt = $conn->prepare("SELECT meal_name, price, rice_price_1, rice_price_2, drinks_price FROM meals WHERE id = ?");
    $stmt->bind_param("i", $meal_id);
    $stmt->execute();
    $mealData = $stmt->get_result()->fetch_assoc();

    if ($mealData) {
        $meal_name = htmlspecialchars($mealData['meal_name']);
        $meal_price = floatval($mealData['price']);
        $rice_price = ($rice_option === '1 cup') ? floatval($mealData['rice_price_1']) : (($rice_option === '2 cups') ? floatval($mealData['rice_price_2']) : NULL);
        $drink_price = $drink_option ? floatval($mealData['drinks_price']) : NULL;
        $total_price = ($meal_price * $quantity) + ($rice_price ?? 0) + ($drink_price ?? 0);

        $user_id = $_SESSION['user_id'];

        // Check if item with same meal, rice, and drink already exists in the cart
        $checkQuery = "SELECT id, quantity FROM cart WHERE user_id = ? AND meal_id = ? AND (rice_option = ? OR ? IS NULL) AND (drinks = ? OR ? IS NULL)";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("iissss", $user_id, $meal_id, $rice_option, $rice_option, $drink_option, $drink_option);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        // Check if item with same meal, rice, and drink already exists in the cart
        $checkQuery = "SELECT id, quantity FROM cart WHERE user_id = ? AND meal_id = ? AND rice_option = ? AND drinks = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("iiss", $user_id, $meal_id, $rice_option, $drink_option);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            $existingItem = $checkResult->fetch_assoc();
            $new_quantity = $existingItem['quantity'] + $quantity;

            // Calculate the updated total price
            $updated_total_price = ($meal_price * $new_quantity) + ($rice_price ?? 0) + ($drink_price ?? 0);

            // Debugging output
            echo "New Quantity: $new_quantity<br>";
            echo "Updated Total Price: $updated_total_price<br>";

            // Prepare the update statement
            $updateQuery = "UPDATE cart SET quantity = ?, total_price = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("idi", $new_quantity, $updated_total_price, $existingItem['id']);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<script>alert('Cart item updated successfully!');</script>";
            } else {
                echo "<script>alert('Failed to update cart item.');</script>";
            }
        } else {
            // Prepare the insert statement with NULL check for optional fields
            $insertQuery = "INSERT INTO cart (user_id, meal_id, meal_name, quantity, price, rice_option, rice_price, drinks, drink_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);

            // Prepare bind parameters ensuring correct data types
            $rice_option = $rice_option ?? NULL; // Ensure $rice_option is NULL if not set
            $drink_option = $drink_option ?? NULL; // Ensure $drink_option is NULL if not set

            // Debugging output for insert
            echo "Quantity: $quantity<br>";
            echo "Total Price: $total_price<br>";

            $stmt->bind_param(
                "iisidssssd",
                $user_id,
                $meal_id,
                $meal_name,
                $quantity,
                $meal_price,
                $rice_option,
                $rice_price,
                $drink_option,
                $drink_price,
                $total_price
            );

            if ($stmt->execute()) {
                echo "<script>alert('Item added to cart successfully!');</script>";
            } else {
                echo "<script>alert('Failed to add item to cart.');</script>";
            }
        }

        // Redirect to prevent resubmission on refresh
        header("Location: meal.php?seller_id=" . $seller_id);
        exit();
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
    <link rel="icon" href="images/logo2.jpg" type="image/jpeg">
    <title><?php echo "$seller_name's Meals"; ?></title>
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

        /* Base Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #ffffff;
            /* Light background */
            margin: 0;
            color: #333;
        }

        /* Header */


        /* Form Container */
        .form-container select {
            border-radius: 10px;
            padding: 10px;
            background-color: #d056ef;
            /* Accent color */
            color: white;
            border: none;
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            appearance: none;
        }

        .form-container select:hover {
            background-color: #b045c0;
            /* Darker accent */
        }

        /* Back Button */
        .back-button {
            display: inline-block;
            margin: 15px 20px;
            color: #d056ef;
            /* Accent color */
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .back-button:hover {
            color: #b045c0;
        }

        /* Meal Container */
        .meal-container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background:
                radial-gradient(35.36% 35.36% at 100% 25%, #0000 66%, #d056ef 68% 70%, #0000 72%) 50px 50px/calc(2*50px) calc(2*50px),
                radial-gradient(35.36% 35.36% at 0 75%, #0000 66%, #d056ef 68% 70%, #0000 72%) 50px 50px/calc(2*50px) calc(2*50px),
                radial-gradient(35.36% 35.36% at 100% 25%, #0000 66%, #d056ef 68% 70%, #0000 72%) 0 0/calc(2*50px) calc(2*50px),
                radial-gradient(35.36% 35.36% at 0 75%, #0000 66%, #d056ef 68% 70%, #0000 72%) 0 0/calc(2*50px) calc(2*50px),
                repeating-conic-gradient(#ffffff 0 25%, #0000 0 50%) 0 0/calc(2*50px) calc(2*50px),
                radial-gradient(#0000 66%, #d056ef 68% 70%, #0000 72%) 0 calc(50px/2)/50px 50px #ffffff;

        }

        @media (max-width: 768px) {
            .meal-container {
                width: 80vw;
            }
        }

        .meal-container h2 {
            padding: 10px;
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 24px;
            color: #333;
            background-color: white;
            width: 120px;
        }

        /* Meal Grid */
        .meal-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .meal {
            width: 260px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease;
            font-family: 'MyCustomFont1', sans-serif;
            font-weight: 500;
        }

        .meal:hover {
            transform: scale(1.03);
        }

        .meal h3 {
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 20px;
            color: #555;
            margin: 0 0 8px 0;
            /* Adjusted margin */
        }

        .meal img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            align-self: center;
        }

        .description-box {
            max-height: 80px;
            overflow-y: auto;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f0f0f0;
            border-radius: 6px;
            word-break: break-word;
        }

        .description-box p {
            font-family: 'MyCustomFont1', sans-serif;
            font-weight: 900;
            margin: 0;
            color: #666;
            line-height: 1.4;
        }

        .meal-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }



        @media (max-width: 768px) {
            .meal {
                width: 100%;
            }
        }

        .meal img {
            width: 100%;
            height: 150px;
            /* Fixed height for image */
            object-fit: cover;
            border-radius: 8px;
        }

        /* Meal Details */
        .meal-details {
            flex-grow: 1;
            margin-top: 10px;
        }

        .meal-details h3 {
            margin: 0 0 5px;
            font-size: 16px;
            color: #333;
        }

        .meal-details p {
            font-size: 12px;
            color: #666;
            overflow-y: auto;
            /* Allow vertical scrolling */
            overflow-x: hidden;
            /* Prevent horizontal scrolling */
            white-space: normal;
            /* Allow multi-line text */
            word-wrap: break-word;
            /* Allow breaking long words */
            padding-right: 5px;
            /* Add padding to avoid scroll bar overlap */
        }

        /* Meal Actions */
        .meal-actions {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
        }

        .meal-actions label {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }

        .meal-actions input[type="number"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;

            width: 82%;
        }

        .meal-actions select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;

            width: 100%;
        }


        /* No Meals Message */
        .no-meals {
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 18px;
            color: #666;
            text-align: center;
            margin-top: 20px;
        }






        /* BUTTONS */



        .btn {
            font-family: 'MyCustomFont2', sans-serif;
            padding: 1.1em 2em;
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
            width: 100%;
        }

        .btn:before {
            content: "";
            position: absolute;
            width: 100px;
            height: 120%;
            background-color: #ff6700;
            top: 50%;
            transform: skewX(30deg) translate(-150%, -50%);
            transition: all 0.5s;
        }

        .btn:hover {
            background-color: #4500b5;
            color: #fff;
            box-shadow: 0 2px 0 2px #0d3b66;
        }

        .btn:hover::before {
            transform: skewX(30deg) translate(150%, -50%);
            transition-delay: 0.1s;
        }

        .btn:active {
            transform: scale(0.9);
        }


        /* END OF BUTTONS */

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
        function navigateToPage(selectElement) {
            const selectedValue = selectElement.value;
            if (selectedValue) {
                window.location.href = selectedValue;
            }
        }
    </script>
</head>

<body>
    <!-- Header -->
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

    <br><br><br>

    <!-- Meals Section -->
    <div class="meal-container">
        <h2 style="color: #333;">Available Meals</h2>
        <?php if ($mealsResult->num_rows > 0): ?>
            <div class="meal-grid">
                <?php while ($meal = $mealsResult->fetch_assoc()): ?>
                    <div class="meal">
                        <img src="<?php echo htmlspecialchars($meal['image']); ?>"
                            alt="<?php echo htmlspecialchars($meal['meal_name']); ?>">
                        <div class="meal-details">
                            <hr>
                            <h3><?php echo htmlspecialchars($meal['meal_name']); ?></h3>
                            <p><strong>Price: ₱<?php echo htmlspecialchars($meal['price']); ?></strong></p>
                            <hr>
                            <p>Description: </p>
                            <div class="description-box">
                                <p><?php echo htmlspecialchars($meal['description']); ?></p>
                            </div>

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
                                        <option value="">Select Rice</option>
                                        <option value="1 cup">1 cup (₱<?php echo htmlspecialchars($meal['rice_price_1']); ?>)
                                        </option>
                                        <option value="2 cups">2 cups (₱<?php echo htmlspecialchars($meal['rice_price_2']); ?>)
                                        </option>
                                    </select>
                                <?php endif; ?>
                                <!-- Drinks options dropdown with individual prices -->
                                <?php if (!empty($meal['drinks']) && !empty($meal['drinks_price'])): ?>
                                    <label for="drink_option">Drink:</label>
                                    <select name="drink_option">
                                        <option value="">Select Drink</option>
                                        <?php
                                        $drinks = explode(',', $meal['drinks']);
                                        $prices = explode(',', $meal['drinks_price']);

                                        foreach ($drinks as $index => $drink_option):
                                            $drink_name = trim($drink_option);
                                            $drink_price = isset($prices[$index]) ? trim($prices[$index]) : '0';
                                            ?>
                                            <option value="<?php echo htmlspecialchars($drink_name); ?>">
                                                <?php echo htmlspecialchars($drink_name); ?> -
                                                ₱<?php echo htmlspecialchars($drink_price); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>


                                <br><br>
                                <hr>
                                <br>
                                <button class="btn" type="submit">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-meals">No meals available.</p>
        <?php endif; ?>
    </div>


    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>

</html>