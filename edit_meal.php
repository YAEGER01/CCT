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
            $sql = "UPDATE meals SET name='$meal_name', description='$description', price='$price', image='$target_file' WHERE id='$meal_id' AND seller_id={$_SESSION['user_id']}";
        } else {
            echo "Error uploading file.";
        }
    } else {
        // Update meal without new image
        $sql = "UPDATE meals SET name='$meal_name', description='$description', price='$price' WHERE id='$meal_id' AND seller_id={$_SESSION['user_id']}";
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
        body { font-family: Arial, sans-serif; }
        .form-container { width: 50%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
        label { display: block; margin-bottom: 10px; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 10px; margin-bottom: 20px; }
        button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Meal</h2>
    <form method="POST" action="edit_meal.php?meal_id=<?php echo $meal_id; ?>" enctype="multipart/form-data">
        <label>Meal Name:</label>
        <input type="text" name="meal_name" value="<?php echo htmlspecialchars($meal['name']); ?>" required>

        <label>Description:</label>
        <textarea name="description" required><?php echo htmlspecialchars($meal['description']); ?></textarea>

        <label>Price:</label>
        <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($meal['price']); ?>" required>

        <label>Meal Image:</label>
        <input type="file" name="meal_image" accept="image/*">
        <br>
        <button type="submit">Update Meal</button>
    </form>
</div>

</body>
</html>
