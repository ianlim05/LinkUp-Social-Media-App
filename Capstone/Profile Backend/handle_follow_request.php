<?php
session_start();
include "../!! General/database.php"; // Database connection


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You are not logged in.']);
    exit();
}

// Validate required POST parameters
if (!isset($_POST['request_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Incomplete parameters.']);
    exit();
}

$request_id = intval($_POST['request_id']); // Cast to integer for safety
$action = $_POST['action'];
$user_id = $_SESSION['user_id']; // This is the FollowerID (receiver)

try {
    // Verify if the request belongs to the current user
    $check_query = $conn->prepare("
    SELECT * FROM follow 
    WHERE FollowingID = ? 
    AND FollowerID = ? 
    AND FollowStatus = 'pending'
");
$check_query->bind_param("ii", $user_id, $request_id);
    $check_query->execute();
    $result = $check_query->get_result();

    // Check if a matching follow request exists
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid follow request.']);
        exit();
    }
    
    if ($action === 'approve') {
        // Approve follow request
        $update_query = $conn->prepare("
            UPDATE follow 
            SET FollowStatus = 'approved' 
            WHERE FollowingID = ? 
            AND FollowerID = ?
        ");
        $update_query->bind_param("ii", $user_id, $request_id);
        $success = $update_query->execute();
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Follow request approved.']);
        } else {
            throw new Exception("Failed to update status: " . $conn->error);
        }
    } else if ($action === 'reject') {
        // Reject follow request
        $delete_query = $conn->prepare("
            DELETE FROM follow 
            WHERE FollowingID = ? 
            AND FollowerID = ?
        ");
        $delete_query->bind_param("ii", $user_id, $request_id);
        $success = $delete_query->execute();
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Follow request rejected.']);
        } else {
            throw new Exception("Failed to delete request: " . $conn->error);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action type.']);
    }
} catch (Exception $e) {
    // Log the error and return a user-friendly message
    error_log("Error handling follow request: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing request.']);
}
?>
