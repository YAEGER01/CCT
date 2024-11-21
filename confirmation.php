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
            font-family: 'Poppins', Arial, sans-serif;
            font-weight: 300;
            line-height: 1.7;
            text-align: center;
            color: #ffeba7;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 0% 0% / 64px 64px,
                radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 32px 32px / 64px 64px,
                linear-gradient(#a43fc6 2px, transparent 2px) 0px -1px / 32px 32px,
                linear-gradient(90deg, #a43fc6 2px, #ffffff 2px) -1px 0px / 32px 32px #ffffff;
            background-size: 64px 64px, 64px 64px, 32px 32px, 32px 32px;
            background-color: #ffffff;
            animation: scroll-diagonal 10s linear infinite;
        }

        @keyframes scroll-diagonal {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 64px 64px;
            }
        }

        .message {
            font-size: 20px;
            margin: 20px 0;
            color: #333;
            background-color: #F2F2F2;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 75%;
            /* Responsive width */
            max-width: 600px;
            /* Max width */
            text-align: center;
            /* Center text */
        }

        .nessage h2 {
            font-family: 'MyCustomFont2', sans-serif;
        }

        .message p {
            font-family: 'MyCustomFont1', sans-serif;
            font-weight: 690;
        }

        .back-button {
            font-family: 'MyCustomFont2';
            margin-top: 20px;
            background-color: #6a0dad;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 10px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #4b0082;
        }
    </style>
</head>

<body>


    <div class="message">
        <h2>Your order has been placed successfully!</h2>
        <p>Thank you for your purchase. Please pay as you pick-up your meal, Eat Well bb. </p>
        <a href="user_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
    <br>

</body>

</html>