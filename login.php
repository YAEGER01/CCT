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

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #2E2E2E;
            /* Grayish black background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #383838;
            /* Lighter grayish black */
            padding: 40px;
            border-radius: 12px;
            /* Smooth edges */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }

        h2 {
            margin-bottom: 30px;
            text-align: center;
            color: #D3D3D3;
            /* Light gray */
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #D3D3D3;
            /* Light gray */
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #6A5ACD;
            /* Purple border */
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            background-color: #2E2E2E;
            /* Grayish black input background */
            color: #D3D3D3;
            /* Light gray text */
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #5a4db1;
            /* Darker purple on focus */
            outline: none;
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #6A5ACD;
            /* Purple button */
            color: white;
            border: none;
            border-radius: 12px;
            /* Smooth edges */
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #5a4db1;
            /* Darker purple on hover */
        }

        .form-footer {
            margin-top: 20px;
            text-align: center;
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
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px;
            }

            button {
                padding: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Login</h2>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Login</button>

            <div class="form-footer">
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
            </div>
        </form>
    </div>

</body>

</html>