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
            background-color: #F2F2F2;
            /* Grayish-black background */
            color: white;
            /* White text for contrast */
        }

        .header {
            background-color: #6a0dad;
            /* Purple header */
            color: white;
            padding: 15px;
            text-align: center;
            border-bottom: 5px solid #4b0082;
            /* Darker purple border */
            border-radius: 0 0 15px 15px;
            /* Rounded bottom corners */
        }

        .message {
            font-size: 20px;
            margin: 20px 0;
            color: #333;
            background-color: #F2F2F2;
            /* Dark grayish-black background for message */
            padding: 20px;
            border-radius: 15px;
            /* Rounded corners for message container */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            /* Slight shadow for depth */
        }

        .back-button {
            margin-top: 20px;
            background-color: #6a0dad;
            /* Purple for back button */
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 10px;
            /* Rounded corners for button */
            display: inline-block;
            transition: background-color 0.3s ease;
            /* Smooth hover transition */
        }

        .back-button:hover {
            background-color: #4b0082;
            /* Darker purple on hover */
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Order Confirmation</h1>
    </div>

    <div class="message">
        <h2>Your order has been placed successfully!</h2>
        <p>Thank you for your purchase. Please pay as you pick-up your meal, Eat Well bb. </p>
    </div>

    <a href="user_dashboard.php" class="back-button">Back to Dashboard</a>
</body>

</html>