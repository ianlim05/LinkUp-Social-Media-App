<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

session_start();
require_once '../!! General/database.php';

try {
    $posts = [];
    $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    // Updated query to use manage_visibility from users table
    $query = "SELECT p.PostID, p.fileImage, p.titleText, p.captionText, p.date,
                     u.username, u.profile_picture 
              FROM post p 
              JOIN users u ON p.UserID = u.id 
              WHERE u.id NOT IN (
                  SELECT BlockedUserID 
                  FROM block 
                  WHERE UserID = " . $current_user_id . "
              )
              AND u.id NOT IN (
                  SELECT UserID 
                  FROM block 
                  WHERE BlockedUserID = " . $current_user_id . "
              )
              AND u.manage_visibility = 'public'"; // Changed to use manage_visibility from users table
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $conn->real_escape_string($_GET['search']);
        $query .= " AND (p.titleText LIKE '%$search%' OR p.captionText LIKE '%$search%')";
    }
    
    $query .= " ORDER BY p.date DESC";

    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
    
    $output = ob_get_clean();
    file_put_contents('debug.txt', $output); // This will save the output to debug.txt
    echo json_encode(['success' => true, 'posts' => $posts]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

exit;
?>
