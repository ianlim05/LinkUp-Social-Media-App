<?php
session_start();
require_once "../!! General/database.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

// Check if required data is provided
if (!isset($_POST['postId']) || !isset($_POST['comment'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required data'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $conn->real_escape_string($_POST['postId']);
$comment = $conn->real_escape_string($_POST['comment']);

try {
    // First check if the post exists
    $check_post = $conn->prepare("SELECT PostID FROM post WHERE PostID = ?");
    $check_post->bind_param("i", $post_id);
    $check_post->execute();
    $post_result = $check_post->get_result();

    if ($post_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Post not found'
        ]);
        exit();
    }

    // Insert the comment
    $insert_query = "INSERT INTO comments (PostID, UserID, comment, date) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iis", $post_id, $user_id, $comment);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    // Get the comment details including username for response
    $comment_id = $stmt->insert_id;
    $select_query = "SELECT c.*, u.username, u.profile_picture 
                    FROM comments c 
                    JOIN users u ON c.UserID = u.id 
                    WHERE c.CommentID = ?";
                    
    $select_stmt = $conn->prepare($select_query);
    $select_stmt->bind_param("i", $comment_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $comment_data = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'message' => 'Comment added successfully',
        'comment' => [
            'id' => $comment_data['CommentID'],
            'username' => $comment_data['username'],
            'comment' => $comment_data['comment'],
            'date' => $comment_data['date'],
            'profile_picture' => $comment_data['profile_picture']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 