<?php
session_start();
include "../!! General/database.php"; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    
    $query = $conn->prepare("
    SELECT f.FollowID, f.FollowerID, u.username, u.profile_picture 
    FROM follow f
    JOIN users u ON f.FollowerID = u.id
    WHERE f.FollowingID = ? 
    AND f.FollowStatus = 'pending';
    ");
    
    if (!$query) {
        throw new Exception("prepare query failed: " . $conn->error);
    }
    
    $query->bind_param("i", $user_id);
    
    if (!$query->execute()) {
        throw new Exception("execute query failed: " . $query->error);
    }
    
    $result = $query->get_result();
    
    $requests = [];
    while ($row = $result->fetch_assoc()) {
       
        if (!empty($row['profile_picture'])) {
            $row['profile_picture'] = '../!! Images/' . $row['profile_picture'];
        } else {
            $row['profile_picture'] = '../!! Images/DefaultUser.jpg';
        }
        $requests[] = $row;
    }
    
    echo json_encode(['success' => true, 'requests' => $requests]);
    
} catch (Exception $e) {
    error_log("get pending follows failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'get pending follows failed: ' . $e->getMessage()]);
}
?>