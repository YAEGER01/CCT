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
function renderStores($result) {
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
            background-color: #2E2E2E; /* Grayish black background */
        }
        .header {
            background-color: #6A5ACD; /* Purple header */
            color: white;
            padding: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
        }
        .nav-buttons {
            display: flex;
            gap: 10px;
        }
        .logout, .cart {
            background-color: #383838; /* Darker grayish black */
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout:hover, .cart:hover {
            background-color: #4A4A4A; /* Lighter grayish black on hover */
        }
        .store-container {
            margin: 20px auto;
            max-width: 1200px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px; /* Space between store items */
            padding: 20px;
        }
        .store {
            background-color: #383838; /* Dark grayish black for store card */
            border: 1px solid #6A5ACD; /* Purple border */
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .store:hover {
            transform: scale(1.05);
        }
        .view-meals {
            background-color: #6A5ACD; /* Purple button */
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .view-meals:hover {
            background-color: #5a4db1; /* Darker purple on hover */
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #D3D3D3; /* Light gray text */
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Welcome, <?php echo $username; ?>!</h1>
        <div class="nav-buttons">
            <a href="cart.php" class="cart">Cart</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <!-- Stores Section -->
    <div class="store-container">
        <h2>Available Stores</h2>
        <?php renderStores($result); ?>

        <?php
        // Close the result set and connection
        $result->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
