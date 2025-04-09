<?php
session_start();
require_once "../!! General/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

if (!isset($_GET['post_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID not provided'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $conn->real_escape_string($_GET['post_id']);

try {
    // Get post details
    $query = "SELECT p.*, u.username 
              FROM post p 
              JOIN users u ON p.UserID = u.id 
              WHERE p.PostID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    if (!$post) {
        echo json_encode([
            'success' => false,
            'message' => 'Post not found'
        ]);
        exit();
    }

    // Check if the user has liked this post
    $like_query = "SELECT * FROM likes WHERE PostID = ? AND UserID = ?";
    $like_stmt = $conn->prepare($like_query);
    $like_stmt->bind_param("ii", $post_id, $user_id);
    $like_stmt->execute();
    $is_liked = $like_stmt->get_result()->num_rows > 0;

    echo json_encode([
        'success' => true,
        'post' => [
            'PostID' => $post['PostID'],
            'UserID' => $post['UserID'],
            'username' => $post['username'],
            'titleText' => $post['titleText'],
            'captionText' => $post['captionText'],
            'fileImage' => $post['fileImage'],
            'date' => $post['date']
        ],
        'is_liked' => $is_liked
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 