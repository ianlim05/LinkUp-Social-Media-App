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
    // 1. 检查当前用户是否已经屏蔽了目标用户
    $check_query = $conn->prepare("SELECT * FROM block WHERE UserID = ? AND BlockedUserID = ?");
    $check_query->bind_param("ii", $current_user_id, $blocked_id);
    $check_query->execute();
    $result = $check_query->get_result();

    if ($result->num_rows > 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'This user is already blocked']);
        exit();
    }

    // 2. 检查目标用户是否已经屏蔽了当前用户
    $check_reverse_query = $conn->prepare("SELECT * FROM block WHERE UserID = ? AND BlockedUserID = ?");
    $check_reverse_query->bind_param("ii", $blocked_id, $current_user_id);
    $check_reverse_query->execute();
    $reverse_result = $check_reverse_query->get_result();

    // 3. 删除任何可能存在的关注关系（双向）
    // 删除当前用户关注目标用户的关系
    $delete_follow1 = $conn->prepare("DELETE FROM follow WHERE FollowerID = ? AND FollowingID = ?");
    $delete_follow1->bind_param("ii", $current_user_id, $blocked_id);
    $delete_follow1->execute();

    // 删除目标用户关注当前用户的关系
    $delete_follow2 = $conn->prepare("DELETE FROM follow WHERE FollowerID = ? AND FollowingID = ?");
    $delete_follow2->bind_param("ii", $blocked_id, $current_user_id);
    $delete_follow2->execute();

    // 4. 添加当前用户屏蔽目标用户的记录
    $insert_query = $conn->prepare("INSERT INTO block (UserID, BlockedUserID) VALUES (?, ?)");
    $insert_query->bind_param("ii", $current_user_id, $blocked_id);
    $insert_query->execute();

    // 5. 如果目标用户尚未屏蔽当前用户，添加反向屏蔽关系
    if ($reverse_result->num_rows == 0) {
        $insert_reverse_query = $conn->prepare("INSERT INTO block (UserID, BlockedUserID) VALUES (?, ?)");
        $insert_reverse_query->bind_param("ii", $blocked_id, $current_user_id);
        $insert_reverse_query->execute();
    }

    // 提交事务
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'User has been blocked (both ways)']);
    
} catch (Exception $e) {
    // 发生错误时回滚事务
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to block user: ' . $e->getMessage()]);
}

$conn->close();
?> 