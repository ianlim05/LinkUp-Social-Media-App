<?php
session_start();
include "../../!! General/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

if (!isset($_POST['postId'])) {
    echo "error:Post ID is required";
    exit();
}

$userId = $_SESSION['user_id'];
$postId = $_POST['postId'];

// Check if post exists
$check_post = $conn->prepare("SELECT UserID FROM post WHERE PostID = ?");
$check_post->bind_param("i", $postId);
$check_post->execute();
$post_result = $check_post->get_result();

if ($post_result->num_rows === 0) {
    echo "error:Post not found";
    exit();
}

// Check if user has already liked the post
$check_like = $conn->prepare("SELECT LikeID FROM likes WHERE PostID = ? AND UserID = ?");
$check_like->bind_param("ii", $postId, $userId);
$check_like->execute();
$like_result = $check_like->get_result();

if ($like_result->num_rows > 0) {
    // Unlike the post
    $delete_like = $conn->prepare("DELETE FROM likes WHERE PostID = ? AND UserID = ?");
    $delete_like->bind_param("ii", $postId, $userId);
    
    if ($delete_like->execute()) {
        // Get updated like count
        $count_likes = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE PostID = ?");
        $count_likes->bind_param("i", $postId);
        $count_likes->execute();
        $count_result = $count_likes->get_result()->fetch_assoc();
        
        echo "unliked:" . $count_result['count'];
    } else {
        echo "error:Failed to unlike post";
    }
} else {
    // Like the post
    $add_like = $conn->prepare("INSERT INTO likes (PostID, UserID) VALUES (?, ?)");
    $add_like->bind_param("ii", $postId, $userId);
    
    if ($add_like->execute()) {
        // Get updated like count
        $count_likes = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE PostID = ?");
        $count_likes->bind_param("i", $postId);
        $count_likes->execute();
        $count_result = $count_likes->get_result()->fetch_assoc();
        
        echo "liked:" . $count_result['count'];
    } else {
        echo "error:Failed to like post";
    }
}

$conn->close();
?>