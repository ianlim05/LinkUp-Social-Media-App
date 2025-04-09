<?php
session_start(); // Start the session

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location:../../Auth/login.php");
    exit();
}

require_once(__DIR__ . '/../../!! General/database.php');

// Handle ban/unban action first
if (isset($_POST['ban_action'])) {
    $user_id = $_POST['user_id']; // Get user_id from form
    $ban_query = $conn->prepare("SELECT * FROM ban WHERE UserID = ?");
    $ban_query->bind_param("i", $user_id);
    $ban_query->execute();
    $ban_result = $ban_query->get_result();
    $is_banned = $ban_result->num_rows > 0;

    if ($is_banned) {
        // Unban the user
        $delete_ban_query = $conn->prepare("DELETE FROM ban WHERE UserID = ?");
        $delete_ban_query->bind_param("i", $user_id);
        $delete_ban_query->execute();
    } else {
        // Ban the user
        $ban_date = date('Y-m-d H:i:s');
        $insert_ban_query = $conn->prepare("INSERT INTO ban (UserID, BanDate) VALUES (?, ?)");
        $insert_ban_query->bind_param("is", $user_id, $ban_date);
        $insert_ban_query->execute();
    }
    header("Location: admin-viewprofile.php?user_id=$user_id");
    exit();
}

// Handle warning submission
if (isset($_POST['warn']) && isset($_POST['warning_reason'])) {
    $user_id = $_POST['user_id'];
    $warning_reason = mysqli_real_escape_string($conn, $_POST['warning_reason']);
    $warning_date = date('Y-m-d H:i:s'); // Get current timestamp
    
    // Insert warning into database with WarningDate
    $warning_query = $conn->prepare("INSERT INTO warning (UserID, WarningReason, WarningDate) VALUES (?, ?, ?)");
    $warning_query->bind_param("iss", $user_id, $warning_reason, $warning_date);
    
    if ($warning_query->execute()) {
        // Success message with JavaScript
        echo "<script>
            alert('Warning has been successfully issued to the user.');
            window.location.href = 'admin-viewprofile.php?user_id=" . $user_id . "';
        </script>";
    } else {
        // Error message with JavaScript
        echo "<script>
            alert('Failed to issue warning. Error: " . $warning_query->error . "');
            window.location.href = 'admin-viewprofile.php?user_id=" . $user_id . "';
        </script>";
    }
    exit();
}

// Handle reject action
if (isset($_POST['reject'])) {
    $user_id = $_POST['user_id'];
    
    // Delete the user's reports from reporteduser table
    $delete_reports_query = $conn->prepare("DELETE FROM reporteduser WHERE UserID = ?");
    $delete_reports_query->bind_param("i", $user_id);
    
    if ($delete_reports_query->execute()) {
        // Success message with JavaScript
        echo "<script>
            alert('User reports have been rejected and deleted.');
            window.location.href = '../Moderation/moderation.php';
        </script>";
    } else {
        // Error message with JavaScript
        echo "<script>
            alert('Failed to reject reports. Error: " . $delete_reports_query->error . "');
            window.location.href = 'admin-viewprofile.php?user_id=" . $user_id . "';
        </script>";
    }
    exit();
}

// Handle delete post action - Moved to top before any output
if (isset($_POST['delete_post'])) {
    $post_id = $_POST['post_id'];
    
    // Step 1: Delete related records in the reportedpost table
    $delete_reported_query = $conn->prepare("DELETE FROM reportedpost WHERE PostID = ?");
    $delete_reported_query->bind_param("i", $post_id);
    $delete_reported_query->execute();
    
    // Step 2: Delete the post from the post table
    $delete_query = $conn->prepare("DELETE FROM post WHERE PostID = ?");
    $delete_query->bind_param("i", $post_id);
    
    if ($delete_query->execute()) {
        // Use JavaScript for redirect instead of PHP header
        echo "<script>window.location.href = 'admin-viewprofile.php?user_id=" . $user_id . "';</script>";
        exit();
    } else {
        // Handle deletion error
        echo "<script>alert('Error deleting post');</script>";
    }
}

