<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch conversations
$query = "
    SELECT messages.*, users.username as seller_name 
    FROM messages 
    JOIN users ON messages.recipient_id = users.id 
    WHERE messages.sender_id = ? 
    ORDER BY messages.sent_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
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
            <p><strong>With:</strong> <?php echo htmlspecialchars($row['seller_name']); ?></p>
            <p><strong>Message:</strong> <?php echo htmlspecialchars($row['message']); ?></p>
            <p><strong>Sent at:</strong> <?php echo htmlspecialchars($row['sent_at']); ?></p>
            <a href="opened_conversation.php?seller_id=<?php echo $row['recipient_id']; ?>">View Conversation</a>
        </div>
    <?php endwhile; ?>
</body>

</html>
<?php
$stmt->close();
$conn->close();
?>