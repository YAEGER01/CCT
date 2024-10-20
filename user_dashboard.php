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
                echo "<a href='meal.php?seller_id=" . htmlspecialchars($store['id']) . "' class='view-meals'>View Meals</a>";
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
                case 'view_orders':
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
            body {
                font-family: 'Roboto', sans-serif;
                margin: 0;
                padding: 0;
                background-color: #F2F2F2;
            }

            .header {
                background-color: #ffffff;
                color: black;
                padding: 20px;
                text-align: center;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .site_name h2 {
                margin: 0;
            }

            .form-container {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-left: auto;
            }

            select {
                border-radius: 10px;
            }

            .action-select {
                padding: 10px;
                font-size: 16px;
                margin-left: 10px;
            }

            .store-container {
                padding: 20px;
                max-width: 1200px;
                margin: 20px auto;
                background-color: white;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .stores-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                justify-content: space-around;
            }

            .store {
                background-color: #fff;
                border: 1px solid #ddd;
                padding: 20px;
                text-align: center;
                width: 250px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
            }

            .store h3 {
                margin: 0 0 10px;
            }

            .store .view-meals {
                background-color: #333;
                color: white;
                text-decoration: none;
                padding: 10px 20px;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }

            .store .view-meals:hover {
                background-color: #555;
            }

            .store:hover {
                transform: translateY(-5px);
            }

            @media (max-width: 768px) {
                .stores-grid {
                    flex-direction: column;
                    align-items: center;
                }

                .store {
                    width: 80%;
                }
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
                        <option value="view_orders">View Orders</option>
                        <option value="edit_profile">Edit Profile</option>
                        <option value="logout">Logout</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Stores Section -->
        <div class="store-container">
            <h2>Available Stores</h2>
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