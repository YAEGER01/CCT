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
    /* Root Variables */
    :root {
        --primary-color: #6A5ACD;
        --secondary-color: #F2F2F2;
        --accent-color: #555;
        --text-color: #333;
        --font-primary: 'Roboto', sans-serif;
        --shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        --transition: all 0.3s ease;
    }

    /* Global Styles */
    body {
        font-family: var(--font-primary);
        margin: 0;
        padding: 0;
        background-color: var(--secondary-color);
        scroll-behavior: smooth;
        overflow-x: hidden;
        background: -webkit-linear-gradient(
        to right,
        #24243e,
        #302b63,
        #0f0c29
  ); /* Chrome 10-25, Safari 5.1-6 */
  background: linear-gradient(
    to right,
    #24243e,
    #302b63,
    #0f0c29
  );
    }

    /* Header Styling */
    .header {
        background-color: #ffffff;
        color: var(--text-color);
        padding: 20px;
        text-align: center;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 8px;
        box-shadow: var(--shadow);
    }

    .site_name h2 {
        margin: 0;
    }

    /* Form Container Styling */
    .form-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-left: auto;
    }

    select,
    .action-select {
        padding: 10px;
        font-size: 16px;
        border-radius: 10px;
        background-color: var(--primary-color);
        border: 1px solid var(--primary-color);
        color: #fff;
        transition: var(--transition);
    }

    select:hover,
    .action-select:hover {
        background-color: var(--accent-color);
    }

    /* Store Container */
    .store-container {
        padding: 20px;
        max-width: 1200px;
        margin: 20px auto;
        background-color: #fff;
        box-shadow: var(--shadow);
        border-radius: 10px;
        min-height: 80vh;
    }

    /* Grid Layout for Stores */
    .stores-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    /* Store Card Styling */
    .store {
        background-image: url('CCT/images/PaikkotNaLogo.jpg');
        background-size: cover;
        background-position: center;
        border: 1px solid #ddd;
        padding: 20px;
        text-align: center;
        box-shadow: var(--shadow);
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: transform 0.3s ease;
    }

    .store h3 {
        margin: 0 0 10px;
        color: var(--text-color);
    }

    .store .view-meals {
        background-color: var(--primary-color);
        color: #fff;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 5px;
        transition: var(--transition);
    }

    .store .view-meals:hover {
        background-color: var(--accent-color);
    }

    .store:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }

    /* Link Styling */
    a {
        text-decoration: none;
        padding: 15px 30px;
        background-color: var(--primary-color);
        color: #fff;
        border-radius: 12px;
        font-size: 16px;
        font-weight: bold;
        display: inline-block;
        transition: var(--transition);
    }

    a:hover {
        background-color: var(--accent-color);
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .store-container {
            padding: 15px;
            margin-top: 5%;
        }

        .header {
            flex-direction: column;
            padding: 15px;
            text-align: center;
        }

        .stores-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .store {
            width: 100%;
            height: auto;
        }
    }

    @media (max-width: 480px) {
        select,
        .action-select {
            padding: 8px;
            font-size: 14px;
        }

        .store h3 {
            font-size: 1em;
        }

        .store .view-meals {
            padding: 8px 15px;
            font-size: 14px;
        }

        a {
            padding: 10px 20px;
            font-size: 14px;
        }
    }

    /* Smooth Transition */
    .search-form-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-left: auto;
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

        <?php
        // Close the result set and connection
        $result->close();
        $conn->close();
        ?>
    </body>

    </html>