<?php
session_start();
include 'db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch the user data from the session
$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);
$role = htmlspecialchars($_SESSION['role']);

// Fetch user details from the database
$sql = "SELECT username, email, password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = htmlspecialchars($_POST['username']);
    $new_email = htmlspecialchars($_POST['email']);

    // Update username and email
    $update_sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssi", $new_username, $new_email, $role, $user_id);

    if ($update_stmt->execute()) {
        // Update the session variables
        $_SESSION['username'] = $new_username;

        // Check if the user submitted a password change
        if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            // Verify current password
            if ($current_password !== $user['password']) {
                // Use session or flags for error, avoid echoing in the main logic
                $_SESSION['error'] = 'Current password is incorrect.';
            } elseif ($new_password !== $confirm_password) {
                $_SESSION['error'] = 'New password and confirmation do not match.';
            } else {
                // Update password in the database
                $update_password_sql = "UPDATE users SET password = ? WHERE id = ?";
                $update_password_stmt = $conn->prepare($update_password_sql);
                $update_password_stmt->bind_param("si", $new_password, $user_id); // Hash password before saving

                if ($update_password_stmt->execute()) {
                    $_SESSION['success'] = 'Password updated successfully.';
                } else {
                    $_SESSION['error'] = 'Error updating password.';
                }
            }
        }

        // Redirect based on user role using PRG
        if ($role === 'user') {
            header("Location: user_dashboard.php?success=1");
        } elseif ($role === 'seller') {
            header("Location: seller_dashboard.php?success=1");
        }
        exit();
    } else {
        $_SESSION['error'] = 'Error updating profile.';
    }
}

// Handle success or error messages after PRG
$success_message = isset($_GET['success']) && $_GET['success'] == 1 ? 'Profile updated successfully!' : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // Clear error after displaying
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6A5ACD;
            --secondary-color: #F2F2F2;
            --font-primary: 'Roboto', sans-serif;
        }

        body {
            font-family: var(--font-primary);
            margin: 0;
            padding: 0;
            background-color: var(--secondary-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 0% 0% / 64px 64px,
                radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 32px 32px / 64px 64px,
                linear-gradient(#4500b5 2px, transparent 2px) 0px -1px / 32px 32px,
                linear-gradient(90deg, #4500b5 2px, #ffffff 2px) -1px 0px / 32px 32px #ffffff;
            background-size: 64px 64px, 64px 64px, 32px 32px, 32px 32px;
            background-color: #ffffff;
            animation: scroll-diagonal 10s linear infinite;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Keyframes for Diagonal Scrolling */
        @keyframes scroll-diagonal {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 64px 64px;
            }
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        @media (max-width: 768px) {
            .container {
                width: 80vw;
            }
        }

        h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(106, 90, 205, 0.3);
        }

        h3 {
            margin-top: 20px;
            color: #333;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        input[type="submit"] {
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

        input[type="submit"]:hover {
            background-color: #555;
        }

        .cancel-btn {
            padding: 10px 20px;
            background-color: #ccc;
            border: none;
            color: black;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .cancel-btn:hover {
            background-color: #aaa;
        }

        /* Button Styles with Animation */
        .btn {
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
            transform: skewX(30deg) translate(80%, -50%);
            transition-delay: 0.1s;
        }

        .btn:active {
            transform: scale(0.9);
        }

        /* Button Styles with Animation */
        .btn-update {
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
        }

        .btn-update:before {
            content: "";
            position: absolute;
            width: 100px;
            height: 120%;
            background-color: #ff6700;
            top: 50%;
            transform: skewX(30deg) translate(-110%, -50%);
            transition: all 0.5s;
        }

        .btn-update:hover {
            background-color: #4500b5;
            color: #fff;
            box-shadow: 0 2px 0 2px #0d3b66;
        }

        .btn-update:hover::before {
            transform: skewX(30deg) translate(130%, -50%);
            transition-delay: 0.1s;
        }

        .btn-update:active {
            transform: scale(0.9);
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Edit Profile</h2>

        <form action="user_edit.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                    value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                    required>
            </div>

            <h3>Change Password (Optional)</h3>
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password"
                    placeholder="Enter your current password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter a new password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                    placeholder="Confirm your new password">
            </div>

            <div class="btn-group">
                <!--input type="submit" value="Update Profile"-->
                <button class="btn-update" type="submit" value="Update Profile">Update Profile</button>
                <button type="button" class="cancel-btn btn" onclick="cancelEdit()">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        function cancelEdit() {
            const role = '<?php echo $role; ?>';
            if (role === 'user') {
                window.location.href = 'user_dashboard.php';
            } else if (role === 'seller') {
                window.location.href = 'seller_dashboard.php';
            }
        }
    </script>
</body>

</html>