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
    
    // Image upload handling
    if (isset($_FILES['meal_image']) && $_FILES['meal_image']['error'] === 0) {
        $target_dir = "uploads/"; // Directory to store uploaded images
        $file_name = basename($_FILES["meal_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name; // Unique name using time
        
        // Allow only certain file types (JPEG, PNG, GIF)
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if ($file_type != "jpg" && $file_type != "png" && $file_type != "jpeg" && $file_type != "gif") {
            echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        } elseif (move_uploaded_file($_FILES["meal_image"]["tmp_name"], $target_file)) {
            // Insert meal data including the image path
            $sql = "INSERT INTO meals (name, description, price, image, seller_id) 
                    VALUES ('$meal_name', '$description', '$price', '$target_file', {$_SESSION['user_id']})";
            if (!mysqli_query($conn, $sql)) {
                echo "<script>alert('Error uploading meal: " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "');</script>";
            }
        } else {
            echo "<script>alert('Error uploading file.');</script>";
        }
    } else {
        echo "<script>alert('Please upload a valid image.');</script>";
    }
}

// Handle meal deletion
if (isset($_POST['delete_meal'])) {
    $meal_id = mysqli_real_escape_string($conn, $_POST['meal_id']);
    
    // First, delete any associated orders
    $deleteOrdersQuery = "DELETE FROM orders WHERE meal_id = '$meal_id'";
    mysqli_query($conn, $deleteOrdersQuery);

    // Now delete the meal
    $sql = "DELETE FROM meals WHERE id = '$meal_id' AND seller_id = {$_SESSION['user_id']}";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Meal deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting meal: " . mysqli_real_escape_string($conn, mysqli_error($conn)) . "');</script>";
    }
}

// Fetch meals by the seller
$meals_query = "SELECT * FROM meals WHERE seller_id = {$_SESSION['user_id']}";
$meals_result = mysqli_query($conn, $meals_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
    <style>
     body {
        font-family: Arial, sans-serif;
        background-color: #2b2b2b; /* Grayish-black background */
        color: white; /* White text for contrast */
    }

    .header {
        background-color: #6a0dad; /* Purple header */
        color: white;
        padding: 15px;
        text-align: center;
        border-bottom: 5px solid #4b0082; /* Darker purple border */
        border-radius: 0 0 15px 15px; /* Rounded bottom corners */
    }

    .logout {
        background-color: red;
        color: white;
        padding: 10px;
        text-decoration: none;
        border-radius: 5px;
        margin: 5px;
        transition: background-color 0.3s ease;
    }

    .logout:hover {
        background-color: darkred;
    }

    .section {
        margin: 20px;
        background-color: #333; /* Dark gray background */
        padding: 20px;
        border-radius: 15px; /* Rounded corners for sections */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Subtle shadow for depth */
    }

    h2 {
        color: #6a0dad; /* Purple headings */
    }

    input, textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #555; /* Grayish-black border */
        border-radius: 8px; /* Rounded corners for form elements */
        background-color: #444; /* Dark background for inputs */
        color: white;
    }

    button {
        background-color: #6a0dad; /* Purple button */
        color: white;
        padding: 10px;
        border: none;
        border-radius: 8px; /* Rounded corners for buttons */
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #4b0082; /* Darker purple on hover */
    }

    .meal {
        background-color: #444; /* Dark background for meal items */
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 12px; /* Rounded corners for meal items */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Subtle shadow for depth */
    }

    .meal img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 10px; /* Rounded corners for images */
    }

    .view-meals {
        background-color: #6a0dad; /* Purple button for view meals */
        color: white;
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .view-meals:hover {
        background-color: #4b0082; /* Darker purple on hover */
    }

    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?> (Seller Dashboard)</h1>
    <a href="track_orders.php" class="logout">Track Orders</a>
    <a href="transactions.php" class="logout">Transactions</a>
    <a href="pending_orders.php" class="logout">Pending Orders</a>
    <a href="logout.php" class="logout" style="float: right; color: white;">Logout</a>
</div>

<!-- Meal Upload Section -->
<div class="section">
    <h2>Upload Meal</h2>
    <form method="POST" action="seller_dashboard.php" enctype="multipart/form-data">
        <label>Meal Name:</label><br>
        <input type="text" name="meal_name" required><br>
        <label>Description:</label><br>
        <textarea name="description" required></textarea><br>
        <label>Price:</label><br>
        <input type="number" name="price" step="0.01" required><br>
        <label>Meal Image:</label><br>
        <input type="file" name="meal_image" accept="image/*" required><br>
        <button type="submit" name="upload_meal">Upload Meal</button>
    </form>
</div>

<!-- Existing Meals Section -->
<div class="section">
    <h2>Uploaded Meals</h2>
    <?php while ($meal = mysqli_fetch_assoc($meals_result)): ?>
    <div class="meal">
        <h3><?php echo htmlspecialchars($meal['name']); ?></h3>
        <p><?php echo htmlspecialchars($meal['description']); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($meal['price']); ?></p>
        <?php if (!empty($meal['image'])): ?>
            <img src="<?php echo htmlspecialchars($meal['image']); ?>" alt="Meal Image">
        <?php endif; ?>
        
        <!-- Delete Form -->
        <form method="POST" action="seller_dashboard.php">
            <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
            <button type="submit" name="delete_meal">Delete Meal</button>
        </form>
        
        <!-- Edit Form Button (Redirects to edit_meal.php) -->
        <form method="GET" action="edit_meal.php">
            <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">
            <button type="submit">Edit Meal</button>
        </form>
    </div>
    <?php endwhile; ?>
</div>

</body>
</html>
