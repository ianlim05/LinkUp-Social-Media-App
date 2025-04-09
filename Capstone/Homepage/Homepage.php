<?php
include "../!! General/header.php";
include "../!! General/database.php";

// Fetch posts from the database with privacy checks
$query = "
    SELECT 
        p.PostID,
        p.fileImage,
        p.captionText,
        p.titleText,
        p.date,
        u.id AS UserID,
        u.username,
        u.profile_picture,
        u.manage_visibility
    FROM 
        post p
    JOIN 
        users u ON p.UserID = u.id
";

// Add conditions for blocking and private posts
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $query .= " WHERE (
            u.manage_visibility = 'public' OR 
            (u.manage_visibility = 'private' AND p.UserID IN (
                SELECT FollowingID FROM follow 
                WHERE FollowerID = $user_id
            )) OR
            p.UserID = $user_id
        )
        AND p.UserID NOT IN (
            SELECT BlockedUserID FROM block 
            WHERE UserID = $user_id
        )";
} else {
    // For non-logged-in users, only show public posts
    $query .= " WHERE u.manage_visibility = 'public'";
}

$query .= " ORDER BY p.date DESC;";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching posts: " . mysqli_error($conn));
}

// Function to fetch likes for a post
function getLikesCount($conn, $postId) {
    $query = "SELECT COUNT(*) as likeCount FROM likes WHERE PostID = $postId";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['likeCount'];
}

// Function to check if current user liked the post
function isPostLikedByUser($conn, $postId, $userId) {
    $query = "SELECT * FROM likes WHERE PostID = $postId AND UserID = $userId";
    $result = mysqli_query($conn, $query);
    return (mysqli_num_rows($result) > 0);
}

// Function to fetch comments for a post
function getComments($conn, $postId) {
    $query = "
        SELECT 
            c.comment,
            c.date,
            u.username
        FROM 
            comments c
        JOIN 
            users u ON c.UserID = u.id
        WHERE 
            c.PostID = $postId
        ORDER BY 
            c.date DESC;
    ";
    $result = mysqli_query($conn, $query);
    $comments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
    }
    return $comments;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="Homepage.css">
    <link rel="stylesheet" href="Overlay.css">
    <!-- from https://cdnjs.com/libraries/font-awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Testing, though it doesn't work
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css"> -->
    <script src="../!! General/Countdown_Timer.js"></script>
