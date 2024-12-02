<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Best-Selling Items</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        h1 {
            margin: 20px 0;
        }

        /* styles.css */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            overflow-x: hidden;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: center;
            padding: 12px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #d056ef;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        @media (max-width: 600px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            th {
                display: none;
            }

            td {
                text-align: left;
                position: relative;
                padding-left: 50%;
            }

            td::before {
                position: absolute;
                left: 10px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
            }

            td:nth-of-type(1)::before {
                content: "Rank";
            }

            td:nth-of-type(2)::before {
                content: "Store Name";
            }

            td:nth-of-type(3)::before {
                content: "Meal Name";
            }

            td:nth-of-type(4)::before {
                content: "Total Quantity Sold";
            }
        }


        /* Button Styles with Animation */
        .btn {
            font-family: 'MyCustomFont2', sans-serif;
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
            width: 200px;
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
            transform: skewX(30deg) translate(150%, -50%);
            transition-delay: 0.1s;
        }

        .btn:active {
            transform: scale(0.9);
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            fetch('get_rankings.php')
                .then(response => response.json())
                .then(data => {
                    const table = document.getElementById('rankingTable');
                    data.forEach((item, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${item.seller_name}</td>
                            <td>${item.meal_name}</td>
                            <td>${item.total_sold}</td>
                        `;
                        table.appendChild(row);
                    });
                })
                .catch(error => console.error('Error fetching rankings:', error));
        });
    </script>
</head>

<body>
    <div class="container">
        <h1>Top Meal Ranks</h1>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Store Name</th>
                        <th>Meal Name</th>
                        <th>Total Quantity Sold</th>
                    </tr>
                </thead>
                <tbody id="rankingTable">

                </tbody>
            </table>
            <a href="user_dashboard.php">
                <button class="btn">Go back</button></a>
        </div>
</body>

</html>