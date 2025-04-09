<?php
session_start();
include "../!! General/database.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if blocked user ID is provided
if (!isset($_POST['blocked_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
    exit();
}

$blocked_id = $_POST['blocked_user_id'];
$current_user_id = $_SESSION['user_id'];

// 开始事务以确保操作的原子性
$conn->begin_transaction();

try {
    // 1. 删除关注关系（如果存在）
    // 检查当前用户是否关注了被解除屏蔽的用户
    $check_follow = $conn->prepare("SELECT * FROM follow WHERE FollowerID = ? AND FollowingID = ?");
    $check_follow->bind_param("ii", $current_user_id, $blocked_id);
    $check_follow->execute();
    $follow_result = $check_follow->get_result();
    
    if ($follow_result->num_rows > 0) {
        // 如果存在关注关系，则删除
        $delete_follow = $conn->prepare("DELETE FROM follow WHERE FollowerID = ? AND FollowingID = ?");
        $delete_follow->bind_param("ii", $current_user_id, $blocked_id);
        $delete_follow->execute();
    }
    
    // 2. 删除屏蔽关系
    $delete_query = $conn->prepare("DELETE FROM block WHERE UserID = ? AND BlockedUserID = ?");
    $delete_query->bind_param("ii", $current_user_id, $blocked_id);
    $delete_query->execute();
    
    // 如果成功，提交事务
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'User has been unblocked']);
} catch (Exception $e) {
    // 如果失败，回滚事务
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to unblock user: ' . $e->getMessage()]);
}

$conn->close();
?> 