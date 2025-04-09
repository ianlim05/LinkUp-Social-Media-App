<?php
session_start(); 


include "../!! General/database.php";


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit();
}

$user_id = $_SESSION['user_id']; 

// Query to get likes with post details for the current user
$sql = "SELECT l.LikeID, l.PostID, l.UserID, u.username, u.profile_picture, 
       p.fileImage, p.titleText
FROM likes l
JOIN users u ON l.UserID = u.id
JOIN post p ON l.PostID = p.PostID
WHERE p.UserID = ? 
ORDER BY l.LikeID DESC";  // Order by newest first

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); 
$stmt->execute();
$result = $stmt->get_result();

// Prepare response array
$response = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = [
            "LikeID" => $row["LikeID"],
            "PostID" => $row["PostID"],
            "UserID" => $row["UserID"],
            "username" => $row["username"],
            "profile_picture" => $row["profile_picture"] ?: "../!! Images/DefaultUser.jpg",
            "fileImage" => $row["fileImage"] ?: "../!! Images/DefaultPost.jpg",
            "titleText" => $row["titleText"] ?: "No description available."
        ];
    }

    // Success response
    echo json_encode([
        "success" => true,
        "likes" => $response
    ]);
} else {
    // No likes found
    echo json_encode([
        "success" => false,
        "message" => "No likes found."
    ]);
}

// Close connection
$conn->close();
?>
