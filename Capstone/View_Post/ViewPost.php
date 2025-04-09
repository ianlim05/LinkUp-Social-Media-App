<?php
include "../!! General/database.php";
session_start();


if (!isset($_SESSION['user_id'])) {
    ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="icon" type="image/x-icon" href="../!! Images/LinkUp-logo.png">
            <title>Login Required</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    background: #f0f2f5;
                    font-family: Arial, sans-serif;
                }
                .modal {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    text-align: center;
                    max-width: 400px;
                    width: 90%;
                }
                .modal h2 {
                    color: #2c3e50;
                    margin-bottom: 20px;
                }
                .modal p {
                    color: #666;
                    margin-bottom: 25px;
                }
                .login-btn {
                    background: #0671B7;
                    color: white;
                    padding: 12px 30px;
                    border: none;
                    border-radius: 5px;
                    font-size: 16px;
                    cursor: pointer;
                    transition: background 0.3s ease;
                }
                .login-btn:hover {
                    background: #67A3D9;
                }
                .cancel-btn {
                    background: #f6ACC5;
                    color: black;
                    padding: 12px 20px;
                    border: none;
                    border-radius: 5px;
                    font-size: 16px;
                    cursor: pointer;
                    transition: background 0.3s ease;
                }
                .cancel-btn:hover {
                    background: #F3C4D6;
                }
                .icon {
                    font-size: 48px;
                    color: #0671B7;
                    margin-bottom: 15px;
                }

        /* Modal styles */
        #reportModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-radius: 8px;
        }
        #reportModal textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #reportModal button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        #reportModal button:hover {
            background: #0056b3;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .close {
            position: absolute;
            right: 10px;
            top: 5px;
            font-size: 24px;
            cursor: pointer;
        }                                    
            </style>
        </head>
        <body>
            <div class="modal">
                <div class="icon">🔒</div>
                <h2>Login Required</h2>
                <p>Please log in to share your thoughts, photos, and moments with your community.</p>
                <button class="login-btn" onclick="window.location.href='../Auth/login.php'">Go to Login</button>
                <button class="cancel-btn" onclick="window.location.href='../Homepage/Homepage.php'">Cancel</button>
            </div>
        </body>
        </html>
        <?php
        exit();
    }



// Retrieves data from posts table
$sql = "SELECT titleText, captionText, fileImage, date FROM post"; 
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
} else {
    echo "<script>console.log('Error fetching data: " . mysqli_error($conn) . "');</script>";
}



// Fetch all posts from the 'sharingimage' table
$sql = "SELECT * FROM post ORDER BY date DESC";
$result = mysqli_query($conn, $sql);

$posts = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $postID = $_GET['id'];  // Retrieve the postID from URL

    $sql = "SELECT date FROM post WHERE postID = $postID ORDER BY date DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $latestDate = $row['date']; // Store the fetched date
        echo "<script>console.log('✅ Latest date for Post ID $postID: $latestDate');</script>";
    } else {
        echo "<script>console.log('⚠️ Error: No results found for Post ID $postID.');</script>";
    }
} else {
    echo "<script>console.log('⚠️ No valid Post ID found in URL.');</script>";
}

$sql = "SELECT BlacklistedWord FROM filter"; 
$result = mysqli_query($conn, $sql);

$blacklistedKeywords = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $blacklistedKeywords[] = $row['BlacklistedWord'];
    }
}

$errorMessage = "";
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear message after displaying
}

