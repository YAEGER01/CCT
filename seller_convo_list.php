<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Fetch conversations
$query = "
    SELECT messages.*, users.username as buyer_name 
    FROM messages 
    JOIN users ON messages.sender_id = users.id 
    WHERE messages.recipient_id = ? 
    ORDER BY messages.sent_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation List</title>
    <style>
        /* Your styles here */
    </style>
</head>

<body>
    <h1>Your Conversations</h1>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div>
            <p><strong>With:</strong> <?php echo htmlspecialchars($row['buyer_name']); ?></p>
            <p><strong>Message:</strong> <?php echo htmlspecialchars($row['message']); ?></p>
            <p><strong>Sent at:</strong> <?php echo htmlspecialchars($row['sent_at']); ?></p>
            <a href="seller_opened_convo.php?user_id=<?php echo $row['sender_id']; ?>">View Conversation</a>
        </div>
    <?php endwhile; ?>
</body>

</html>
<?php
$stmt->close();
$conn->close();
?>