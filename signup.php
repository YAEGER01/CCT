<?php
// Start a session to manage form submission status
session_start();

// Include the database connection file
include 'db.php';

// Define variables for form data and error message
$username = $email = $password = $confirm_password = $role = "";
$error = "";

// Check if the form was already submitted
if (isset($_SESSION['submitted'])) {
    // Redirect to the same page to avoid form resubmission
    unset($_SESSION['submitted']); // Remove the submitted flag
    header("Location: {$_SERVER['PHP_SELF']}?success=1");
    exit(); // Ensure no further code is executed
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password']));
    $confirm_password = trim(mysqli_real_escape_string($conn, $_POST['confirm_password']));
    $role = trim(mysqli_real_escape_string($conn, $_POST['role']));

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be exactly 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username already exists
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $error = "Username is already taken.";
        } else {
            // Insert new user
            $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
            if (mysqli_query($conn, $query)) {
                // Set session variable to prevent form resubmission
                $_SESSION['submitted'] = true;

                // Redirect to the same page (PRG pattern)
                header("Location: {$_SERVER['PHP_SELF']}");
                exit();
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Check if form submission was successful
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<script>alert('Registered Successfully!'); window.location.href='login.php';</script>";
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F2F2F2;
            /* Light gray background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
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

        .container {
            background-color: #ffffff;
            /* White background */
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 12px;
            /* Increased border radius */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Light shadow for depth */
            text-align: center;

        }

        h2 {
            margin-bottom: 20px;
            color: #333333;
            /* Dark gray text */
        }

        form {
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333333;
            /* Dark gray text */
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #cccccc;
            /* Light gray border */
            border-radius: 8px;
            /* Smooth edges */
            background-color: #f8f8f8;
            /* Light gray background */
            color: #333333;
            /* Dark gray text */
        }

        button {
        padding: 15px 150px;
        background-color: #f3f3f3;
        color: purple;
        border-radius: 20px;
        font-size: 1em;
        font-weight: bold;
        display: inline-block;
        transition: background-color 0.3s ease, transform 0.3s ease;
        animation: fadeIn 2s ease-out, pulse 2s infinite;
        box-shadow: 0 4px 10px rgba(106, 90, 205, 0.4);
        }

        button:hover {
            background-color: #5a4db1;
            box-shadow: 0 6px 12px rgba(106, 90, 205, 0.6);
            color: #fff;
            transform: scale(1.05);
            /* Darker purple */
        }

        .form-footer {
            margin-top: 15px;
        }

        .form-footer a {
            color: #6A5ACD;
            /* Purple link */
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Create an Account</h2>
        <?php if (!empty($error)) : ?>
            <script>
                alert("<?php echo htmlspecialchars($error); ?>");
            </script>
        <?php endif; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter an 8-character password" required>
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-type your password" required>
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="user">Buyer</option>
                <option value="seller">Seller</option>
            </select>
            <button type="submit">Sign Up</button>
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </form>
    </div>
</body>

</html>