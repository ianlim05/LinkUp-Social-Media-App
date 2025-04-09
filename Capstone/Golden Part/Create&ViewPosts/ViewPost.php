<?php
session_start();
$_SESSION['user_id'] = 2; // Testing purposes

// Database connection
$conn = mysqli_connect("localhost", "root", "", "linkup");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle post submission
if (isset($_POST['submit'])) {
    // ... (existing code for post submission)
}

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'], $_POST['comment'])) {
    // ... (existing code for comment submission)
}

// Handle like/unlike
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['post_id'], $_POST['user_id'])) {
    // ... (existing code for like/unlike)
}

// Handle post report submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['report_post_id'], $_POST['report_reason'])) {
    $post_id = mysqli_real_escape_string($conn, $_POST['report_post_id']);
    $reason = mysqli_real_escape_string($conn, $_POST['report_reason']);

    $sql = "INSERT INTO reportedpost (PostID, Reason) VALUES ('$post_id', '$reason')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Post reported successfully!');</script>";
    } else {
        echo "<script>alert('Error reporting post: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch all posts
$sql = "SELECT * FROM post ORDER BY date DESC";
$result = mysqli_query($conn, $sql);
$posts = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}

// Fetch selected post details
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = $_GET['id'];
    $sql = "SELECT * FROM post WHERE PostID = $post_id";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $selectedPost = mysqli_fetch_assoc($result);
    } else {
        echo "<script>console.log('Post not found');</script>";
    }

    // Fetch comments for the selected post
    $comment_sql = "
        SELECT comments.comment, comments.date, users.username 
        FROM comments
        JOIN users ON comments.UserID = users.id
        WHERE comments.PostID = $post_id
        ORDER BY comments.date DESC";
    $comment_result = mysqli_query($conn, $comment_sql);
    $comments = [];
    while ($comment_row = mysqli_fetch_assoc($comment_result)) {
        $comments[] = $comment_row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Post</title>
    <link rel="stylesheet" href="View_Post.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <style>
        /* Modal styles */
        #reportModal {
            display: none; /* Hidden by default */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        #reportModal textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
        }
        #reportModal button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        #reportModal button:hover {
            background: #0056b3;
        }
        .overlay {
            display: none; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
<header><?php include "../header.html"; ?></header>

<div class="postContainer">
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div class="postItem">
                <a href="ViewPost.php?id=<?php echo $post['PostID']; ?>" class="hidden-link">
                    <img src="../images/<?php echo htmlspecialchars($post['fileImage']); ?>" alt="Post Image" class="postImage">
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts available.</p>
    <?php endif; ?>
</div>

<div id="overlay1">
    <div class="overlayBackground">
        <div class="overlayContent1">
            <div class="imgContainer">
                <img src="../images/<?php echo htmlspecialchars($selectedPost['fileImage']); ?>">
            </div>
            <div class="post-details">
                <h2 class="post-title"><?php echo htmlspecialchars($selectedPost['titleText']); ?></h2>
                <p class="post-text"><?php echo htmlspecialchars($selectedPost['captionText']); ?></p>

                <div class="commentList">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="commentItem">
                                <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong>:
                                    <?php echo htmlspecialchars($comment['comment']); ?>
                                </p>
                                <small><?php echo $comment['date']; ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="noComments">Start The Comments!</p>
                    <?php endif; ?>
                </div>

                <!-- Comment Section -->
                <div class="commentSection">
                    <form method="POST" autocomplete="off">
                        <input type="hidden" name="post_id" value="<?php echo $selectedPost['PostID']; ?>">
                        <input type="text" class="commentInput" name="comment" placeholder="Write a comment..." required>
                        <button type="submit" class="commentButton">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="post-footer">
            <div class="post-actions">
                <div class="heart-bg">
                    <span id="like-btn" class="heart-icon" data-post-id="<?php echo $selectedPost['PostID']; ?>" data-user-id="<?php echo $_SESSION['user_id']; ?>"></span>
                </div>
                <span id="comment-btn">💬</span>
                <span id="share-btn">🔗</span>
                <!-- New Report Post Button -->
                <button id="report-btn" onclick="openReportModal(<?php echo $selectedPost['PostID']; ?>)">Report Post</button>
            </div>
            <div class="dateCreated">
                Posted On: <?php echo $selectedPost['date']; ?>
            </div>
        </div>
        <a href="ViewPost.php" class="xButton1">✖</a>
    </div>
</div>

<!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReportModal()">&times;</span>
        <h2>Report Post</h2>
        <form id="reportForm" method="POST">
            <input type="hidden" id="reportId" name="report_post_id" value="">
            <textarea id="reportReason" name="report_reason" placeholder="Enter reason for report..." required></textarea>
            <button type="submit">Submit Report</button>
        </form>
    </div>
</div>

<!-- Overlay for modal -->
<div id="modalOverlay" class="overlay" onclick="closeReportModal()"></div>

<script>
// JavaScript for Report Modal
function openReportModal(postId) {
    document.getElementById('reportId').value = postId; // Set the post ID
    document.getElementById('reportModal').style.display = 'block'; // Show modal
    document.getElementById('modalOverlay').style.display = 'block'; // Show overlay
}

function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none'; // Hide modal
    document.getElementById('modalOverlay').style.display = 'none'; // Hide overlay
}

// Handle report form submission
document.getElementById('reportForm').addEventListener('submit', function (event) {
    event.preventDefault();
    const formData = new FormData(this);

    fetch('ViewPost.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert('Report submitted successfully!');
        closeReportModal(); // Close modal after submission
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting report.');
    });
});
</script>

<script src="View_Post.js?v=<?php echo time(); ?>"></script>
</body>
</html>