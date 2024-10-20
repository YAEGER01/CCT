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
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #2E2E2E;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #383838;
            padding: 20px;
            border-radius: 8px;
            color: #D3D3D3;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #555;
            border-radius: 5px;
            background-color: #6A5ACD;
            color: white;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        input[type="submit"],
        button {
            background-color: #6A5ACD;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 48%;
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: #5a4db1;
        }

        button.cancel-btn {
            background-color: #555;
        }

        button.cancel-btn:hover {
            background-color: #444;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Edit Profile</h2>

        <form action="user_edit.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <h3>Change Password (Optional)</h3>
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" placeholder="Enter your current password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter a new password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password">
            </div>

            <div class="btn-group">
                <input type="submit" value="Update Profile">
                <button type="button" class="cancel-btn" onclick="cancelEdit()">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        function cancelEdit() {
            const role = '<?php echo $role; ?>';
            if (role === 'buyer') {
                window.location.href = 'user_dashboard.php';
            } else if (role === 'seller') {
                window.location.href = 'seller_dashboard.php';
            }
        }
    </script>
</body>

</html>