<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : null;

if (!$seller_id) {
    echo "<p>No seller selected!</p>";
    exit();
}

// Fetch messages
$query = "
    SELECT messages.*, users.username as seller_name 
    FROM messages 
    JOIN users ON (messages.sender_id = users.id OR messages.recipient_id = users.id) 
    WHERE (messages.sender_id = ? AND messages.recipient_id = ?) 
    OR (messages.sender_id = ? AND messages.recipient_id = ?) 
    ORDER BY messages.sent_at ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $user_id, $seller_id, $seller_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $query = "INSERT INTO messages (sender_id, recipient_id, message, sent_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $user_id, $seller_id, $message);
        $stmt->execute();
        header("Location: user_opened_convo.php?seller_id=$seller_id");
        exit();
    } else {
        echo "<p>Message cannot be empty.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation with Seller</title>
    <style>
        /* Your styles here */
    </style>
</head>

<body>
    <h1>Conversation with <?php echo htmlspecialchars($result->fetch_assoc()['seller_name']); ?></h1>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div>
            <p><strong><?php echo htmlspecialchars($row['sender_id'] == $user_id ? 'You' : $row['seller_name']); ?>:</strong> <?php echo htmlspecialchars($row['message']); ?></p>
            <p><strong>Sent at:</strong> <?php echo htmlspecialchars($row['sent_at']); ?></p>
        </div>
    <?php endwhile; ?>
    <form method="POST">
        <textarea name="message" required></textarea>
        <button type="submit">Send Message</button>
    </form>
</body>

</html>
<?php
$stmt->close();
$conn->close();
?>