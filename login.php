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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F2F2F2;
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
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }

        h2 {
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
            /* Light gray border */
            border-radius: 8px;
            /* Smooth edges */
            background-color: #f8f8f8;
            /* Light gray background */
            color: #333333;
            /* Dark gray text */
        }

        input:focus {
            border-color: #5a4db1;
            outline: none;
        }

        button {
        padding: 15px 175px;
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
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(106, 90, 205, 0.6);
            color: #fff;
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