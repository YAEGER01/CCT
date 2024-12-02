<?php
session_start();
include 'db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

// Get meal ID from query parameter
$meal_id = mysqli_real_escape_string($conn, $_GET['meal_id']);

// Fetch meal details from the database
$sql = "SELECT * FROM meals WHERE id = '$meal_id' AND seller_id = {$_SESSION['user_id']}";
$result = mysqli_query($conn, $sql);
$meal = mysqli_fetch_assoc($result);

// If meal not found, redirect to dashboard
if (!$meal) {
    header("Location: seller_dashboard.php");
    exit();
}
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $meal_name = mysqli_real_escape_string($conn, $_POST['meal_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $rice_options = isset($_POST['rice_options']) ? implode(', ', $_POST['rice_options']) : '';
    $rice_price_1 = !empty($_POST['rice_price_1']) ? mysqli_real_escape_string($conn, $_POST['rice_price_1']) : NULL;
    $rice_price_2 = !empty($_POST['rice_price_2']) ? mysqli_real_escape_string($conn, $_POST['rice_price_2']) : NULL;
    $drinks = mysqli_real_escape_string($conn, $_POST['drinks']);
    $drinks_price = !empty($_POST['drinks_price']) ? mysqli_real_escape_string($conn, $_POST['drinks_price']) : NULL;

    // Build the dynamic SQL query for non-empty fields
    $update_fields = [
        "meal_name='$meal_name'",
        "description='$description'",
        "price='$price'"
    ];

    if ($rice_options)
        $update_fields[] = "rice_options='$rice_options'";
    if ($rice_price_1)
        $update_fields[] = "rice_price_1='$rice_price_1'";
    if ($rice_price_2)
        $update_fields[] = "rice_price_2='$rice_price_2'";
    if ($drinks)
        $update_fields[] = "drinks='$drinks'";
    if ($drinks_price)
        $update_fields[] = "drinks_price='$drinks_price'";

    // Check if a new image is uploaded
    if (isset($_FILES['meal_image']) && $_FILES['meal_image']['error'] === 0) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES["meal_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name;

        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($file_type != "jpg" && $file_type != "png" && $file_type != "jpeg" && $file_type != "gif") {
            echo "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif (move_uploaded_file($_FILES["meal_image"]["tmp_name"], $target_file)) {
            $update_fields[] = "image='$target_file'";
        } else {
            echo "Error uploading file.";
        }
    }

    // Convert the array into a string for SQL query
    $sql = "UPDATE meals SET " . implode(', ', $update_fields) . " WHERE id='$meal_id' AND seller_id={$_SESSION['user_id']}";

    if (mysqli_query($conn, $sql)) {
        echo "Meal updated successfully!";
        header("Location: seller_dashboard.php"); // Redirect to seller dashboard after successful update
        exit();
    } else {
        echo "Error updating meal: " . mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Meal</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

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
            font-family: 'MyCustomFont1', sans-serif;
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

        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        .form-container h2,
        h3 {
            font-size: 22px;
            color: #333;
            margin-bottom: 15px;
            font-family: 'MyCustomFont2', sans-serif;
        }

        label {
            font-size: 16px;
            color: #555;
            font-family: 'MyCustomFont2', sans-serif;
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
            font-family: 'MyCustomFont2', sans-serif;
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
            font-family: 'MyCustomFont2', sans-serif;
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

    <div class="form-container">
        <h2>Edit Meal</h2>
        <br>
        <hr>
        <br>
        <form method="POST" action="edit_meal.php?meal_id=<?php echo $meal_id; ?>" enctype="multipart/form-data">
            <label>Meal Name:</label>
            <input type="text" name="meal_name" value="<?php echo htmlspecialchars($meal['meal_name']); ?>" required>

            <label>Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($meal['description']); ?></textarea>

            <label>Price:</label>
            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($meal['price']); ?>"
                required>
            <label>Meal Image:</label>
            <input type="file" name="meal_image" accept="image/*">
            <br>
            <br>
            <h3>Select Rice Options:</h3><br>
            <label>
                <input type="checkbox" name="rice_options[]" value="1 cup">
                1 Cup Rice - Price: <input type="number" name="rice_price_1" value=""
                    placeholder="Enter RICE price for 1 cup">
            </label><br>
            <label>
                <input type="checkbox" name="rice_options[]" value="2 cups">
                2 Cups Rice - Price: <input type="number" name="rice_price_2" value=""
                    placeholder="Enter RICE price for 2 cup">

            </label><br>

            <h3>Upload Drinks:</h3>
            <div id="drink-list">
                <!-- Dynamic list of drinks with prices will appear here -->
            </div>
            <input type="text" id="drink-input" placeholder="Type a drink and click Add">
            <input type="number" id="drink-price-input" placeholder="Price">
            <button class="btn" type="button" onclick="addDrink()">Add Drink</button><br><br>

            <!-- Hidden input to store drink list -->
            <input type="hidden" name="drinks" id="drinks">
            <!-- Hidden input to store drink prices -->
            <input type="hidden" name="drinks_price" id="drinks_price">
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
            <br>
            <br>

            <br>
            <br>
            <button class="btn" type="submit">Update Meal</button>
            <button class="btn">Cancel</button>
        </form>
    </div>

</body>

</html>