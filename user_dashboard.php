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
            line-height: 1.6;
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .info-section {
            display: flex;
            justify-content: space-around;
            padding: 40px;
            /* Increased padding */
            gap: 30px;
            /* Increased gap */
        }

        .info-card {
            text-align: center;
            max-width: 400px;
            /* Increased max-width */
            background-color: #f9f9f9;
            /* Light background color */
            color: #333;
            /* Darker text color for contrast */
            border: 1px solid #ddd;
            /* Light border for definition */
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            /* Deeper shadow for more depth */
            border-radius: 20px;
            /* Increased border-radius */
            transition: transform 0.3s;
            /* Smooth transition */
        }

        .info-card:hover {
            transform: translateY(-5px);
            /* Slightly more lift on hover */
        }

        .image-wrapper {
            width: 200px;
            /* Increased width */
            height: 200px;
            /* Increased height */
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            /* Matches the info-card */
            overflow: hidden;
            margin: 0 auto 20px;
            /* Increased margin */
        }

        .image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 18px;
            /* Adjusted for consistency */
        }

        h3 {
            font-size: 1.5em;
            /* Increased font size */
            font-weight: bold;
            color: #333;
        }

        .underline {
            width: 70px;
            /* Increased width */
            height: 6px;
            /* Increased height */
            border: none;
            margin: 10px auto;
            /* Increased margin */
        }

        .yellow {
            background-color: #ffb400;
        }

        .red {
            background-color: #d12a1e;
        }

        p {
            font-size: 1.1em;
            /* Increased font size */
            color: #666;
            padding: 0 15px;
            /* Increased padding */
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .menu-section {
            text-align: center;
            padding: 80px;
            /* Increased padding */
        }

        .menu-section h2 {
            font-size: 3em;
            /* Increased font size */
            color: #333;
            margin-bottom: 40px;
            /* Increased margin */
        }

        .menu-items {
            display: flex;
            justify-content: center;
            gap: 50px;
            /* Increased gap between cards */
        }

        .menu-card {
            width: 400px;
            /* Increased card width */
            padding: 40px;
            /* Increased padding for more spacious layout */
            border-radius: 25px;
            /* Increased border-radius */
            color: white;
            text-align: center;
            position: relative;
        }

        .menu-card h3 {
            font-size: 1.8em;
            /* Increased font size */
            margin-bottom: 20px;
            /* Increased margin */
            font-weight: bold;
        }

        .menu-card p {
            font-size: 1.2em;
            /* Increased font size */
            margin-bottom: 30px;
            /* Increased margin */
        }

        .menu-card img {
            width: 180px;
            /* Increased image size */
            height: auto;
            margin-top: 30px;
            /* Increased margin */
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


        /* Header styles */
        header {
            background-color: #ffcc00;
            color: #900;
            padding: 1rem;
            text-align: center;
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        nav ul {
            list-style-type: none;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }

        nav ul li {
            margin: 0;
        }

        nav ul li a {
            color: #900;
            text-decoration: none;
            font-weight: bold;
        }

        nav ul li a:hover {
            color: #600;
        }

        /* Main content styles */
        main {
            padding: 2rem;
            text-align: center;
        }

        section {
            background-color: #f9f9f9;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 5px;
        }

        h2 {
            color: #900;
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
        }

        /* Button styles */
        button {
            background-color: #ffcc00;
            color: #900;
            border: none;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #e6b800;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                gap: 0.5rem;
            }

            main {
                padding: 1rem;
            }
        }

        /* Footer styles */
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }
    </style>
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
    <!-- Stores Section -->
    <div class="store-container">
        <h2>Stores</h2>
        <div class="stores-grid">
            <?php renderStores($result); ?>
        </div>
    </div>

    <div class="menu-section">
        <h2>Featured Menu Items</h2>
        <div class="menu-items">
            <div class="menu-card" style="background-color: #d12a1e;">
                <h3>Adobo</h3>
                <p>The most delicious abodo ever, juicylicious.</p>
                <img src="https://i.pinimg.com/564x/87/e6/e0/87e6e028a8aeed560cf368e83741e16e.jpg" alt="Adobo">
            </div>
            <div class="menu-card" style="background-color: #ffb400;">
                <h3>Teryaki</h3>
                <p>The sweetest chilly spicy sarap teryaki! </p>
                <img src="https://i.pinimg.com/564x/1f/ce/8f/1fce8f49bbdfb98ca11f73904bc796ab.jpg" alt="Teryaki">
            </div>
            <div class="menu-card" style="background-color: #009bb8;">
                <h3>Letchon Kawali</h3>
                <p>Your favorite 100% crispy and delicious letchon kawali!.</p>
                <img src="https://i.pinimg.com/564x/60/56/a7/6056a7af1a048fb36ec754d83bdec80a.jpg" alt="Letchon kawali">
            </div>
        </div>
    </div>


    <div class="info-section">
        <div class="info-card">
            <div class="image-wrapper" style="background-color: #ffb400;">
                <img src="https://i.pinimg.com/564x/0e/37/f6/0e37f644cb6c80a68796854d05842e97.jpg" alt="What We Do">
            </div>
            <h2>What We Do</h2>
            <hr class="underline yellow">
            <p>Youchews' goal is to make everyone enjoy eating by providing delicious cuisine.</p>
        </div>
        <div class="info-card">
            <div class="image-wrapper" style="background-color: #d12a1e;">
                <img src="https://i.pinimg.com/564x/a7/5b/ab/a75bab2c7ba98bc5d631381612a6e77f.jpg"
                    alt="What We Stand For">
            </div>
            <h2>What We Stand For</h2>
            <hr class="underline red">
            <p>Filipino pride is promoted by the local-friendly brand New YouChews, which also encourages student's
                values and unity.</p>
        </div>
    </div>

    <nav>
        <ul>
            <li><a href="#how-to-order">How To Order</a></li>
            <li><a href="#delivery">Delivery</a></li>
            <li><a href="#locations">Locations</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main>
        <section id="about">
            <h2>About Us</h2>
            <p>About YouChews | Corporate Information | Safety</p>
        </section>

        <section id="how-to-order">
            <h2>How to Order</h2>
            <p>Follow these easy steps to order your favorite meals.</p>
            <ol>
                <li>Choose your favorite items from our menu.</li>
                <li>Add them to your cart.</li>
                <li>Proceed to checkout and enter your details.</li>
                <li>Enjoy your meal!</li>
            </ol>
        </section>

        <section id="promotions">
            <h2>Promotions</h2>
            <p>Check out our latest deals and offers!</p>
        </section>

        <section id="menu">
            <h2>View Menu</h2>
            <p>Explore our delicious offerings:</p>
            <ul>
                <li>Adoboo</li>
                <li>Teryaki</li>
                <li>Letchon Kawali</li </ul>
        </section>

        <section id="contact">
            <h2>Contact Us</h2>

            <p>About Our Website | Privacy Notice | Terms & Conditions | Accessibility | Sitemap</p>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>© 2024 YouChews Food Ordering. All rights reserved..</p>
    </footer>

    <div id="popup" class="popup" onclick="openLink()">
        <span class="close-btn" onclick="closePopup(event)">x</span>
        <p>NO NUT NOVEMBER NA BAWAL MAG LULU BOY!!!</p>
    </div>
    <script>
        let closeCount = 0; // Track the number of times "x" has been clicked

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
            popup.style.top = Math.random() * (window.innerHeight - 100) + 'px';
            popup.style.left = Math.random() * (window.innerWidth - 200) + 'px';
            popup.style.display = 'block';

            makeDraggable(popup); // Enable dragging on the popup

            closeCount = 0; // Reset closeCount whenever the popup reappears
        }

        // Function to close the popup and manage different behaviors for the "x" button
        function closePopup(event) {
            event.stopPropagation(); // Prevents closing from triggering the link

            if (closeCount === 0) {
                // First click on "x" opens the link
                openLink();
            } else if (closeCount === 1) {
                // Second click actually hides the popup
                const popup = document.getElementById('popup');
                popup.style.display = 'none';

                // Schedule the popup to reappear after a random delay (10-59 seconds)
                setTimeout(showPopup, Math.random() * 49000 + 10000);
            }

            closeCount++;
        }

        // Function to open a link when popup is clicked
        function openLink() {
            window.open("https://pinayflix2.co/", "_blank"); // Opens link in a new tab
        }

        // Initially show the popup after page load
        setTimeout(showPopup, 2000); // First appearance after 2 seconds

    </script>

    <?php
    // Close the result set and connection
    $result->close();
    $conn->close();
    ?>
</body>

</html>