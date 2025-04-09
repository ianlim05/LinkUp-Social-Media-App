<?php
session_start();
require_once "../!! General/database.php";

header('Content-Type: application/json');

if (!isset($_GET['post_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID not provided'
    ]);
    exit();
}

try {
    $post_id = $conn->real_escape_string($_GET['post_id']);
    
    $query = "SELECT c.*, u.username 
              FROM comments c 
              JOIN users u ON c.UserID = u.id 
              WHERE c.PostID = ? 
              ORDER BY c.date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'username' => $row['username'],
            'comment' => $row['comment'],
            'date' => $row['date']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 