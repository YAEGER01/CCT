<?php
include 'db.php';

// Fetch seller name, meal name, total quantity sold, meal price, and image
$sql = "
    SELECT 
        users.username AS seller_name, 
        meals.meal_name, 
        meals.price,
        meals.image,
        SUM(transactions.quantity) AS total_sold
    FROM transactions
    INNER JOIN users ON transactions.seller_id = users.id
    INNER JOIN meals ON transactions.meal_id = meals.id
    WHERE users.role = 'seller'
    GROUP BY transactions.seller_id, transactions.meal_id
";

$result = $conn->query($sql);

$rankings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rankings[] = $row;
    }
}

// Merge Sort Algorithm for sorting rankings by `total_sold`
function mergeSort($array)
{
    if (count($array) <= 1) {
        return $array;
    }

    $mid = intdiv(count($array), 2);
    $left = array_slice($array, 0, $mid);
    $right = array_slice($array, $mid);

    return merge(mergeSort($left), mergeSort($right));
}

function merge($left, $right)
{
    $result = [];
    while (count($left) > 0 && count($right) > 0) {
        if ($left[0]['total_sold'] >= $right[0]['total_sold']) {
            $result[] = array_shift($left);
        } else {
            $result[] = array_shift($right);
        }
    }
    return array_merge($result, $left, $right);
}

// Sort the rankings
$rankings = mergeSort($rankings);

// Return JSON response with seller name, meal name, price, image, and total sold
header('Content-Type: application/json');
echo json_encode($rankings);

$conn->close();
?>