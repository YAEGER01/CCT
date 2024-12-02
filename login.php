<?php
// Include the database connection file
include 'db.php';

// Start the session to store user login information
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password']));

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Prepare an SQL statement to check user credentials
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if the user exists
            if ($result->num_rows > 0) {
                // Fetch user data
                $user = $result->fetch_assoc();

                // Compare plain text password
                if ($password === $user['password']) { // Basic comparison
                    // Store user information in session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on role
                    if ($user['role'] === 'seller') {
                        header("Location: seller_dashboard.php");
                    } else {
                        header("Location: user_dashboard.php");
                    }
                    exit(); // Ensure no further code is executed
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "No user found with that email.";
            }

            $stmt->close();
        } else {
            $error = "Database error: Unable to prepare statement.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">


<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<style>
    @font-face {
        font-family: 'MyCustomFont1';
        /* Give your font a name */
        src: url('fonts/nexa/Nexa-ExtraLight.ttf') format('truetype');
        /* Path to the TTF file */
        font-weight: normal;
        font-style: normal;
    }

    @font-face {
        font-family: 'MyCustomFont2';
        /* Give your font a name */
        src: url('fonts/nexa/Nexa-Heavy.ttf') format('truetype');
        /* Path to the TTF file */
        font-weight: normal;
        font-style: normal;
    }

    body {
        font-family: 'MyCustomFont2', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #F2F2F2;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
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

    .container {
        background-color: #ffffff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        max-width: 400px;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    @media (max-width: 768px) {
        .container {
            width: 70vw;
        }
    }

    h2 {
        font-family: 'MyCustomFont2', sans-serif;
        margin-bottom: 30px;
        text-align: center;
        color: #333333;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333333;
    }

    input[type="email"],
    input[type="password"] {
        box-sizing: border-box;
        font-size: 16px;
        transition: border-color 0.3s;
        width: 100%;
        padding: 10px;
        margin: 10px 0 20px;
        border: 1px solid #cccccc;
        border-radius: 8px;
        background-color: #f8f8f8;
        color: #333333;
    }

    input:focus {
        border-color: #5a4db1;
        outline: none;
    }

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
        width: 56%;
        margin: 10px 0;
        margin-left: 90px;
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

    .form-footer {
        margin-top: 20px;
        text-align: center;
    }

    .form-footer a {
        color: #6A5ACD;
        text-decoration: none;
    }

    .form-footer a:hover {
        text-decoration: underline;
    }

    .error {
        color: red;
        margin-bottom: 20px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .container {
            padding: 30px;
        }

        .btn {
            padding: 12px;
            margin-left: 80px;
        }
    }
</style>
</head>

<body>

    <div class="container">
        <h2><a href="index.php" style="text-decoration:none">YouChews</a>Login</h2>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button class="btn" type="submit">Login</button>

            <div class="form-footer">
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
            </div>
        </form>
    </div>

</body>

</html>