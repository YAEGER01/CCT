<?php
session_start();
include 'db.php';

// Check if user is logged in as a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch transactions for the buyer, including new fields
$transactionQuery = "SELECT t.id, t.transaction_date, s.username AS seller_name, m.meal_name AS meal_name, t.quantity, t.rice_option, t.rice_price, t.drinks, t.drinks_price, t.total_price
                     FROM transactions t
                     JOIN users s ON t.seller_id = s.id
                     JOIN meals m ON t.meal_id = m.id
                     WHERE t.user_id = $user_id
                     ORDER BY t.transaction_date DESC";
$transactionResult = mysqli_query($conn, $transactionQuery);

// Fetch transactions into an array
$transactions = mysqli_fetch_all($transactionResult, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #2b2b2b;
            color: white;
            margin: 0;
            padding: 20px;
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

        h1 {
            color: #6a0dad;
            text-align: center;
            background-color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #ffffff;
            color: #333;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #555;
        }

        th {
            background-color: #6a0dad;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #ffffff;
        }

        tr:hover {
            background-color: #b792f2;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {

            /* Hide extra columns on mobile */
            table th:nth-child(2),
            table th:nth-child(3),
            /* Hide the third column header */
            table th:nth-child(4),
            table th:nth-child(5) {
                display: none;
            }

            table td:nth-child(2),
            table td:nth-child(3),
            /* Hide the third column data */
            table td:nth-child(4),
            table td:nth-child(5) {
                display: none;
            }

            /* Adjust meal name and action button */
            table td:nth-child(1),
            /* Adjust the first column (now the second) */
            table td:nth-child(4) {
                /* Adjust the fourth column (now the third) */
                display: block;
                width: 100%;
            }
        }

        .back-btn {
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
            width: 250px;
            margin: 10px;
            text-decoration: none;
        }

        .back-btn:before {
            content: "";
            position: absolute;
            width: 100px;
            height: 120%;
            background-color: #ff6700;
            top: 50%;
            transform: skewX(30deg) translate(-110%, -50%);
            transition: all 0.5s;
        }

        .back-btn:hover {
            background-color: #4500b5;
            color: #fff;
            box-shadow: 0 2px 0 2px #0d3b66;
        }

        .back-btn:hover::before {
            transform: skewX(30deg) translate(160%, -50%);
            transition-delay: 0.1s;
        }

        .back-btn:active {
            transform: scale(0.9);
        }

        .view-btn {
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
            width: 150px;
            margin: 10px;
            text-decoration: none;
        }

        .view-btn:before {
            content: "";
            position: absolute;
            width: 100px;
            height: 120%;
            background-color: #ff6700;
            top: 50%;
            transform: skewX(30deg) translate(-110%, -50%);
            transition: all 0.5s;
        }

        .view-btn:hover {
            background-color: #4500b5;
            color: #fff;
            box-shadow: 0 2px 0 2px #0d3b66;
        }

        .view-btn:hover::before {
            transform: skewX(30deg) translate(100%, -50%);
            transition-delay: 0.1s;
        }

        .view-btn:active {
            transform: scale(0.9);
        }

        /* Modal Customization */
        .modal-dialog {
            max-width: 600px;
        }

        .modal-content {
            color: #fff;
            background-color: transparent;
            /* Let inner styles dominate */
            border: none;
            /* Remove Bootstrap border */
            box-shadow: none;
            /* Remove Bootstrap shadow */
            display: flex;
            flex-direction: column;
            align-items: center;
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 26px;
        }

        /* Meal Grid (Adjusted for Modal) */
        .modal-body .meal {
            width: 100%;
            /* Full width within modal */
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease;
            font-family: 'MyCustomFont1', sans-serif;
            font-weight: 500;
        }

        .modal-body .meal img {
            width: 100%;
            height: 200px;
            /* Adjust height for modal */
            object-fit: cover;
            border-radius: 8px;
            align-self: center;
        }

        .modal-body .meal-details {
            margin-top: 10px;
        }

        .modal-body .meal-actions input,
        .modal-body .meal-actions select {
            width: calc(100% - 10px);
            /* Adjust for modal padding */
        }

        /* Add media query for modal */
        @media (max-width: 768px) {
            .modal-dialog {
                max-width: 100%;
                /* Full screen width for smaller devices */
            }

            .modal-body .meal {
                padding: 10px;
            }
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
    <table class="table table-striped table-hover">
        <thead>
            <tr>

                <th>Seller Name</th>
                <th>Meal Name</th>
                <th>Quantity</th>
                <th>Transaction Date</th>
                <th>Total Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($transactions)): ?>
                <?php foreach ($transactions as $index => $transaction): ?>
                    <tr>
                        <td><?= htmlspecialchars($transaction['seller_name']); ?></td>
                        <td><?= htmlspecialchars($transaction['meal_name']); ?></td>
                        <td><?= htmlspecialchars($transaction['quantity']); ?></td>
                        <td><?= htmlspecialchars($transaction['transaction_date']); ?></td>
                        <td>₱<?= htmlspecialchars($transaction['total_price']); ?></td>
                        <td>
                            <button class="view-btn" data-bs-toggle="modal" data-bs-target="#detailsModal<?= $index; ?>">See
                                More</button>
                        </td>
                    </tr>

                    <!-- Modal -->
                    <div class="modal fade" id="detailsModal<?= $index; ?>" tabindex="-1"
                        aria-labelledby="detailsModalLabel<?= $index; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailsModalLabel<?= $index; ?>">Order Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Transaction Date:</strong>
                                        <?= htmlspecialchars($transaction['transaction_date']); ?></p>
                                    <p><strong>Seller Name:</strong> <?= htmlspecialchars($transaction['seller_name']); ?></p>
                                    <p><strong>Meal Name:</strong> <?= htmlspecialchars($transaction['meal_name']); ?></p>
                                    <p><strong>Quantity:</strong> <?= htmlspecialchars($transaction['quantity']); ?></p>
                                    <p><strong>Rice Option:</strong> <?= htmlspecialchars($transaction['rice_option']); ?></p>
                                    <p><strong>Rice Price:</strong> ₱<?= htmlspecialchars($transaction['rice_price']); ?></p>
                                    <p><strong>Drinks:</strong> <?= htmlspecialchars($transaction['drinks']); ?></p>
                                    <p><strong>Drinks Price:</strong> ₱<?= htmlspecialchars($transaction['drinks_price']); ?>
                                    </p>
                                    <p><strong>Total Price:</strong> ₱<?= htmlspecialchars($transaction['total_price']); ?></p>
                                </div>
                                <!--div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div-->
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No transactions found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>