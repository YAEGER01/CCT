<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Page</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
            overflow: hidden;

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


        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 700px;
            height: 400px;
            padding: 20px;
            overflow-y: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        ul {
            list-style-type: none;
        }

        li {
            margin-bottom: 15px;
        }

        p {
            font-size: 14px;
            line-height: 1.5;
        }

        h2 {
            margin-bottom: 5px;
            font-size: 16px;
        }

        @media (max-width: 600px) {
            .card {
                height: 300px;
            }

            p {
                font-size: 12px;
            }

            h2 {
                font-size: 14px;
            }
        }

        /* Button Styles with Animation */
        .btn {
            padding: 0.3em 1.5em;
            /* Adjusted padding for a larger button */
            background: none;
            border: 2px solid #fff;
            font-size: 14px;
            /* Slightly larger font size */
            color: #131313;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
            border-radius: 8px;
            /* Keeping the border radius */
            background-color: #d056ef;
            font-weight: bolder;
            box-shadow: 0 2px 0 2px #000;
            width: 120px;
            /* Slightly larger width */
            margin: 20px auto;
            /* Center button with margin */
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
    </style>

</head>

<body>
    <div class="container">
        <div class="card">
            <h1>About Us</h1>
            <ul>
                <li>
                    <h2>What is YouChews?</h2>
                    <p>YouChews is a play of words originally from "You Choose" which is translated from the Filipino
                        phrase "Ikaw
                        mamili" which is closely related to the phrase "Ikaw bahala" which most Filipinos say when they
                        are in a group and they are about to make a decision.</p>

                    <br>
                    <p>
                        We made this site aiming to reduce the crowding and congestion on the campus food court during
                        peak lunch hours. The idea is the users will just go find a vacant seat, instead of queuing in
                        line and waiting for the lunch roulette (the server most of the time chooses randomly to serve a
                        customer's meal); you can just sit, order, and pick-up your meal when it's ready.
                    </p>
                </li>
                <br>
                <hr>
                <br>
                <br>
                <li>
                    <h1>BSIT 2A NS</h1>
                    <p style="text-align:center">Project for Creative Critical Thinking, aiming to solve the Food Court
                        Dilemma.</p>
                </li>
                <li>
                    <h2>Madayag, Frederick S.</h2>
                    <p>Project manager and mainly contributed to the back-end.</p>
                </li>
                <li>
                    <h2>Mendoza, Fernando A.</h2>
                    <p>Focused on designs and contents.</p>
                </li>
                <li>
                    <h2>Donato, Charles Bobby</h2>
                    <p>Focused on designs and contents.</p>
                </li>
            </ul>
        </div>
        <a href="index.php">
            <button class="btn">Go Back</button>
        </a>
    </div>
</body>

</html>