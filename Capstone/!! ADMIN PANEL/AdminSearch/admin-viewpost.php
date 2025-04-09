<?php
session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location:../../Auth/login.php");
    exit();
}

require_once(__DIR__ . '/../../!! General/database.php');

//Delete post
if (isset($_POST['delete_post'])) {
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    
    // Step 1: Delete likes associated with the post
    $delete_likes_query = $conn->prepare("DELETE FROM likes WHERE PostID = ?");
    $delete_likes_query->bind_param("i", $post_id);
    $delete_likes_query->execute();
    
    // Step 2: Delete comments associated with the post
    $delete_comments_query = $conn->prepare("DELETE FROM comments WHERE PostID = ?");
    $delete_comments_query->bind_param("i", $post_id);
    $delete_comments_query->execute();
    
    // Step 3: Delete the reported post record
    $delete_reported_query = $conn->prepare("DELETE FROM reportedpost WHERE PostID = ?");
    $delete_reported_query->bind_param("i", $post_id);
    $delete_reported_query->execute();
    
    // Step 4: Delete the actual post
    $delete_query = $conn->prepare("DELETE FROM post WHERE PostID = ?");
    $delete_query->bind_param("i", $post_id);
    $delete_query->execute();
    
    header("Location: ../Moderation/moderation.php"); 
    exit();
}

// Handle reject action
if (isset($_POST['reject'])) {
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    
    // Delete the reported post record
    $delete_reported_query = $conn->prepare("DELETE FROM reportedpost WHERE PostID = ?");
    $delete_reported_query->bind_param("i", $post_id);
    
    if ($delete_reported_query->execute()) {
        // Success message with JavaScript
        echo "<script>
            alert('Post report has been rejected and deleted.');
            window.location.href = '../Moderation/moderation.php';
        </script>";
    } else {
        // Error message with JavaScript
        echo "<script>
            alert('Failed to reject report. Error: " . $delete_reported_query->error . "');
            window.location.href = 'admin-viewpost.php?post_id=" . $post_id . "';
        </script>";
    }
    exit();
}

include __DIR__ . '/../../!! General/admin-header.php';

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

// Fetch post details
$post_query = $conn->prepare("SELECT * FROM post WHERE PostID = ?");
$post_query->bind_param("i", $post_id);
$post_query->execute();
$post_result = $post_query->get_result();
$post = $post_result->fetch_assoc();

if (!$post) {
    echo "Post not found.";
    exit();
}

// Fetch comments for the post
$comment_query = $conn->prepare("
    SELECT comments.CommentID, comments.comment, comments.date, users.username 
    FROM comments
    JOIN users ON comments.UserID = users.id
    WHERE comments.PostID = ?
    ORDER BY comments.date DESC
");
$comment_query->bind_param("i", $post_id);
$comment_query->execute();
$comment_result = $comment_query->get_result();
$comments = $comment_result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin View Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        .post-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .post-container img {
            width: 100%;
            border-radius: 10px;
        }
        .post-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .post-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .post-actions button.delete {
            background: #ff4d4d;
            color: white;
        }
        .post-actions button.view-profile {
            background: #4d79ff;
            color: white;
        }

        /* Comment Styles */
        .comments-section {
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .comments-section h3 {
            margin-bottom: 15px;
            color: #333;
        }
        .comment-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .comment-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .comment-item:last-child {
            border-bottom: none;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .comment-username {
            font-weight: bold;
            color: #4a90e2;
        }
        .comment-date {
            color: #666;
            font-size: 0.9em;
        }
        .comment-text {
            color: #333;
            line-height: 1.4;
        }
        .no-comments {
            text-align: center;
            color: #666;
            padding: 20px;
            font-style: italic;
        }
        .reject-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .reject-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="post-container">
        <img src="../../!! Images/<?php echo htmlspecialchars($post['fileImage']); ?>" alt="Post Image">
        <h2><?php echo htmlspecialchars($post['titleText']); ?></h2>
        <p><?php echo htmlspecialchars($post['captionText']); ?></p>
        <div class="post-actions">
            <form method="POST" style="display: inline;">
                <button type="submit" name="delete_post" class="delete">Delete</button>
            </form>
            <form method="POST" style="display: inline;" onsubmit="return confirmReject()">
                <button type="submit" name="reject" class="reject-btn">Reject</button>
            </form>
            <button onclick="location.href='admin-viewprofile.php?user_id=<?php echo $post['UserID']; ?>'" class="view-profile">View Profile</button>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="comments-section">
        <h3>Comments</h3>
        <div class="comment-list">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <span class="comment-username"><?php echo htmlspecialchars($comment['username']); ?></span>
                            <span class="comment-date"><?php echo $comment['date']; ?></span>
                        </div>
                        <div class="comment-text">
                            <?php echo htmlspecialchars($comment['comment']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-comments">No comments yet.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function confirmReject() {
        return confirm("Are you sure you want to reject and delete this post report?");
    }
    </script>
</body>
</html>