<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Optional: You can fetch user and order details from the database to display them
// For now, we will just show a confirmation message

$user_id = $_SESSION['user_id']; // Get the logged-in user ID
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .message {
            font-size: 20px;
            margin: 20px 0;
        }
        .back-button {
            margin-top: 20px;
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Confirmation</h1>
    </div>

    <div class="message">
        <h2>Your order has been placed successfully!</h2>
        <p>Thank you for your purchase. You will receive an email confirmation shortly.</p>
    </div>

    <a href="user_dashboard.php" class="back-button">Back to Dashboard</a>
</body>
</html>
