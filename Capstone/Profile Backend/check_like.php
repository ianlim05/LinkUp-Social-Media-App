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
    // Check if user has liked the post
    $check_query = "SELECT COUNT(*) as liked FROM likes WHERE PostID = ? AND UserID = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    // Get total likes count
    $count_query = "SELECT COUNT(*) as total_likes FROM likes WHERE PostID = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param("i", $post_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'liked' => $row['liked'] > 0,
        'total_likes' => $count_row['total_likes']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 