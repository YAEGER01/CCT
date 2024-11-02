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
            case 'edit_profile':
                header("Location: user_edit.php");
                exit();
            case 'logout':
                session_destroy();
                header("Location: login.php");
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
        /* Base Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #ffffff;
            /* Light background */
            margin: 0;
        }

        /* Header */
        .header {
            background-color: #f8f8f8;
            /* Light gray */
            color: #333;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h2 {
            font-size: 24px;
            margin: 0;
            color: #d056ef;
            /* Accent color */
        }

        .header p {
            font-size: 12px;
            color: #555;
        }

        /* Form Container in Header */
        .form-container form {
            display: flex;
            align-items: center;
        }

        .action-select {
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

        .store-container h2 {
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
        }

        .store h3 {
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
        button {
            background-color: #d056ef;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #b045c0;
        }

        /* No Meals Message */
        .no-meals {
            font-size: 18px;
            color: #666;
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            padding: 1.1em 2em;
            background: none;
            border: 2px solid #fff;
            font-size: 15px;
            color: #131313;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            border-radius: 12px;
            background-color: #ecd448;
            font-weight: bolder;
            box-shadow: 0 2px 0 2px #000;
        }

        .btn:before {
            content: "";
            position: absolute;
            width: 100px;
            height: 120%;
            background-color: #ff6700;
            top: 50%;
            transform: skewX(30deg) translate(-150%, -50%);
            transition: all 0.5s;
        }

        .btn:hover {
            background-color: #d056ef;
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

        .popup {
            display: none;
            position: fixed;
            width: 200px;
            padding: 20px;
            background-color: #ffdd57;
            border: 2px solid #000;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 9999;
            /* Ensure it's above everything else */
            cursor: pointer;
            /* Set cursor to pointer for interaction */
        }

        .close-btn {
            position: absolute;
            top: 2px;
            right: 4px;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
    <script>
        let closeCount = 0; // Track the number of times "x" has been clicked

        // Array of ad objects
        const ads = [
            { content: "Ad 1: Check out our sale!", link: "https://example1.com" },
            { content: "Ad 2: Don't miss these deals!", link: "https://example2.com" },
            { content: "Best Movies 2024!", link: "https://www.example3.com/domain/top10hd.com" },
        ];

        // Function to randomly select an ad
        function getRandomAd() {
            return ads[Math.floor(Math.random() * ads.length)];
        }

        // Function to make the popup draggable
        function makeDraggable(element) {
            let offsetX = 0, offsetY = 0, mouseX = 0, mouseY = 0;

            element.onmousedown = function (event) {
                event.preventDefault();
                mouseX = event.clientX;
                mouseY = event.clientY;
                document.onmousemove = dragElement;
                document.onmouseup = closeDragElement;
            };

            function dragElement(event) {
                event.preventDefault();
                offsetX = mouseX - event.clientX;
                offsetY = mouseY - event.clientY;
                mouseX = event.clientX;
                mouseY = event.clientY;

                element.style.top = (element.offsetTop - offsetY) + "px";
                element.style.left = (element.offsetLeft - offsetX) + "px";
            }

            function closeDragElement() {
                document.onmousemove = null;
                document.onmouseup = null;
            }
        }

        // Initialize the popup and make it draggable
        function showPopup() {
            const popup = document.getElementById('popup');
            const ad = getRandomAd(); // Get a random ad
            popup.innerHTML = `<span class="close-btn" onclick="closePopup(event)">x</span><p>${ad.content}</p>`;
            popup.dataset.link = ad.link; // Store the ad link in data attribute

            popup.style.top = Math.random() * (window.innerHeight - 100) + 'px';
            popup.style.left = Math.random() * (window.innerWidth - 200) + 'px';
            popup.style.display = 'block';

            makeDraggable(popup); // Enable dragging on the popup

            closeCount = 0; // Reset closeCount whenever the popup reappears
        }

        // Function to close the popup and manage different behaviors for the "x" button
        function closePopup(event) {
            event.stopPropagation(); // Prevents closing from triggering the link

            const popup = document.getElementById('popup');

            if (closeCount === 0) {
                // First click on "x" opens the link
                window.open(popup.dataset.link, "_blank");
            } else if (closeCount === 1) {
                // Second click actually hides the popup
                popup.style.display = 'none';

                // Schedule the popup to reappear after a random delay (10-59 seconds)
                setTimeout(showPopup, Math.random() * 49000 + 10000);
            }

            closeCount++;
        }

        // Function to open the link when the popup is clicked
        function openLink() {
            const popup = document.getElementById('popup');
            window.open(popup.dataset.link, "_blank"); // Opens link in a new tab
        }

        // Initially show the popup after page load
        setTimeout(showPopup, 2000); // First appearance after 2 seconds

    </script>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="site_name">
            <h2>You Chews</h2>
            <p>IKAW BAHALA</p>
        </div>
        <div class="form-container">
            <form action="user_dashboard.php" method="post">
                <select name="user_action" class="action-select" onchange="this.form.submit()">
                    <option value="">Options</option>
                    <option value="view_cart">View Cart</option>
                    <option value="edit_profile">Edit Profile</option>
                    <option value="logout">Logout</option>
                </select>
            </form>
        </div>
    </div>
    <div id="popup" class="popup" onclick="openLink()">
        <span class="close-btn" onclick="closePopup(event)">x</span>
        <p>HOT MOMS IN YOUR AREAA!!!</p>
    </div>




    <!-- Stores Section -->
    <div class="store-container">
        <h2>Stores</h2>
        <div class="stores-grid">
            <?php renderStores($result); ?>
        </div>
    </div>

    <?php
    // Close the result set and connection
    $result->close();
    $conn->close();
    ?>
</body>

</html>