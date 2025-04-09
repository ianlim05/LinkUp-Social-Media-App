<?php
session_start();
include "../!! General/database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get comments on user's posts with commenter details
$query = "SELECT c.CommentID, c.PostID, c.UserID, c.comment, c.date, 
          p.titleText, p.fileImage, u.username, u.profile_picture 
          FROM comments c 
          INNER JOIN post p ON c.PostID = p.PostID 
          INNER JOIN users u ON c.UserID = u.id 
          WHERE p.UserID = ? 
          ORDER BY c.date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = [
        'comment_id' => $row['CommentID'],
        'post_id' => $row['PostID'],
        'commenter_id' => $row['UserID'],
        'commenter_name' => $row['username'],
        'commenter_profile_pic' => $row['profile_picture'],
        'comment_text' => $row['comment'],
        'comment_date' => $row['date'],
        'post_title' => $row['titleText'],
        'post_image' => $row['fileImage']
    ];
}

echo json_encode([
    'success' => true,
    'comments' => $comments
]);

$stmt->close();
$conn->close();
?> 