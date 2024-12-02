<?php
session_start();
include 'db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php"); // Redirect to login if not seller
    exit();
}

// Fetch seller username
$username = $_SESSION['username'];

// Handle meal upload with image
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_meal'])) {
    $meal_name = mysqli_real_escape_string($conn, $_POST['meal_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);

    // Get selected rice options and prices
    $rice_options = isset($_POST['rice_options']) ? implode(', ', $_POST['rice_options']) : '';
    $rice_price_1 = mysqli_real_escape_string($conn, $_POST['rice_price_1']); // New: price for 1 cup rice
    $rice_price_2 = mysqli_real_escape_string($conn, $_POST['rice_price_2']); // New: price for 2 cups rice

    // Get uploaded drinks and drink prices
    $drinks = isset($_POST['drinks']) ? mysqli_real_escape_string($conn, $_POST['drinks']) : '';
    $drinks_price = isset($_POST['drinks_price']) ? mysqli_real_escape_string($conn, $_POST['drinks_price']) : ''; // New: drink prices

    // Image upload handling (same as before)
    if (isset($_FILES['meal_image']) && $_FILES['meal_image']['error'] === 0) {
        $target_dir = "uploads/"; // Directory to store uploaded images
        $file_name = basename($_FILES["meal_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name; // Unique name using time

        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["meal_image"]["tmp_name"], $target_file)) {
                // Insert meal data including rice options, drinks, drink prices, and the image path
                $sql = "INSERT INTO meals (meal_name, description, price, image, rice_options, rice_price_1, rice_price_2, drinks, drinks_price, seller_id) 
                        VALUES ('$meal_name', '$description', '$price', '$target_file', '$rice_options', '$rice_price_1', '$rice_price_2', '$drinks', '$drinks_price', {$_SESSION['user_id']})";
                if (mysqli_query($conn, $sql)) {
                    echo "<script>alert('Meal uploaded successfully.');</script>";
                } else {
                    echo "<script>alert('Error uploading meal: " . mysqli_error($conn) . "');</script>";
                }
            }
        } else {
            echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        }
    } else {
        echo "<script>alert('Please upload a valid image.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Meal</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
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

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #F2F2F2;
        }

        .section {
            background-color: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        @media (max-width: 750px) {
            .section {
                width: 80vw;
            }
        }

        .section h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 15px;
        }

        label {
            font-size: 16px;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        input[type="file"] {
            width: 95%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input[type="checkbox"] {
            margin-right: 5px;
        }

        button {
            background-color: #6c63ff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #5753d8;
        }

        .meal-image-label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }

        #drink-input {
            width: calc(100% - 70px);
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #drink-list div {
            margin: 5px 0;
        }

        #drink-list button {
            margin-left: 10px;
            background-color: #ff6b6b;
            padding: 3px 7px;
            font-size: 12px;
        }

        /* Button Styles with Animation */
        button,
        .btn {
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
            background-color: #4500b5;
            font-weight: bolder;
            box-shadow: 0 2px 0 2px #000;
            width: 250px;
            margin: 10px;
            text-decoration: none;
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
            transform: skewX(30deg) translate(110%, -50%);
            transition-delay: 0.1s;
        }

        .btn:active {
            transform: scale(0.9);
        }

        /* Button Styles with Animation */
        .btn-dashb {
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
            background-color: #4500b5;
            font-weight: bolder;
            box-shadow: 0 2px 0 2px #000;
            width: 250px;
            margin: 10px;
            text-decoration: none;
        }

        .btn-dashb:before {
            content: "";
            position: absolute;
            width: 100px;
            height: 120%;
            background-color: #ff6700;
            top: 50%;
            transform: skewX(30deg) translate(-110%, -50%);
            transition: all 0.5s;
        }

        .btn-dashb:hover {
            background-color: #4500b5;
            color: #fff;
            box-shadow: 0 2px 0 2px #0d3b66;
        }

        .btn-dashb:hover::before {
            transform: skewX(30deg) translate(160%, -50%);
            transition-delay: 0.1s;
        }

        .btn-dashb:active {
            transform: scale(0.9);
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

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .header h2 {
                font-size: 15px;
            }
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
        <h2 class="welcome-message desktop-only">Meal Upload</h2>
        <div class="nav-dropdown">
            <select id="options-dropdown" onchange="navigateToPage(this)">
                <option value="" style="display:none">Options</option>
                <option value="seller_dashboard.php">Home</option>
                <option value="meal_upload.php">Upload Meal</option>
                <option value="track_orders.php">Orders</option>
                <option value="pending_orders.php">Accepted Orders</option>
                <option value="transactions.php">Transactions</option>
                <option value="user_edit.php">Edit User</option>
                <option value="logout.php">Logout</option>
            </select>
        </div>
    </div>
    <!-- Meal Upload Section -->
    <!-- Meal Upload Section -->
    <div class="section">
        <h2>Upload Meal</h2>
        <form method="POST" action="meal_upload.php" enctype="multipart/form-data">
            <label>Meal Name:</label><br>
            <input type="text" name="meal_name" required><br>

            <label>Description:</label><br>
            <textarea name="description" required></textarea><br>

            <label>Price:</label><br>
            <input type="number" name="price" step="0.01" required><br>

            <label>Meal Image:</label><br>
            <input type="file" name="meal_image" accept="image/*" required><br>


            <h3>Select Rice Options:</h3><br>
            <label>
                <input type="checkbox" name="rice_options[]" value="1 cup">
                1 Cup Rice - Price: <input type="number" name="rice_price_1" value=""
                    placeholder="Enter RICE price for 1 cup" required>
            </label><br>
            <label>
                <input type="checkbox" name="rice_options[]" value="2 cups">
                2 Cups Rice - Price: <input type="number" name="rice_price_2" value=""
                    placeholder="Enter RICE price for 2 cup" required>

            </label><br>

            <h3>Upload Drinks:</h3>
            <div id="drink-list">
                <!-- Dynamic list of drinks with prices will appear here -->
            </div>
            <input type="text" id="drink-input" placeholder="Type a drink and click Add">
            <input type="number" id="drink-price-input" placeholder="Price">
            <button type="button" class="btn" onclick="addDrink()">Add Drink</button><br><br>

            <!-- Hidden input to store drink list -->
            <input type="hidden" name="drinks" id="drinks">
            <!-- Hidden input to store drink prices -->
            <input type="hidden" name="drinks_price" id="drinks_price">

            <button type="submit" class="btn" name="upload_meal">Upload Meal</button>

            <script>
                let drinks = [];
                let drinkPrices = [];

                function addDrink() {
                    const drinkInput = document.getElementById('drink-input');
                    const drinkPriceInput = document.getElementById('drink-price-input');
                    const drinkValue = drinkInput.value.trim();
                    const drinkPrice = drinkPriceInput.value.trim();

                    if (drinkValue && drinkPrice) {
                        drinks.push(drinkValue);
                        drinkPrices.push(drinkPrice);
                        drinkInput.value = '';
                        drinkPriceInput.value = '';
                        updateDrinkList();
                    } else {
                        alert('Please provide both drink name and price.');
                    }
                }

                function updateDrinkList() {
                    const drinkList = document.getElementById('drink-list');
                    drinkList.innerHTML = drinks.map((drink, index) => `
                    <div>${drink} - Price: ${drinkPrices[index]} Pesos <button type="button" onclick="removeDrink(${index})">Remove</button></div>`).join('');
                    document.getElementById('drinks').value = drinks.join(',');
                    document.getElementById('drinks_price').value = drinkPrices.join(',');
                }

                function removeDrink(index) {
                    drinks.splice(index, 1);
                    drinkPrices.splice(index, 1);
                    updateDrinkList();
                }
            </script>


</body>

</html>