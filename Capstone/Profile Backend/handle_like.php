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

if (!isset($_POST['post_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID not provided'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = $conn->real_escape_string($_POST['post_id']);

try {
    // Check if user has already liked the post
    $check_query = "SELECT * FROM likes WHERE PostID = ? AND UserID = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // User has already liked the post, so unlike it
        $delete_query = "DELETE FROM likes WHERE PostID = ? AND UserID = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("ii", $post_id, $user_id);
        $delete_stmt->execute();

        echo json_encode([
            'success' => true,
            'liked' => false,
            'message' => 'Post unliked successfully'
        ]);
    } else {
        // User hasn't liked the post, so add the like
        $insert_query = "INSERT INTO likes (PostID, UserID, date) VALUES (?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ii", $post_id, $user_id);
        $insert_stmt->execute();

        echo json_encode([
            'success' => true,
            'liked' => true,
            'message' => 'Post liked successfully'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 