// Handle comment submission
                if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'], $_POST['comment'])) {
                    if (!isset($_SESSION['user_id'])) {
                        $_SESSION['error_message'] = "You must be logged in to comment.";
                        header("Location: ViewPost.php?id=" . $_POST['post_id']);
                        exit();
                    }

                    $PostID = $_POST['post_id'];
                    $UserID = $_SESSION['user_id'];
                    $comment = trim(mysqli_real_escape_string($conn, $_POST['comment']));
                    $date = date("Y-m-d");

                    // Checks for blacklisted words
                    foreach ($blacklistedKeywords as $word) {
                        if (stripos($comment, $word) !== false) {
                            $_SESSION['error_message'] = "Your comment contains inappropriate language and cannot be posted.";
                            header("Location: ViewPost.php?id=" . $PostID);
                            exit();
                        }
                    }

                    // Insert clean comment into the database
                    $insert_sql = "INSERT INTO comments (PostID, UserID, comment, date) VALUES ('$PostID', '$UserID', '$comment', '$date')";
                    mysqli_query($conn, $insert_sql);

                    // Redirect to refresh page without error message
                    header("Location: ViewPost.php?id=" . $PostID);
                    exit();
                }

                $comments = [];
                // Check if 'id' exists in the URL before using it
                if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $post_id = $_GET['id'];

                    // Fetch the post details
                    $sql = "SELECT * FROM post WHERE PostID = $post_id"; 
                    $result = mysqli_query($conn, $sql);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        $selectedPost = mysqli_fetch_assoc($result);
                    } else {
                        echo "<script>console.log('Post not found');</script>";
                    }

                    // Fetch comments for this post
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
                } else {
                    
                }

                
            
                
                if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['post_id'], $_POST['user_id'])) {
                    $post_id = $_POST['post_id'];
                    $user_id = $_POST['user_id'];
                
                    // Check if user already liked the post
                    $query = "SELECT * FROM likes WHERE PostID = ? AND UserID = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                
                    if (mysqli_num_rows($result) > 0) {
                        $delete_query = "DELETE FROM likes WHERE PostID = ? AND UserID = ?";
                        $stmt = mysqli_prepare($conn, $delete_query);
                        mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
                        mysqli_stmt_execute($stmt);
                
                        echo "unliked"; 
                    } else {
                        $insert_query = "INSERT INTO likes (PostID, UserID) VALUES (?, ?)";
                        $stmt = mysqli_prepare($conn, $insert_query);
                        mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
                        mysqli_stmt_execute($stmt);
                
                        echo "liked"; 
                    }
                
                    mysqli_stmt_close($stmt);
                    mysqli_close($conn);
                    exit();
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
    <script src="../!! General/Countdown_Timer.js"></script>
</head>

<header><?php include "../!! General/header.php";?> </header>

<body>
        <div class="postContainer">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="postItem"> <!-- New wrapper for styling & hover effects -->
                        <a href="ViewPost.php?id=<?php echo $post['PostID']; ?>" class="hidden-link">
                            <img src="../!! Images/<?php echo htmlspecialchars($post['fileImage']); ?>" 
                                alt="Post Image" 
                                class="postImage">
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No posts available.</p>
            <?php endif; ?>
        </div>



    <div id="overlay1" >
        <div class="overlayBackground">
            <div class="overlayContent1">
                <div class="imgContainer">
                <img src="../!! Images/<?php echo htmlspecialchars($selectedPost['fileImage']); ?>">
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

                    <!-- Displays an error message if a blacklisted keyword is found -->
                    <?php if (!empty($errorMessage)): ?>
                        <div class="error-message" >
                            <?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>    
                    
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
                        <span id="like-btn" class="heart-icon" 
                            data-post-id="<?php echo $selectedPost['PostID']; ?>" 
                            data-user-id="<?php echo $_SESSION['user_id']; ?>">
                        </span>
                    </div>
                        <span id="comment-btn">💬</span>
                        <button id="report-btn" onclick="openReportModal(<?php echo $selectedPost['PostID']; ?>)" class="ReportButton">🚩</button>
                    </div>
                    <div class="dateCreated">
                         Posted On: <?php echo $row['date'];?>
                    </div>
                </div>
                
        <a href="ViewPost.php" class="xButton1">✖</a>
        </div>
    </div>


    <script src="View_Post.js?v=<?php echo time(); ?>"></script>

        <!-- Report Modal -->
        <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReportModal()">&times;</span>
            <h2>Report Post</h2>
            <form id="reportForm" method="POST">
                <input type="hidden" id="reportId" name="post_id" value="">
                <textarea id="reportReason" name="reason" placeholder="Enter reason for report..." required></textarea>
                <button type="submit">Submit Report</button>
            </form>
        </div>
    </div>

    <!-- Overlay for modal -->
    <div id="modalOverlay" class="overlay" onclick="closeReportModal()"></div>

    <script>
    // JavaScript for Report Modal
    function openReportModal(postId) {
        document.getElementById('reportId').value = postId;
        document.getElementById('reportModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    function closeReportModal() {
        document.getElementById('reportModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }

    // Handle report form submission
    document.getElementById('reportForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('report_post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Post reported successfully!');
                closeReportModal();
            } else {
                alert(data.message || 'Failed to report post');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting report.');
        });
    });
    </script>
</body>
</html>