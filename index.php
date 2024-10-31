<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login or Signup</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700,800,900');

        /* Global Styles */
        body {
            font-family: 'Poppins', Arial, sans-serif;
            font-weight: 300;
            line-height: 1.7;
            text-align: center;
            background-color: #f2f2f2;
            color: #ffeba7;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: -webkit-linear-gradient(to right,
                    #24243e,
                    #302b63,
                    #0f0c29);
            /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to right,
                    #24243e,
                    #302b63,
                    #0f0c29);
        }

        /* Keyframe Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Containers */
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 90%;
            max-width: 500px;
            padding: 50px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            margin-top: 5%;
            animation: fadeIn 1s ease-out;
            background-image: url('japanese.jpeg');
            background-size: cover;
            background-position: center;
            background-blend-mode: overlay;
        }

        /* Headings */
        h1 {
            color: #333333;
            margin-bottom: 20px;
            font-size: 2.2em;
            font-weight: 700;
            animation: slideIn 1s ease-out;
        }

        /* Paragraphs */
        p {
            font-size: 1em;
            font-weight: 500;
            color: #333333;
            margin-bottom: 30px;
            animation: fadeIn 1.5s ease-out;
        }

        /* Button Styles with Animation */
        .btn {
            padding: 15px 30px;
            background-color: #f3f3f3;
            color: white;
            border-radius: 20px;
            font-size: 1em;
            font-weight: bold;
            display: inline-block;
            transition: background-color 0.3s ease, transform 0.3s ease;
            animation: fadeIn 2s ease-out, pulse 2s infinite;
            box-shadow: 0 4px 10px rgba(10 6, 90, 205, 0.4);
        }

        .btn:hover {
            background-color: #5a4db1;
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(106, 90, 205, 0.6);
        }

        /* Additional Link Styling */
        .link {
            color: #333;

            transition: color 0.3s ease;
            font-weight: 600;
            text-decoration: none;
        }

        .link:hover {
            text-decoration: none;
            color: #ffeba7;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                margin-top: 15%;
            }

            h1 {
                font-size: 1.8em;
            }

            p {
                font-size: 0.9em;
            }

            .btn {
                padding: 12px 25px;
                font-size: 0.9em;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px 15px;
                margin-top: 20%;
            }

            h1 {
                font-size: 1.6em;
            }

            p {
                font-size: 0.8em;
            }

            .btn {
                padding: 10px 20px;
                font-size: 0.8em;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="h1">Welcome</h1>
        <p class="p">Please choose an option below to continue:</p>
        <ul>
            <li class="btn"><a class="link" href="login.php">Login</a></li> <br><br>
            <li class="btn"><a class="link" href="signup.php">Signup</a></li>
        </ul>
    </div>
</body>

</html>