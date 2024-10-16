<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login or Signup</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #2E2E2E;
            /* Grayish black background */
            margin: 0;
            padding: 0;
        }

        h1 {
            margin-top: 50px;
            color: #D3D3D3;
            /* Light gray for contrast */
        }

        p {
            font-size: 18px;
            color: #D3D3D3;
            /* Light gray for contrast */
        }

        ul {
            list-style: none;
            padding: 0;
            margin-top: 30px;
        }

        li {
            margin: 15px 0;
        }

        a {
            text-decoration: none;
            padding: 15px 30px;
            background-color: #6A5ACD;
            /* Purple background */
            color: white;
            border-radius: 12px;
            /* Increased border radius */
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #5a4db1;
            /* Darker purple on hover */
        }

        .container {
            display: inline-block;
            padding: 50px;
            background-color: #383838;
            /* Slightly lighter grayish black */
            border-radius: 12px;
            /* Increased border radius */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome!</h1>
        <p>Please choose an option below to continue:</p>
        <ul>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Signup</a></li>
        </ul>
    </div>
</body>

</html>