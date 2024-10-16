<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
if (!$user_id) {
    echo "<p>No user selected!</p>";
    exit();
}

// Fetch messages
$query = "
    SELECT messages.*, users.username as user_name 
    FROM messages 
    JOIN users ON (messages.sender_id = users.id OR messages.recipient_id = users.id) 
    WHERE (messages.sender_id = ? AND messages.recipient_id = ?) 
    OR (messages.sender_id = ? AND messages.recipient_id = ?) 
    ORDER BY messages.sent_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $seller_id, $user_id, $user_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $query = "INSERT INTO messages (sender_id, recipient_id, message, sent_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $seller_id, $user_id, $message);
        $stmt->execute();
        header("Location: seller_opened_convo.php?user_id=$user_id");
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
    <title>Conversation with User</title>
    <style>
        /* Your styles here */
        body {
            font-family: Arial, sans-serif;
            background-color: #2E2E2E;
            /* Grayish black background */
            color: white;
            padding: 20px;
        }

        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 15px;
            background-color: #383838;
            /* Dark grayish black */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: none;
            margin-bottom: 10px;
            resize: vertical;
            min-height: 150px;
        }

        button {
            padding: 10px 15px;
            background-color: #6A5ACD;
            /* Purple button */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #5a4db1;
            /* Darker purple on hover */
        }
    </style>
</head>

<body>
    <h1>Conversation with <?php echo htmlspecialchars($result->fetch_assoc()['user_name']); ?></h1>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div>
            <p><strong><?php echo htmlspecialchars($row['sender_id'] == $seller_id ? 'You' : $row['user_name']); ?>:</strong> <?php echo htmlspecialchars($row['message']); ?></p>
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