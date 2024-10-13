<?php
// Include the database connection file
include 'db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password']));
    $role = trim(mysqli_real_escape_string($conn, $_POST['role']));

    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare an SQL statement to insert the user data
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            
            // Execute the statement and check for success
            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            $error = "Failed to prepare SQL statement.";
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
    <style>
        /* General Body and Container Styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #2E2E2E; /* Grayish black */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #383838; /* Lighter grayish black */
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 12px; /* Increased border radius */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #D3D3D3; /* Light gray for contrast */
        }

        form {
            width: 100%;
        }

        label {
            text-align: left;
            display: block;
            margin-bottom: 5px;
            color: #D3D3D3; /* Light gray */
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #6A5ACD; /* Purple border */
            border-radius: 8px; /* Smooth edges */
            box-sizing: border-box;
            font-size: 16px;
            background-color: #2E2E2E; /* Grayish black */
            color: #D3D3D3; /* Light gray text */
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            border-color: #5a4db1; /* Darker purple on focus */
            outline: none;
        }

        button {
            width: 100%;
            background-color: #6A5ACD; /* Purple button */
            color: white;
            padding: 15px;
            border: none;
            border-radius: 12px; /* Smooth edges for button */
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #5a4db1; /* Darker purple on hover */
        }

        .form-footer {
            margin-top: 15px;
        }

        .form-footer a {
            text-decoration: none;
            color: #6A5ACD; /* Purple link */
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            margin-bottom: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            button {
                padding: 12px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Create an Account</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="signup.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

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
