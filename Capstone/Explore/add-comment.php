<?php
session_start();
require_once '../!! General/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_id'], $_POST['comment'])) {
    $response = ['success' => false];
    
    if (!isset($_SESSION['user_id'])) {
        $response['error'] = 'You must be logged in to comment';
        echo json_encode($response);
        exit;
    }

    $post_id = intval($_POST['post_id']);
    $user_id = $_SESSION['user_id'];
    $comment = trim($_POST['comment']);

    if (empty($comment)) {
        $response['error'] = 'Comment cannot be empty';
        echo json_encode($response);
        exit;
    }

    // Insert the comment
    $stmt = $conn->prepare("INSERT INTO comments (PostID, UserID, comment, date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $post_id, $user_id, $comment);
    
    if ($stmt->execute()) {
        // Get the username and formatted date
        $username_query = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $username_query->bind_param("i", $user_id);
        $username_query->execute();
        $username_result = $username_query->get_result();
        $user_data = $username_result->fetch_assoc();
        
        $response['success'] = true;
        $response['username'] = $user_data['username'];
        $response['date'] = date("Y-m-d H:i:s"); // Or get from database if you prefer
    } else {
        $response['error'] = 'Failed to post comment';
    }

    echo json_encode($response);
}
?>