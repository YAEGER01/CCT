    <?php
    // Start session and include database connection
    session_start();
    include 'db.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php"); // Redirect to login page if not logged in
        exit();
    }

    // Check if 'seller_id' is provided in the URL
    $seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : null;
    if (!$seller_id) {
        echo "<p>No store selected!</p>";
        exit();
    }

    // Fetch seller information
    $sellerQuery = "SELECT username FROM users WHERE id = ? AND role = 'seller'";
    $stmt = $conn->prepare($sellerQuery);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $sellerResult = $stmt->get_result();

    if ($sellerResult->num_rows === 0) {
        echo "<p>Store not found!</p>";
        exit();
    }

    // Fetch seller data
    $sellerData = $sellerResult->fetch_assoc();
    $seller_name = htmlspecialchars($sellerData['username']);

    // Fetch meals uploaded by this store (seller)
    // Fetch meals uploaded by this store (seller), including new columns for add-ons
    $mealsQuery = "SELECT id, name, description, price, image, rice_options, drinks FROM meals WHERE seller_id = ?";
    $stmt = $conn->prepare($mealsQuery);
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $mealsResult = $stmt->get_result();


    // Close the seller result set
    $sellerResult->close();
    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo "$seller_name's Meals"; ?></title>
        <style>
            :root {
                --primary-color: #6A5ACD;
                --secondary-color: #F2F2F2;
                --font-primary: 'Roboto', sans-serif;
            }

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

            .header h1 {
                margin: 0;
            }

            .message-button {
                padding: 10px 15px;
                background-color: #333;
                color: white;
                text-decoration: none;
                border-radius: 5px;

                padding: 10px 20px;
                background-color: var(--primary-color);
                border: none;
                color: white;
                font-size: 16px;
                font-weight: bold;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .message-button:hover {
                background-color: #555;
            }

            .back-button {
                display: block;
                margin: 20px auto;
                padding: 10px 15px;
                background-color: #333;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                text-align: center;
                width: fit-content;

                padding: 10px 20px;
                background-color: var(--primary-color);
                border: none;
                color: white;
                font-size: 16px;
                font-weight: bold;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .back-button:hover {
                background-color: #555;
            }

            .meal-container {
                max-width: 1200px;
                margin: 20px auto;
                padding: 20px;
                background-color: white;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .meal-container h2 {
                color: black;
                text-align: center;
            }

            .meal {
                display: flex;
                flex-direction: column;
                align-items: center;
                border: 1px solid #ddd;
                border-radius: 10px;
                padding: 20px;
                margin: 10px;
                background-color: #f9f9f9;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
            }

            .meal img {
                max-width: 100%;
                border-radius: 10px;
                height: auto;
            }

            .meal-details {
                text-align: center;
            }

            .meal h3 {
                margin: 10px 0;
                color: #333;
            }

            .meal p {
                margin: 5px 0;
                color: #555;
            }

            .meal-actions {
                margin-top: 10px;
                text-align: center;
            }

            .meal-actions input[type="number"] {
                width: 50px;
                margin-right: 10px;
                border-radius: 5px;
                padding: 5px;
                border: 1px solid #ccc;
            }

            .meal-actions select {
                margin-right: 10px;
                border-radius: 5px;
                padding: 5px;
                border: 1px solid #ccc;
            }

            .meal-actions button {
                padding: 5px 10px;
                background-color: #333;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;

                padding: 10px 20px;
                background-color: var(--primary-color);
                border: none;
                color: white;
                font-size: 16px;
                font-weight: bold;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .meal-actions button:hover {
                background-color: #555;
            }

            .no-meals {
                text-align: center;
                font-weight: bold;
                color: #777;
            }

            /* Responsive styling */
            @media (max-width: 768px) {
                .meal {
                    width: 90%;
                }
            }
        </style>
    </head>

    <body>
        <!-- Header -->
        <div class="header">
            <h1>STORE: <?php echo $seller_name; ?></h1>
            <a href="user_opened_convo.php?seller_id=<?php echo $seller_id; ?>" class="message-button">Message Seller</a>
        </div>

        <a href="user_dashboard.php" class="back-button">Back to Stores</a>

        <!-- Meals Section -->
        <div class="meal-container">
            <h2 style="color: white;">Available Meals</h2>
            <?php if ($mealsResult->num_rows > 0): ?>
                <?php while ($meal = $mealsResult->fetch_assoc()): ?>

                    <div class="meal">
                        <img src="<?php echo htmlspecialchars($meal['image']); ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>">
                        <div class="meal-details">
                            <h3><?php echo htmlspecialchars($meal['name']); ?></h3>
                            <p><?php echo htmlspecialchars($meal['description']); ?></p>
                            <p><strong>Price: ₱<?php echo htmlspecialchars($meal['price']); ?></strong></p>
                        </div>
                        <div class="meal-actions">
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="meal_id" value="<?php echo $meal['id']; ?>">

                                <!-- Quantity input -->
                                <label for="quantity">Qty:</label>
                                <input type="number" name="quantity" required min="1">


                                <button type="submit">Add to Cart</button>
                            </form>
                        </div>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-meals">No meals available from this store at the moment.</p>
            <?php endif; ?>

            <!-- Close the meals result set -->
            <?php $mealsResult->close(); ?>
        </div>

        <?php
        // Close the database connection
        $stmt->close();
        $conn->close();
        ?>
    </body>

    </html>