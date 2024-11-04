<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login or Signup</title>
    <link rel="icon" type="image/png" href="images/Logo/logoplate.png">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700,800,900');

        body {
            font-family: 'Poppins', Arial, sans-serif;
            font-weight: 300;
            line-height: 1.7;
            text-align: center;
            color: #ffeba7;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            /* Background Style */
            background: radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 0% 0% / 64px 64px,
                radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 32px 32px / 64px 64px,
                linear-gradient(#a43fc6 2px, transparent 2px) 0px -1px / 32px 32px,
                linear-gradient(90deg, #a43fc6 2px, #ffffff 2px) -1px 0px / 32px 32px #ffffff;
            background-size: 64px 64px, 64px 64px, 32px 32px, 32px 32px;
            background-color: #ffffff;
            animation: scroll-diagonal 10s linear infinite;

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



        /* Keyframes for background scrolling */
        @keyframes scrollPattern {
            0% {
                background-position: 0 0, 20px 20px, 0px 0px, 0px 0px;
            }

            100% {
                background-position: 40px 40px, 60px 60px, 20px 20px, 20px 20px;
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
        <a class="link" href="login.php"><button class="btn">Login</button></a>
        <a class="link" href="signup.php"><button class="btn">Signup</button></a>
    </div>
</body>

</html>