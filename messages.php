<?php
/**
 * Messages Page - Direct messaging between users
 */

require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get list of conversations
$conversations_query = "
    SELECT DISTINCT 
        CASE 
            WHEN m.sender_id = ? THEN m.receiver_id 
            ELSE m.sender_id 
        END as other_user_id,
        u.username, u.full_name, u.profile_pic,
        (SELECT message FROM messages 
         WHERE (sender_id = ? AND receiver_id = u.user_id) 
            OR (sender_id = u.user_id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages 
         WHERE (sender_id = ? AND receiver_id = u.user_id) 
            OR (sender_id = u.user_id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM messages m
    JOIN users u ON u.user_id = CASE 
        WHEN m.sender_id = ? THEN m.receiver_id 
        ELSE m.sender_id 
    END
    WHERE m.sender_id = ? OR m.receiver_id = ?
    GROUP BY other_user_id
    ORDER BY last_message_time DESC
";

$stmt = $conn->prepare($conversations_query);
$stmt->bind_param("iiiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result();

// Get selected conversation
$selected_user_id = isset($_GET['user']) ? intval($_GET['user']) : null;
$messages = [];
$selected_user = null;

if ($selected_user_id) {
    // Get selected user info
    $user_query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $selected_user_id);
    $stmt->execute();
    $selected_user = $stmt->get_result()->fetch_assoc();
    
    if ($selected_user) {
        // Get messages
        $messages_query = "
            SELECT m.*, 
                   s.username as sender_username, s.profile_pic as sender_pic,
                   r.username as receiver_username, r.profile_pic as receiver_pic
            FROM messages m
            JOIN users s ON m.sender_id = s.user_id
            JOIN users r ON m.receiver_id = r.user_id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ";
        
        $stmt = $conn->prepare($messages_query);
        $stmt->bind_param("iiii", $user_id, $selected_user_id, $selected_user_id, $user_id);
        $stmt->execute();
        $messages = $stmt->get_result();
        
        // Mark messages as read
        $update_query = "UPDATE messages SET is_read = 1 
                        WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $selected_user_id, $user_id);
        $stmt->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Social Media</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .messages-container {
            display: flex;
            height: calc(100vh - 100px);
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .conversations-list {
            width: 300px;
            border-right: 1px solid #e2e8f0;
            overflow-y: auto;
        }
        
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .conversation-item:hover {
            background: #f7fafc;
        }
        
        .conversation-item.active {
            background: #e6f2ff;
        }
        
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .messages-list {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .message {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }
        
        .message.sent {
            justify-content: flex-end;
        }
        
        .message-content {
            max-width: 60%;
            padding: 10px 15px;
            border-radius: 15px;
            background: #f0f2f5;
        }
        
        .message.sent .message-content {
            background: #667eea;
            color: white;
        }
        
        .message-input-area {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .message-form {
            display: flex;
            gap: 10px;
        }
        
        .message-input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            outline: none;
        }
        
        .send-btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="nav-brand">Social Media</a>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <a href="messages.php" class="nav-link active"><i class="fas fa-envelope"></i> Messages</a>
                <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Messages</h1>
        
        <div class="messages-container">
            <!-- Conversations List -->
            <div class="conversations-list">
                <?php while ($conv = $conversations->fetch_assoc()): ?>
                    <div class="conversation-item <?php echo $selected_user_id == $conv['other_user_id'] ? 'active' : ''; ?>" 
                         onclick="window.location.href='messages.php?user=<?php echo $conv['other_user_id']; ?>'">
                        <img src="images/<?php echo $conv['profile_pic']; ?>" alt="Profile" class="post-profile-pic">
                        <div style="flex: 1; margin-left: 10px;">
                            <strong><?php echo htmlspecialchars($conv['full_name']); ?></strong>
                            <p style="color: #718096; font-size: 14px; margin-top: 5px;">
                                <?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)); ?>...
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Chat Area -->
            <div class="chat-area">
                <?php if ($selected_user): ?>
                    <div class="chat-header">
                        <h3><?php echo htmlspecialchars($selected_user['full_name']); ?></h3>
                    </div>
                    
                    <div class="messages-list" id="messagesList">
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                    <div style="font-size: 12px; opacity: 0.7; margin-top: 5px;">
                                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="message-input-area">
                        <form class="message-form" id="messageForm">
                            <input type="hidden" id="receiverId" value="<?php echo $selected_user_id; ?>">
                            <input type="text" class="message-input" id="messageInput" placeholder="Type a message...">
                            <button type="submit" class="send-btn">Send</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
                        <p style="color: #718096;">Select a conversation to start messaging</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Auto-scroll to bottom of messages
        const messagesList = document.getElementById('messagesList');
        if (messagesList) {
            messagesList.scrollTop = messagesList.scrollHeight;
        }
        
        // Handle message form submission
        const messageForm = document.getElementById('messageForm');
        if (messageForm) {
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const receiverId = document.getElementById('receiverId').value;
                sendMessage(receiverId);
            });
        }
        
        // Refresh messages every 5 seconds
        <?php if ($selected_user_id): ?>
        setInterval(function() {
            loadMessages(<?php echo $selected_user_id; ?>);
        }, 5000);
        <?php endif; ?>
        
        function loadMessages(userId) {
            fetch(`ajax/get_messages.php?user=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update messages list
                        const messagesList = document.getElementById('messagesList');
                        messagesList.innerHTML = data.html;
                        messagesList.scrollTop = messagesList.scrollHeight;
                    }
                });
        }
    </script>
</body>
</html>