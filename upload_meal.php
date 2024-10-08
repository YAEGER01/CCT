<?php
session_start();
include 'db.php';
if ($_SESSION['role'] != 'seller') {
    die("Access denied!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $seller_id = $_SESSION['user_id'];

    $sql = "INSERT INTO meals (name, description, price, seller_id) VALUES ('$name', '$description', '$price', '$seller_id')";
    if (mysqli_query($conn, $sql)) {
        echo "Meal uploaded!";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>
<form method="post" action="upload_meal.php">
    <input type="text" name="name" placeholder="Meal Name" required>
    <textarea name="description" placeholder="Description"></textarea>
    <input type="text" name="price" placeholder="Price" required>
    <button type="submit">Upload Meal</button>
</form>
