<?php
session_start();
require_once '../!! General/database.php';

header('Content-Type: application/json');

try {
    // Debug logging
    error_log("Like request received. POST data: " . print_r($_POST, true));
    error_log("Session data: " . print_r($_SESSION, true));

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('You must be logged in to like posts');
    }

    if (!isset($_POST['post_id'])) {
        throw new Exception('Post ID is required');
    }

    $user_id = $_SESSION['user_id'];
    $post_id = intval($_POST['post_id']);

    error_log("Processing like for PostID: $post_id, UserID: $user_id");

    // Check if user already liked the post
    $check_stmt = $conn->prepare("SELECT * FROM likes WHERE PostID = ? AND UserID = ?");
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Unlike
        $delete_stmt = $conn->prepare("DELETE FROM likes WHERE PostID = ? AND UserID = ?");
        $delete_stmt->bind_param("ii", $post_id, $user_id);
        $delete_stmt->execute();
        $liked = false;
    } else {
        // Like
        $insert_stmt = $conn->prepare("INSERT INTO likes (PostID, UserID) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $post_id, $user_id);
        $insert_stmt->execute();
        $liked = true;
    }

    // Get updated like count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE PostID = ?");
    $count_stmt->bind_param("i", $post_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $like_count = $count_result->fetch_assoc()['count'];

    error_log("Like operation successful. Liked: " . ($liked ? 'true' : 'false') . ", Count: $like_count");

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'like_count' => $like_count
    ]);

} catch (Exception $e) {
    error_log("Like error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
