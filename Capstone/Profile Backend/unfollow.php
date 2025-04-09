<?php
session_start();
include "../!! General/database.php";

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

try {
    // 开始事务
    $conn->begin_transaction();

    // 删除关注记录
    $stmt = $conn->prepare("DELETE FROM follow WHERE FollowerID = ? AND FollowingID = ? AND FollowStatus = 'approved'");
    $stmt->bind_param("ii", $_SESSION['user_id'], $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete follow record");
    }

    // 如果删除成功
    if ($stmt->affected_rows > 0) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Successfully unfollowed']);
    } else {
        throw new Exception("No follow record found");
    }

} catch (Exception $e) {
    // 回滚事务
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?> 