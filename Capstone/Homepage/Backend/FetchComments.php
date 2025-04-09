<?php
session_start();
require_once "../../!! General/database.php";

header('Content-Type: application/json');

if (!isset($_GET['post_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Post ID not provided'
    ]);
    exit();
}

$post_id = $conn->real_escape_string($_GET['post_id']);

try {
    $query = "SELECT c.*, u.username 
              FROM comments c 
              JOIN users u ON c.UserID = u.id 
              WHERE c.PostID = ? 
              ORDER BY c.date DESC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $post_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
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