<?php
session_start();
include "../!! General/database.php";

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// 检查是否收到所需的数据
if (!isset($_POST['post_id']) || !isset($_POST['title']) || !isset($_POST['caption'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

$post_id = $_POST['post_id'];
$title = $_POST['title'];
$caption = $_POST['caption'];
$user_id = $_SESSION['user_id'];

// 验证帖子是否属于当前用户
$check_query = $conn->prepare("SELECT UserID FROM post WHERE PostID = ?");
$check_query->bind_param("i", $post_id);
$check_query->execute();
$result = $check_query->get_result();
$post = $result->fetch_assoc();

if (!$post || $post['UserID'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized to edit this post']);
    exit();
}

// 更新帖子
$update_query = $conn->prepare("UPDATE post SET titleText = ?, captionText = ? WHERE PostID = ? AND UserID = ?");
$update_query->bind_param("ssii", $title, $caption, $post_id, $user_id);

if ($update_query->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update post: ' . $conn->error]);
}

$update_query->close();
$conn->close();
?> 