</head>
<body class="h-body">
    <main class="ho-container">
        <?php 
            // Store all posts in an array first
            $allPosts = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $allPosts[] = $row;
            }
            
            foreach ($allPosts as $row): 
                $isLiked = isset($_SESSION['user_id']) ? isPostLikedByUser($conn, $row['PostID'], $_SESSION['user_id']) : false;
                $comments = getComments($conn, $row['PostID']);
        ?>
            <section class="ho-post" data-postid="<?php echo $row['PostID']; ?>">
                <!-- Post Header -->
                <header class="ho-post-header">
                <div class="ho-profile-pic" style="background-image: url('../!! Images/<?php echo !empty($row['profile_picture']) ? htmlspecialchars($row['profile_picture']) : 'user_icon.jpg'; ?>');"></div>
                    <strong>
                        <a href="<?php 
                            if (isset($_SESSION['user_id']) && $row['UserID'] == $_SESSION['user_id']) {
                                echo '../View Own Profile/ViewOwnProfile.php';
                            } else {
                                echo '../View Profile/ViewProfile.php?user_id='.$row['UserID'];
                            }
                        ?>" class="ho-username-link">
                            <?php echo htmlspecialchars($row['username']); ?>
                        </a>
                    </strong>
                </header>

                <!-- Post Image -->
                <div class="ho-post-image" onclick="showOverlay(<?php echo $row['PostID']; ?>)">
                    <?php if (!empty($row['fileImage'])): ?>
                        <img src="../!! Images/<?php echo htmlspecialchars($row['fileImage']); ?>" alt="Post Image">
                    <?php endif; ?>
                </div>

                <!-- Post Actions -->
                <div class="post-actions">
                    <div class="action-buttons">
                        <!-- Like Form -->
                        <form action="../Homepage/Backend/LikePost.php" method="POST" class="like-form">
                            <input type="hidden" name="postId" value="<?php echo $row['PostID']; ?>">
                            <button type="submit" class="action-btn like-btn">
                                <i class="<?php echo $isLiked ? 'fas fa-heart' : 'far fa-heart'; ?>"></i>
                            </button>
                        </form>
                        
                        <!-- Comment Button - Update onclick -->
                        <button class="action-btn comment-btn" onclick="showOverlay(<?php echo $row['PostID']; ?>); return false;">
                            <i class="far fa-comment"></i>
                        </button>
                        
                        <!-- Report Button -->
                        <button class="action-btn report-btn" onclick="showReportOverlay(<?php echo $row['PostID']; ?>); return false;">
                            <i class="far fa-flag"></i>
                        </button>
                    </div>
                </div>

                <!-- Likes Count -->
                <div class="likes-count">
                    <?php echo getLikesCount($conn, $row['PostID']); ?> likes
                </div>

                <!-- Caption -->
                <div class="post-caption">
                    <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                    <span><?php echo htmlspecialchars($row['captionText']); ?></span>
                </div>

                <!-- View Comments Link -->
                <div class="view-comments">
                    <?php $comments = getComments($conn, $row['PostID']); ?>
                    <?php if (!empty($comments)): ?>
                        <a href="#" class="view-comments-link" onclick="showOverlay(<?php echo $row['PostID']; ?>); return false;">
                            View all <?php echo count($comments); ?> comments
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Post Date -->
                <div class="post-time">
                    <?php echo date('F j, Y', strtotime($row['date'])); ?>
                </div>

                <!-- Comment Form -->
                <form id="comment-form-<?php echo $row['PostID']; ?>" action="../Homepage/Backend/CommentPost.php" method="POST" class="add-comment-form">
                    <input type="hidden" name="postId" value="<?php echo $row['PostID']; ?>">
                    <input type="text" name="comment" placeholder="Add a comment..." class="comment-input" required>
                    <button type="submit" class="post-comment-btn">Post</button>
                </form>
                </section>
        <?php endforeach; ?>
    </main>
    
    <!-- Overlay HTML -->
    <div id="postOverlay" class="post-overlay">
        <span class="close-overlay" onclick="hideOverlay()">&times;</span>
        <div class="overlay-content">
            <div class="overlay-image">
                <img id="overlayPostImage" src="" alt="Post Image">
            </div>
            <div class="overlay-comments">
                <div class="overlay-header">
                    <div class="profile-pic" id="overlayProfilePic"></div>
                    <strong id="overlayUsername"></strong>
                </div>
                <div class="comments-section" id="overlayComments">
                    <!-- Comments will be loaded here, guh -->
                </div>
                <div class="overlay-caption">
                    <span id="overlayCaption"></span>
                </div>
                <div class="overlay-add-comment">
                    <form id="overlayCommentForm" 
                        action="../Homepage/Backend/CommentPost.php" 
                        method="POST" 
                        class="add-comment-form">
                        <input type="hidden" name="postId" id="overlayPostId" value="">
                        <input type="text" name="comment" placeholder="Add a comment..." class="comment-input" required>
                        <button type="submit" class="post-comment-btn">Post</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Overlay -->
    <div id="reportOverlay" class="report-overlay">
        <div class="report-box">
            <span class="close-report" onclick="hideReportOverlay()">&times;</span>
            <h3>Report Post</h3>
            <form id="reportForm" action="../Homepage/Backend/ReportPost.php" method="POST">
                <input type="hidden" name="postId" id="reportPostId" value="">
                <div class="form-group">
                    <label for="reportReason">Reason for reporting:</label>
                    <textarea id="reportReason" name="reason" required></textarea>
                </div>
                <button type="submit" class="submit-report-btn">Submit Report</button>
            </form>
        </div>
    </div>


    <!-- All the scripts =============================================================== -->
    <script>
    // Store all posts data in JavaScript
    const allPosts = [
        // This functions converts all PHP data into a JavaScript Array for client-side
        // This includes post details such as Post ID, image, username

        <?php foreach ($allPosts as $post): ?>
        {
            PostID: <?php echo $post['PostID']; ?>,
            fileImage: '<?php echo $post['fileImage']; ?>',
            profile_picture: '<?php echo $post['profile_picture']; ?>',
            username: '<?php echo addslashes($post['username']); ?>',
            captionText: '<?php echo addslashes($post['captionText']); ?>',
            comments: [
                <?php 
                $postComments = getComments($conn, $post['PostID']);
                foreach ($postComments as $comment): ?>
                {
                    username: '<?php echo addslashes($comment['username']); ?>',
                    comment: '<?php echo addslashes($comment['comment']); ?>'
                },
                <?php endforeach; ?>
            ]
        },
        <?php endforeach; ?>
    ];

    // Check if user is logged in
    function checkLogin() {
        // Is pretty self explanatory, this check if the user is login or not, if not it brings them to the Login page.
        <?php if (!isset($_SESSION['user_id'])): ?>
            window.location.href = '../Auth/login.php';
            return false;
            // Doesn't do much since we don't really return anything, removed or not it shouldn't affected the code.
            // I just put it there just to end the function.
        <?php else: ?>
            return true;
        <?php endif; ?>
    }

    function showOverlay(postId) {
        // It displays post details in a popup. This shows the image, profile and caption, it also dynamically loads comments.

        if (!checkLogin()) return;
        
        const overlay = document.getElementById('postOverlay');
        const post = allPosts.find(p => p.PostID == postId);
        
        if (!post) {
            console.error('Post not found');
            return;
        }

        // Set post content
        document.getElementById('overlayPostImage').src = `../!! Images/${post.fileImage}`;
        document.getElementById('overlayProfilePic').style.backgroundImage = `url('../!! Images/${post.profile_picture}')`;
        document.getElementById('overlayUsername').textContent = post.username;
        document.getElementById('overlayCaption').textContent = post.captionText;
        document.getElementById('overlayPostId').value = postId;
        
        // Set comments
        const commentsSection = document.getElementById('overlayComments');
        commentsSection.innerHTML = '';
        
        post.comments.forEach(comment => {
            const commentElement = document.createElement('div');
            commentElement.className = 'comment';
            commentElement.innerHTML = `
                <strong>${comment.username}</strong>
                <span>${comment.comment}</span>
            `;
            commentsSection.appendChild(commentElement);
        });

        overlay.style.display = 'flex';
    }

    // Hide Overlay
    function hideOverlay() {
        // Hides the post popup, what else? :shrug:
        document.getElementById('postOverlay').style.display = 'none';
    }

    // Display Report Overlay
    function showReportOverlay(postId) {
        if (!checkLogin()) return;
        
        document.getElementById('reportPostId').value = postId;
        document.getElementById('reportOverlay').style.display = 'flex';
    }

    // Hide Report Overlay
    function hideReportOverlay() {
        document.getElementById('reportOverlay').style.display = 'none';
    }

    // Event Listeiners !!!!!!!!!!!!!!!!!!!!
    document.addEventListener('DOMContentLoaded', function() {
        // with the line of closest function, the code finds the closest parent element with class 'ho-post' and gets the value
        // of its 'postid' data attribute

        // So when a button is clicked, this line looks up through until its parent elements which in this case 'ho-post' is found,
        // it will then retrieve the post's unique ID from its dataset.

        // So what I learnt is that e is a short form of event and prevenDefault() is used to stop from refreshing the page.

        // Report button functionality
        document.querySelectorAll('.report-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!checkLogin()) {
                    e.preventDefault();
                    return;
                }
                const postId = this.closest('.ho-post').dataset.postid;
                showReportOverlay(postId);
            });
        });

        // Comment button functionality
        document.querySelectorAll('.comment-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!checkLogin()) {
                    e.preventDefault();
                    return;
                }
                const postId = this.closest('.ho-post').dataset.postid;
                showOverlay(postId);
            });
        });

        // View comments links
        document.querySelectorAll('.view-comments-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!checkLogin()) {
                    e.preventDefault();
                    return;
                }
                const postId = this.closest('.ho-post').dataset.postid;
                showOverlay(postId);
            });
        });

        // Like button functionality
        document.querySelectorAll('.like-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // This stops the page refresh
                
                if (!checkLogin()) return;
                
                const form = this;
                const postId = form.querySelector('input[name="postId"]').value;
                const heartIcon = form.querySelector('i');
                const likesCountElement = form.closest('.post-actions').nextElementSibling;
                
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'text/plain'
                    }
                })
                .then(response => response.text())
                .then(data => {
                    const [status, count] = data.split(':');
                    
                    if (status === 'liked' || status === 'unliked') {
                        // Update heart icon
                        heartIcon.className = status === 'liked' ? 'fas fa-heart' : 'far fa-heart';
                        
                        // Update likes count display
                        if (likesCountElement && count) {
                            likesCountElement.textContent = count + ' likes';
                        }
                    } else if (status === 'error') {
                        console.error('Like error:', count || 'Unknown error');
                        // Revert heart icon if there was an error
                        heartIcon.className = heartIcon.classList.contains('far') 
                            ? 'fas fa-heart' 
                            : 'far fa-heart';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revert heart icon on error
                    heartIcon.className = heartIcon.classList.contains('far') 
                        ? 'fas fa-heart' 
                        : 'far fa-heart';
                });
            });
        });

        // Overlay comment form
        document.getElementById('overlayCommentForm').addEventListener('submit', function(e) {
            if (!checkLogin()) {
                e.preventDefault();
                return;
            }
        });

        // Report form
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            if (!checkLogin()) {
                e.preventDefault();
                return;
            }
        });

        // Close when clicking outside overlay
        document.getElementById('postOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                hideOverlay();
            }
        });

        // Close report overlay when clicking outside
        document.getElementById('reportOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                hideReportOverlay();
            }
        });

        // Handle URL parameters for report status
        const urlParams = new URLSearchParams(window.location.search);
        const reportStatus = urlParams.get('report');
        
        if (reportStatus) {
            const messages = {
                'success': 'Report submitted successfully!',
                'invalid_method': 'Invalid request method.',
                'not_logged_in': 'You must be logged in to report.',
                'empty_reason': 'Please provide a reason for reporting.',
                'already_reported': 'You already reported the post!',
                'db_error': 'Database error occurred.'
            };
            
            if (messages[reportStatus]) {
                alert(messages[reportStatus]);
                // Clear the parameter from URL
                history.replaceState({}, document.title, window.location.pathname);
            }
        }

        const commentError = urlParams.get('comment_error');
        if (commentError) {
            const errorMessages = {
                'inappropriate': 'Inappropriate language is not allowed!',
                'empty': 'Comment cannot be empty!',
                'not_logged_in': 'You must be logged in to comment!',
                'invalid_method': 'Invalid request!'
            };
            alert(errorMessages[commentError] || 'Error submitting comment');
            history.replaceState({}, document.title, window.location.pathname);
        }

        // Optional: Success message
        if (urlParams.get('comment_success') === 'true') {
            // You could refresh comments here if needed
            history.replaceState({}, document.title, window.location.pathname);
        }
    });
    </script>
</body>
</html>