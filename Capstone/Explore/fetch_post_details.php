<?php
session_start();
require_once '../!! General/database.php';

// Set headers
header('Content-Type: application/json');

try {
    // Check if post_id exists and is numeric
    if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
        throw new Exception('Invalid or missing post ID');
    }

    $post_id = intval($_GET['post_id']);
    
    // Debug log
    error_log("Fetching post ID: " . $post_id);
    
    // Fetch post details with user info
    $query = "SELECT p.PostID, p.UserID, p.fileImage, p.date, p.captionText, p.titleText,
              u.username, u.profile_picture 
              FROM post p 
              JOIN users u ON p.UserID = u.id 
              WHERE p.PostID = ?";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare post query: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    if (!$post) {
        throw new Exception('Post not found');
    }
    
    // Fetch comments for the post
    $commentQuery = "SELECT c.*, u.username 
                    FROM comments c 
                    JOIN users u ON c.UserID = u.id 
                    WHERE c.PostID = ?
                    ORDER BY c.date DESC";
                    
    $stmt = $conn->prepare($commentQuery);
    if (!$stmt) {
        throw new Exception('Failed to prepare comment query: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Add this to check if user has liked the post
    $liked_check = $conn->prepare("SELECT * FROM likes WHERE PostID = ? AND UserID = ?");
    $liked_check->bind_param("ii", $post_id, $_SESSION['user_id']);
    $liked_check->execute();
    $is_liked = $liked_check->get_result()->num_rows > 0;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'post' => $post,
        'comments' => $comments,
        'is_liked' => $is_liked
    ]);

} catch (Exception $e) {
    // Return error response
    error_log("Error in fetch_post_details.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

exit;
?>
