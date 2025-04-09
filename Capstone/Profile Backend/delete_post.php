<?php
session_start();
include "../!! General/database.php";

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// 检查是否收到帖子ID
if (!isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing post ID']);
    exit();
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

// 验证帖子是否属于当前用户
$check_query = $conn->prepare("SELECT UserID, fileImage FROM post WHERE PostID = ?");
$check_query->bind_param("i", $post_id);
$check_query->execute();
$result = $check_query->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post does not exist']);
    exit();
}

if ($post['UserID'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this post']);
    exit();
}

// 开始事务
$conn->begin_transaction();

try {
    // 删除相关的评论
    $delete_comments = $conn->prepare("DELETE FROM comments WHERE PostID = ?");
    $delete_comments->bind_param("i", $post_id);
    $delete_comments->execute();

    // 删除相关的点赞
    $delete_likes = $conn->prepare("DELETE FROM likes WHERE PostID = ?");
    $delete_likes->bind_param("i", $post_id);
    $delete_likes->execute();

    // 删除帖子
    $delete_post = $conn->prepare("DELETE FROM post WHERE PostID = ? AND UserID = ?");
    $delete_post->bind_param("ii", $post_id, $user_id);
    $delete_post->execute();

    // 如果所有操作都成功，提交事务
    $conn->commit();

    // 尝试删除图片文件
    $image_path = "../!! Images/" . $post['fileImage'];
    if (file_exists($image_path)) {
        unlink($image_path);
    }

    echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
} catch (Exception $e) {
    // 如果出现错误，回滚事务
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error deleting post: ' . $e->getMessage()]);
}

// 关闭数据库连接
$conn->close();
?> 