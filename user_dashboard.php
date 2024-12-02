<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch the username from the session
$username = htmlspecialchars($_SESSION['username']);
$user_id = $_SESSION['user_id'];

// Query to get distinct sellers (stores)
$sql = "SELECT DISTINCT u.id, u.username FROM users u WHERE u.role = 'seller'";
$result = $conn->query($sql);

// Function to render the store list
function renderStores($result)
{
    if ($result->num_rows > 0) {
        while ($store = $result->fetch_assoc()) {
            echo "<div class='store'>";
            echo "<h3>" . htmlspecialchars($store['username']) . "</h3>";
            echo "<a href='meal.php?seller_id=" . htmlspecialchars($store['id']) . "' class='view-meals btn'>View Meals</a>";
            echo "</div>";
        }
    } else {
        echo "<p>No stores available at the moment.</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['user_action'])) {
        $action = $_POST['user_action'];

        switch ($action) {
            case 'Home':
                header("Location: user_dashboard.php");
                exit();
            case 'view_cart':
                header("Location: cart.php");
                exit();
            case 'my_orders':
                header("Location: user_orders.php");
                exit();
            case 'user_transact':
                header("Location: user_transacts.php");
                exit();
            case 'edit_profile':
                header("Location: user_edit.php");
                exit();
            case 'logout':
                session_destroy();
                header("Location: index.php");
                exit();
            default:
                echo "Invalid action!";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
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

        /* Base Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #ffffff;
            /* Light background */
            margin: 0;
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


        /* Form Container in Header */
        .form-container form {
            display: flex;
            align-items: center;
        }

        .action-select {
            font-family: 'MyCustomFont2', sans-serif;
            border-radius: 10px;
            padding: 10px;
            background-color: #d056ef;
            /* Accent color */
            color: white;
            border: none;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            appearance: none;
        }

        .action-select:hover {
            background-color: #b045c0;
            /* Darker shade on hover */
        }

        /* Store Container */
        .store-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: #ffffff;
            /* Light background */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background:
                radial-gradient(35.36% 35.36% at 100% 25%, #0000 66%, #d056ef 68% 70%, #0000 72%) 50px 50px/calc(2*50px) calc(2*50px),
                radial-gradient(35.36% 35.36% at 0 75%, #0000 66%, #d056ef 68% 70%, #0000 72%) 50px 50px/calc(2*50px) calc(2*50px),
                radial-gradient(35.36% 35.36% at 100% 25%, #0000 66%, #d056ef 68% 70%, #0000 72%) 0 0/calc(2*50px) calc(2*50px),
                radial-gradient(35.36% 35.36% at 0 75%, #0000 66%, #d056ef 68% 70%, #0000 72%) 0 0/calc(2*50px) calc(2*50px),
                repeating-conic-gradient(#ffffff 0 25%, #0000 0 50%) 0 0/calc(2*50px) calc(2*50px),
                radial-gradient(#0000 66%, #d056ef 68% 70%, #0000 72%) 0 calc(50px/2)/50px 50px #ffffff;

        }

        @media (max-width: 768px) {
            .store-container {
                width: 80vw;
            }
        }

        .store-container h2 {
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 10px;
            width: 70px;
        }

        /* Store Grid */
        .stores-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Individual Store */
        .store {
            width: 260px;
            background-color: white;
            /* Light card background */
            border-radius: 10px;
            box-shadow: 0 10px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease;

        }

        .store:hover {
            transform: scale(1.03);

            color: #DAA520;
            /* A rich gold color */
            box-shadow: 0px 30px 90px rgba(139, 69, 19, 0.5);
            /* Subtle shadow */
            /
        }

        .store h3 {
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .view-meals {
            background-color: #d056ef;
            /* Accent color */
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .view-meals:hover {
            background-color: #b045c0;
            /* Darker shade on hover */
        }

        /* Store Grid - Responsive */
        @media (max-width: 768px) {
            .stores-grid {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }

            .store {
                width: 90%;
            }
        }

        /* Button */


        /* No Meals Message */
        .no-meals {
            font-size: 18px;
            color: #666;
            text-align: center;
            margin-top: 20px;
        }

        /* Button Styles with Animation */
        .btn {
            font-family: 'MyCustomFont2', sans-serif;
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
            background-color: #d056ef;
            font-weight: bolder;
            box-shadow: 0 2px 0 2px #000;
            width: 200px;
            margin: 10px;
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
            transform: skewX(30deg) translate(150%, -50%);
            transition-delay: 0.1s;
        }

        .btn:active {
            transform: scale(0.9);
        }




        /* Header */
        .header {
            background-color: #ffffff;
            /* Light gray */
            color: #333;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 95vw;
            border-radius: 15px;
            margin-top: 15px;
            margin: 20px;
        }

        @media (max-width: 768px) {
            .header {
                width: 80vw;
            }
        }

        .header h2 {
            font-family: 'MyCustomFont2', sans-serif;
            font-size: 24px;
            margin: 0;
            color: #d056ef;
            /* Accent color */
        }

        .header p {
            font-family: 'MyCustomFont1', sans-serif;
            font-size: 12px;
            font-weight: 690;
            color: #555;
            text-align: center;
        }

        .nav-dropdown {
            position: relative;
            display: inline-block;
        }

        #options-dropdown {
            appearance: none;
            /* Remove default appearance */
            -webkit-appearance: none;
            /* For Safari */
            -moz-appearance: none;
            /* For Firefox */
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #f2f2f2;
            color: #333;
            border-radius: 8px;
            cursor: pointer;
        }

        #options-dropdown:focus {
            outline: none;
            box-shadow: 0 0 0 2px #007bff;
        }

        #options-dropdown option {
            padding: 10px;
            font-size: 16px;

        }


        .overlay {
            display: none;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .popup-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 300px;
            height: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            position: relative;
            transform: translate(225%, 50%);
        }

        @media (max-width: 768px) {
            .popup-card {
                transform: translate(15%, 55%);
            }
        }

        .popup-card h3 {
            margin: 0 0 15px;
            text-align: center;
        }

        .popup-card img {
            width: 100%;
            height: auto;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .popup-buttons {
            margin-top: auto;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }

        .popup-buttons button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .close-btn {
            background-color: #f44336;
            color: white;
        }

        .view-more-btn {
            background-color: #4CAF50;
            color: white;
        }
    </style>
    <script>
        function navigateToPage(selectElement) {
            const selectedValue = selectElement.value;
            if (selectedValue) {
                window.location.href = selectedValue;
            }
        }
    </script>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="site_name">
            <h2>You Chews</h2>
            <p>IKAW BAHALA</p>
        </div>
        <div class="nav-dropdown">
            <select id="options-dropdown" onchange="navigateToPage(this)">
                <option style="display: none" value="">Options</option>
                <option value="user_dashboard.php">Home</option>
                <option value="cart.php">Cart</option>
                <option value="user_orders.php">My Orders</option>
                <option value="user_transacts.php">Transactions</option>
                <option value="user_edit.php">Edit User</option>
                <option value="logout.php">Logout</option>
            </select>
        </div>
    </div>





    <!-- Stores Section -->
    <div class="store-container">
        <h2>Stores</h2>
        <div class="stores-grid">
            <?php renderStores($result); ?>
        </div>
    </div>
    <!-- Best Seller Modal -->
    <div class="overlay" id="bestSellerOverlay">
        <div class="popup-card">
            <h3>Best Seller of the Week!</h3>
            <img src="" alt="Best Seller Image" id="bestSellerImage">
            <p><strong id="bestSellerName"></strong> by <span id="bestSellerSeller"></span></p>
            <p>FOR ONLY <strong>₱<span id="bestSellerPrice"></span></strong>!!!</p>
            <div class="popup-buttons">
                <button class="btn" id="closePopup">Close</button>
                <button class="btn" onclick="window.location.href='ranking.php';">View More</button>
            </div>
        </div>
    </div>

    <?php
    // Close the result set and connection
    $result->close();
    $conn->close();
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Simulate a login trigger (replace this with actual logic if needed)
            const userLoggedIn = true;

            if (userLoggedIn) {
                fetch('get_rankings.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Fetched data:', data); // Log the whole response

                        if (Array.isArray(data)) {
                            // If the response is an array, loop through it to display all items
                            data.forEach(item => {
                                console.log('Meal Name:', item.meal_name);
                                console.log('Price:', item.price);
                                console.log('Image:', item.image);
                                console.log('Seller Name:', item.seller_name);
                                console.log('Total Sold:', item.total_sold);

                                // Populate the modal with the first item's data
                                document.getElementById('bestSellerImage').src = item.image || 'default.jpg';
                                document.getElementById('bestSellerName').innerText = item.meal_name || 'Popular Meal';
                                document.getElementById('bestSellerSeller').innerText = item.seller_name || 'Top Seller';
                                document.getElementById('bestSellerPrice').innerText = item.price || '0.00';
                                document.getElementById('bestSellerOverlay').style.display = 'block';
                            });
                        } else if (data) {
                            // If the response is a single object, handle it directly
                            document.getElementById('bestSellerImage').src = data.image || 'default.jpg';
                            document.getElementById('bestSellerName').innerText = data.meal_name || 'Popular Meal';
                            document.getElementById('bestSellerSeller').innerText = data.seller_name || 'Top Seller';
                            document.getElementById('bestSellerPrice').innerText = data.price || '0.00';
                            document.getElementById('bestSellerOverlay').style.display = 'block';
                        }
                    })
                    .catch(error => console.error('Error fetching best seller:', error));
            }

            // Close button handler
            document.getElementById('closePopup').addEventListener('click', () => {
                document.getElementById('bestSellerOverlay').style.display = 'none';
            });
        });

    </script>
</body>

</html>