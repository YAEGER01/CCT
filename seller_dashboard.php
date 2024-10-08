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
            echo "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif (move_uploaded_file($_FILES["meal_image"]["tmp_name"], $target_file)) {
            // Insert meal data including the image path
            $sql = "INSERT INTO meals (name, description, price, image, seller_id) 
                    VALUES ('$meal_name', '$description', '$price', '$target_file', {$_SESSION['user_id']})";
            if (mysqli_query($conn, $sql)) {
                echo "";
            } else {
                echo "Error uploading meal: " . mysqli_error($conn);
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Please upload a valid image.";
    }
}

// Handle meal deletion
if (isset($_POST['delete_meal'])) {
    $meal_id = mysqli_real_escape_string($conn, $_POST['meal_id']);
    $sql = "DELETE FROM meals WHERE id = '$meal_id' AND seller_id = {$_SESSION['user_id']}";
    if (mysqli_query($conn, $sql)) {
        echo "";
    } else {
        echo "Error deleting meal: " . mysqli_error($conn);
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
    <style>
       
       
        .section { margin: 20px; }
        .meal { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
        .meal img { width: 100px; height: 100px; object-fit: cover; }
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .logout {
            float: right;
            margin-top: -35px;
            margin-right: 15px;
            background-color: red;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
        }
        .store-container {
            margin: 20px;
        }
        .store {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .view-meals {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h1>Welcome, <?php echo htmlspecialchars($username); ?> (Seller Dashboard)</h1>
    <a href="track_orders.php" class="logout    ">Track Orders</a>
    <a href="transactions.php" class="logout    ">Transactions</a>
    <a href="pending_orders.php" class="logout    ">Pending Orders</a>
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
    <h2>Your Meals</h2>
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
