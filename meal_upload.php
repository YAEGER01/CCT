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
    $drink_prices = isset($_POST['drink_prices']) ? mysqli_real_escape_string($conn, $_POST['drink_prices']) : ''; // New: drink prices

    // Image upload handling (same as before)
    if (isset($_FILES['meal_image']) && $_FILES['meal_image']['error'] === 0) {
        $target_dir = "uploads/"; // Directory to store uploaded images
        $file_name = basename($_FILES["meal_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name; // Unique name using time

        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["meal_image"]["tmp_name"], $target_file)) {
                // Insert meal data including rice options, drinks, drink prices, and the image path
                $sql = "INSERT INTO meals (name, description, price, image, rice_options, rice_price_1, rice_price_2, drinks, drink_prices, seller_id) 
                        VALUES ('$meal_name', '$description', '$price', '$target_file', '$rice_options', '$rice_price_1', '$rice_price_2', '$drinks', '$drink_prices', {$_SESSION['user_id']})";
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
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f9;
            padding: 20px;
        }

        .header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            color: #333;
        }

        .header .logout {
            background-color: #6c63ff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }

        .section {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
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
            width: 100%;
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

        @media (max-width: 768px) {

            .header,
            .section {
                flex-direction: column;
                align-items: flex-start;
            }

            .header h1 {
                font-size: 20px;
            }

            .logout {
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <h1>Upload one of your meal</h1>
        <a href="seller_dashboard.php" class="logout">Back to Dashboard</a>
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
                1 Cup Rice - Price: <input type="number" name="rice_price_1" value="" placeholder="Enter RICE price for 1 cup" required>
            </label><br>
            <label>
                <input type="checkbox" name="rice_options[]" value="2 cups">
                2 Cups Rice - Price: <input type="number" name="rice_price_2" value="" placeholder="Enter RICE price for 2 cup" required> Pesos

            </label><br>

            <h3>Upload Drinks:</h3>
            <div id="drink-list">
                <!-- Dynamic list of drinks with prices will appear here -->
            </div>
            <input type="text" id="drink-input" placeholder="Type a drink and click Add">
            <input type="number" id="drink-price-input" placeholder="Price">
            <button type="button" onclick="addDrink()">Add Drink</button><br><br>

            <!-- Hidden input to store drink list -->
            <input type="hidden" name="drinks" id="drinks">

            <!-- Hidden input to store drink prices -->
            <input type="hidden" name="drink_prices" id="drink_prices">

            <button type="submit" name="upload_meal">Upload Meal</button>

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
                    document.getElementById('drink_prices').value = drinkPrices.join(',');
                }

                function removeDrink(index) {
                    drinks.splice(index, 1);
                    drinkPrices.splice(index, 1);
                    updateDrinkList();
                }
            </script>


</body>

</html>