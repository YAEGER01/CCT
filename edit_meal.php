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
    $rice_price_1 = mysqli_real_escape_string($conn, $_POST['rice_price_1']); // New: price for 1 cup rice
    $rice_price_2 = mysqli_real_escape_string($conn, $_POST['rice_price_2']); // New: price for 2 cups rice

    $drinks = mysqli_real_escape_string($conn, $_POST['drinks']);
    $drinks_price = mysqli_real_escape_string($conn, $_POST['drinks_price']);

    // Check if a new image is uploaded
    if (isset($_FILES['meal_image']) && $_FILES['meal_image']['error'] === 0) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES["meal_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name;

        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($file_type != "jpg" && $file_type != "png" && $file_type != "jpeg" && $file_type != "gif") {
            echo "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif (move_uploaded_file($_FILES["meal_image"]["tmp_name"], $target_file)) {
            // Update meal with new image
            $sql = "UPDATE meals SET meal_name='$meal_name', description='$description', price='$price', rice_options='$rice_options',  rice_price_1='$rice_price_1', rice_price_2='$rice_price_2', drinks='$drinks', drinks_price='$drinks_price', image='$target_file' WHERE id='$meal_id' AND seller_id={$_SESSION['user_id']}";
        } else {
            echo "Error uploading file.";
        }
    } else {
        // Update meal without new image
        $sql = "UPDATE meals SET meal_name='$meal_name', description='$description', price='$price', rice_options='$rice_options', rice_price='$rice_price', drinks='$drinks', drinks_price='$drinks_price' WHERE id='$meal_id' AND seller_id={$_SESSION['user_id']}";
    }

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
        body {
            font-family: Arial, sans-serif;
            background-color: #2b2b2b;
            /* Grayish-black background */
            color: white;
            /* White text for contrast */
            margin: 0;
            /* Remove default margin */
            padding: 0;
            /* Remove default padding */
        }

        .form-container {
            width: 50%;
            margin: 20px auto;
            /* Center the form */
            padding: 40px;
            /* Increased padding for better spacing */
            border: 1px solid #444;
            /* Darker grayish-black border */
            background-color: #333;
            /* Dark gray background */
            border-radius: 15px;
            /* Rounded corners */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            /* Subtle shadow for depth */
            text-align: center;
            /* Center text within the container */
        }

        label {
            display: block;
            margin-bottom: 8px;
            /* Reduced margin for labels */
            color: #6a0dad;
            /* Purple label */
            font-weight: bold;
            /* Make label text bold */
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 12px;
            /* Standard padding for inputs */
            margin-bottom: 25px;
            /* Increased space between inputs */
            border: 1px solid #555;
            /* Grayish-black border */
            border-radius: 8px;
            /* Rounded corners for input fields */
            background-color: #444;
            /* Dark background for inputs */
            color: white;
            /* White text for inputs */
            box-sizing: border-box;
            /* Ensure padding is included in width */
        }

        textarea {
            resize: vertical;
            /* Allow vertical resizing only */
            min-height: 120px;
            /* Increased minimum height for the textarea */
        }

        button {
            padding: 12px 24px;
            /* Increased padding for buttons */
            background-color: #6a0dad;
            /* Purple button */
            color: white;
            border: none;
            border-radius: 8px;
            /* Rounded corners for buttons */
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
            /* Increased space above the button */
            display: inline-block;
            /* Make the button an inline-block element */
        }

        button:hover {
            background-color: #4b0082;
            /* Darker purple on hover */
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2>Edit Meal</h2>
        <form method="POST" action="edit_meal.php?meal_id=<?php echo $meal_id; ?>" enctype="multipart/form-data">
            <label>Meal Name:</label>
            <input type="text" name="meal_name" value="<?php echo htmlspecialchars($meal['meal_name']); ?>" required>

            <label>Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($meal['description']); ?></textarea>

            <label>Price:</label>
            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($meal['price']); ?>"
                required>

            <br>
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
            <button type="button" onclick="addDrink()">Add Drink</button><br><br>

            <!-- Hidden input to store drink list -->
            <input type="hidden" name="drinks" id="drinks">
            <!-- Hidden input to store drink prices -->
            <input type="hidden" name="drinks_price" id="drinks_price">

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
            <label>Meal Image:</label>
            <input type="file" name="meal_image" accept="image/*">
            <br>
            <br>
            <br>
            <button type="submit">Update Meal</button>
        </form>
    </div>

</body>

</html>