include '../../!! General/admin-header.php';

$user_id = null; // Initialize

// Check if ID is in URL
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} elseif (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

// If still no user ID, show an error
if (!$user_id) {
    echo "Profile not found. <a href='login.php'>Login here</a>";
    exit();
}

// Fetch user details
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$users = $result->fetch_assoc();

if (!$users) {
    echo "User not found.";
    exit();
}

// Check if the user is banned
$ban_query = $conn->prepare("SELECT * FROM ban WHERE UserID = ?");
$ban_query->bind_param("i", $user_id);
$ban_query->execute();
$ban_result = $ban_query->get_result();
$is_banned = $ban_result->num_rows > 0;

// Get followers count
$followers_query = $conn->prepare("SELECT COUNT(*) AS count FROM follow WHERE FollowingID = ? AND FollowStatus = 'approved'");
$followers_query->bind_param("i", $user_id);
$followers_query->execute();
$followers_result = $followers_query->get_result();
$followers_count = $followers_result->fetch_assoc()['count'];

// Get following count
$following_query = $conn->prepare("SELECT COUNT(*) AS count FROM follow WHERE FollowerID = ?");
$following_query->bind_param("i", $user_id);
$following_query->execute();
$following_result = $following_query->get_result();
$following_count = $following_result->fetch_assoc()['count'];

// Get posts count
$posts_query = $conn->prepare("SELECT COUNT(*) AS count FROM post WHERE UserID = ?");
$posts_query->bind_param("i", $user_id);
$posts_query->execute();
$posts_result = $posts_query->get_result();
$posts_count = $posts_result->fetch_assoc()['count'];

// Fetch all posts by the user
$posts_query = $conn->prepare("SELECT * FROM post WHERE UserID = ?");
$posts_query->bind_param("i", $user_id);
$posts_query->execute();
$posts_result = $posts_query->get_result();
$posts = $posts_result->fetch_all(MYSQLI_ASSOC);

// Fetch comments for the selected post
$comments = [];
$selectedPost = null;
if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    $post_query = $conn->prepare("SELECT * FROM post WHERE PostID = ?");
    $post_query->bind_param("i", $post_id);
    $post_query->execute();
    $post_result = $post_query->get_result();
    $selectedPost = $post_result->fetch_assoc();

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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu" />
    <title>Admin View Profile</title>
    <style>
        /* Override admin-header styles to ensure fixed positioning and correct sizing */
        .h-header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            z-index: 1000 !important;
            padding: 20px 30px !important;
            box-sizing: border-box !important;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "DM Sans", sans-serif;
            background: #f4f4f4;
            padding-top: 150px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 10px;
        }

        .profile-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 0;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .ProfilePic {
            width: 13rem;
            height: 13rem;
            border-radius: 75%;
            overflow: hidden;
            border: 2px solid #ddd;
        }

        .ProfilePic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-details {
            display: flex;
            flex-direction: column;
            gap: 20px;
            flex: 1;
        }

        .top-section {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: space-between;
            width: 100%;
        }

        .profile-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .profile-stats {
            display: flex;
            gap: 18px;
            font-size: 18px;
            color: #333;
        }

        .profile-stats button {
            background-color: transparent;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            color: #000;
        }

        .profile-stats button:hover {
            color: #007bff;
        }

        .profile-bio {
            color: #666;
            line-height: 1.6;
        }

        .admin-actions {
            display: flex;
            gap: 10px;
        }

        .ban-btn, .warn-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .ban-btn {
            background-color: #ff4d4d;
            color: white;
        }

        .ban-btn:hover {
            background-color: #cc0000;
        }

        .warn-btn {
            background-color: #ffd700;
            color: #333;
        }

        .warn-btn:hover {
            background-color: #ffcc00;
        }

        .admin-actions input[type="text"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 5px;
        }

        hr {
            width: 50rem;
            border-top: 1px solid #000000;
            margin: 20px auto;
        }

        .post-section {
            display: flex;
            justify-content: center;
            width: 100%;
            max-width: 935px;
            margin: 0 auto;
            padding: 20px;
            min-height: 500px;
        }

        .post-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 30px;
            max-width: 1080px;
            width: 100%;
            height: 100%;
            justify-content: center;
            margin-top: 20px;
        }

        .postItem {
            position: relative;
            overflow: hidden;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .postItem:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .postImage {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }

        .no-posts {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
            color: #555;
            grid-column: 1 / -1;
        }

        .no-posts img {
            max-width: 100%;
            display: block;
            margin: 0 auto;
        }

        .no-posts-text {
            margin: 0;
            font-size: 36px;
            color: #555;
            font-weight: bold;
        }

        /* Overlay styles */
        #overlay1 {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .overlayBackground {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .overlayContent1 {
            display: flex;
            gap: 20px;
        }

        .imgContainer {
            width: 500px;
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
        }

        .imgContainer img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .post-details {
            flex: 1;
            min-width: 300px;
        }

        .post-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .post-text {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .commentList {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            max-height: 200px;
            overflow-y: auto;
        }

        .commentItem {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .commentItem:last-child {
            border-bottom: none;
        }

        .delete-btn {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #cc0000;
        }

        .xButton1 {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            color: #333;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .xButton1:hover {
            color: #ff4d4d;
        }

        .post-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .dateCreated {
            color: #666;
            font-size: 14px;
        }

        .reject-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
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

<div class="container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="ProfilePic">
                <?php
                if (!empty($users['profile_picture'])) {
                    $profile_picture = '../../!! Images/' . $users['profile_picture'];
                } else {
                    $profile_picture = '../../!! Images/DefaultUser.jpg';
                }
                ?>
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
            </div>
            <div class="profile-details">
                <div class="top-section">
                    <h2 class="profile-name"><?php echo $users['username']; ?></h2>
                    <div class="admin-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <button type="submit" name="ban_action" class="ban-btn <?php echo $is_banned ? 'banned' : ''; ?>">
                                <?php echo $is_banned ? 'Unban' : 'Ban'; ?>
                            </button>
                        </form>
                        <form method="POST" style="display: inline;" onsubmit="return confirmWarn()">
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <input type="text" name="warning_reason" placeholder="Warning reason" required>
                            <button type="submit" name="warn" class="warn-btn">Warn</button>
                        </form>
                        <form method="POST" style="display: inline;" onsubmit="return confirmReject()">
                            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                            <button type="submit" name="reject" class="reject-btn">Reject</button>
                        </form>
                    </div>
                </div>
                <div class="profile-stats">
                    <button class="Post"><?php echo $posts_count; ?> Posts</button>
                    <button class="follower-btn"><?php echo $followers_count; ?> Followers</button>
                    <button class="following-btn"><?php echo $following_count; ?> Following</button>
                </div>
                <div class="profile-bio">
                    <p><?php echo nl2br($users['bio']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="post-section">
    <div class="post-grid">
        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <img src="../../!! Images/DefaultPost.png" alt="No posts yet">
                <p class="no-posts-text">No posts yet</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="postItem">
                    <a href="admin-viewprofile.php?user_id=<?php echo $user_id; ?>&post_id=<?php echo $post['PostID']; ?>" class="hidden-link">
                        <img src="../../!! Images/<?php echo htmlspecialchars($post['fileImage']); ?>" alt="Post Image" class="postImage" data-image="<?php echo htmlspecialchars($post['fileImage']); ?>">
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Overlay for post details -->
<div id="overlay1">
    <div class="overlayBackground">
        <div class="overlayContent1">
            <div class="imgContainer">
                <img id="overlay-image" src="../../!! Images/<?php echo htmlspecialchars($selectedPost['fileImage'] ?? ''); ?>" alt="Post Image">
            </div>
            <div class="post-details">
                <h2 class="post-title" id="overlay-title"><?php echo htmlspecialchars($selectedPost['titleText'] ?? ''); ?></h2>
                <p class="post-text" id="overlay-caption"><?php echo htmlspecialchars($selectedPost['captionText'] ?? ''); ?></p>

                <div class="commentList">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="commentItem">
                                <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong>: <?php echo htmlspecialchars($comment['comment']); ?></p>
                                <small><?php echo $comment['date']; ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="noComments">No comments yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Delete Post Button -->
                <form method="POST" onsubmit="return confirmDeletePost()">
                    <input type="hidden" name="post_id" id="overlay-post-id" value="<?php echo $selectedPost['PostID'] ?? ''; ?>">
                    <button type="submit" name="delete_post" class="delete-btn">Delete Post</button>
                </form>
            </div>
        </div>
        <div class="post-footer">
            <div class="dateCreated">
                Posted On: <span id="overlay-date"></span>
            </div>
        </div>
        <a href="admin-viewprofile.php?user_id=<?php echo $user_id; ?>" class="xButton1">✖</a>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const overlay = document.getElementById("overlay1");
    const closeButton = document.querySelector(".xButton1");
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has("post_id")) {
        const postId = urlParams.get("post_id");
        fetchPostDetails(postId);
        overlay.style.visibility = "visible";
        overlay.style.opacity = "1";
    }

    document.addEventListener("click", function (event) {
        if (event.target.closest(".hidden-link")) {
            event.preventDefault();
            const postId = event.target.closest(".hidden-link").getAttribute("href").split("post_id=")[1];
            fetchPostDetails(postId);
            overlay.style.visibility = "visible";
            overlay.style.opacity = "1";
        }
    });

    closeButton.addEventListener("click", function (event) {
        event.preventDefault();
        overlay.style.opacity = "0";
        setTimeout(() => {
            overlay.style.visibility = "hidden";
            window.history.pushState({}, "", "admin-viewprofile.php?user_id=<?php echo $user_id; ?>");
        }, 500);
    });

    overlay.addEventListener("click", function (event) {
        if (event.target === overlay) {
            overlay.style.opacity = "0";
            setTimeout(() => {
                overlay.style.visibility = "hidden";
                window.history.pushState({}, "", "admin-viewprofile.php?user_id=<?php echo $user_id; ?>");
            }, 500);
        }
    });

    function fetchPostDetails(postId) {
        // Fetch post details from the server
        fetch(`admin-viewprofile.php?user_id=<?php echo $user_id; ?>&post_id=${postId}`)
            .then(response => response.text())
            .then(data => {
                // Update the overlay with the fetched post details
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, "text/html");
                
                // Update post image
                const postImage = doc.querySelector("#overlay-image");
                if (postImage) {
                    document.getElementById("overlay-image").src = postImage.src;
                }
                
                // Update post title
                const postTitle = doc.querySelector("#overlay-title");
                if (postTitle) {
                    document.getElementById("overlay-title").textContent = postTitle.textContent;
                }
                
                // Update post caption
                const postCaption = doc.querySelector("#overlay-caption");
                if (postCaption) {
                    document.getElementById("overlay-caption").textContent = postCaption.textContent;
                }
                
                // Update post date
                const postDate = doc.querySelector("#overlay-date");
                if (postDate) {
                    document.getElementById("overlay-date").textContent = postDate.textContent;
                }
                
                // Update post ID for delete button
                const postIdInput = doc.querySelector("#overlay-post-id");
                if (postIdInput) {
                    document.getElementById("overlay-post-id").value = postIdInput.value;
                }
                
                // Update comments section
                const commentList = doc.querySelector(".commentList");
                if (commentList) {
                    document.querySelector(".commentList").innerHTML = commentList.innerHTML;
                }
            })
            .catch(error => console.error("Error fetching post details:", error));
    }

    function confirmDeletePost() {
        return confirm("Are you sure you want to delete this post?");
    }

    function confirmWarn() {
        const reason = document.querySelector('input[name="warning_reason"]').value;
        return confirm(`Are you sure you want to warn this user?\nReason: ${reason}`);
    }

    function confirmReject() {
        return confirm("Are you sure you want to reject and delete all reports for this user?");
    }
});
</script>
</body>
</html>