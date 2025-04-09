<?php

include "../!! General/header.php";
include "../!! General/database.php"; // Database connection

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
if (!$query) {
    echo "Error preparing query: " . $conn->error;
    exit();
}
$query->bind_param("i", $user_id);
if (!$query->execute()) {
    echo "Error executing query: " . $conn->error;
    exit();
}
$result = $query->get_result();
$users = $result->fetch_assoc();

if (!$users) {
    echo "User not found.";
    exit();
}

// Check if user is blocked
$is_blocked = false;
if (isset($_SESSION['user_id'])) {
    $block_check = $conn->prepare("SELECT * FROM block WHERE UserID = ? AND BlockedUserID = ?");
    $block_check->bind_param("ii", $_SESSION['user_id'], $user_id);
    $block_check->execute();
    $block_result = $block_check->get_result();
    $is_blocked = $block_result->num_rows > 0;
}

// Get followers count
$followers_query = $conn->prepare("SELECT COUNT(*) AS count FROM follow WHERE FollowingID = ? AND FollowStatus = 'approved'");
$followers_query->bind_param("i", $user_id);
$followers_query->execute();
$followers_result = $followers_query->get_result();
$followers_count = $is_blocked ? 0 : $followers_result->fetch_assoc()['count'];

// Get following count
$following_query = $conn->prepare("SELECT COUNT(*) AS count FROM follow WHERE FollowerID = ? AND FollowStatus = 'approved'");
if (!$following_query) {
    echo "Error preparing query: " . $conn->error;
    exit();
}
$following_query->bind_param("i", $user_id);
if (!$following_query->execute()) {
    echo "Error executing query: " . $conn->error;
    exit();
}
$following_result = $following_query->get_result();
$following_count = $is_blocked ? 0 : $following_result->fetch_assoc()['count'];

// Get posts count
$posts_query = $conn->prepare("SELECT COUNT(*) AS count FROM post WHERE UserID = ?");
$posts_query->bind_param("i", $user_id);
$posts_query->execute();
$posts_result = $posts_query->get_result();
$posts_count = $is_blocked ? 0 : $posts_result->fetch_assoc()['count'];

// Check user whether is private and access permission
$is_private = $users['manage_visibility'] === 'private';
$is_following = false;

if ($is_private && isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user_id) {
    // Check whether has been followed
    $follow_check = $conn->prepare("SELECT FollowStatus FROM follow WHERE FollowerID = ? AND FollowingID = ?");
    $follow_check->bind_param("ii", $_SESSION['user_id'], $user_id);
    $follow_check->execute();
    $follow_result = $follow_check->get_result();
    $follow_status = $follow_result->fetch_assoc();
    
    $is_following = ($follow_status && $follow_status['FollowStatus'] === 'approved');
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="ViewProfile1.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <title>View Profile</title>
    <script src="../!! General/Countdown_Timer.js"></script>


</head>
<body>
    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="ProfilePic">
                <?php
                        if (!empty($users['profile_picture'])) {
                            $profile_picture = '../!! Images/' . htmlspecialchars($users['profile_picture']);
                                } else {
                                   $profile_picture = '../!! images/DefaultUser.jpg'; // Set default profile picture
                                }
                        ?>
                        <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
                            </div>       
                <div class="profile-details">
                <div class="top-section">
                <h2 class="profile-name"><?php echo $users['username']; ?></h2> 
                <?php
                // 检查当前登录用户是否已关注该用户
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user_id) {
                    if ($is_blocked) {
                        echo '<button class="follow-btn unblock" data-user-id="' . $user_id . '">Unblock</button>';
                    } else {
                        $check_follow = $conn->prepare("SELECT FollowStatus FROM follow WHERE FollowerID = ? AND FollowingID = ?");
                        $check_follow->bind_param("ii", $_SESSION['user_id'], $user_id);
                        $check_follow->execute();
                        $follow_result = $check_follow->get_result();
                        $follow_status = $follow_result->fetch_assoc();
                        
                        $button_text = "Follow";
                        $button_class = "";
                        
                        if ($follow_status) {
                            if ($follow_status['FollowStatus'] === 'approved') {
                                $button_text = "Following";
                                $button_class = "following";
                            } else if ($follow_status['FollowStatus'] === 'pending') {
                                $button_text = "Requested";
                                $button_class = "requested";
                            }
                        }
                        
                        echo '<button class="follow-btn ' . $button_class . '" data-user-id="' . $user_id . '">' . $button_text . '</button>';
                    }
                }
                ?>
    
                <!-- Report Icon -->
                <span class="material-symbols-outlined" onclick="toggleReportMenu(event)">
                    menu
                </span>

                <!-- Report Menu Overlay -->
                <div class="report-overlay" id="reportOverlay" onclick="toggleReportMenu(event)"></div>

                <!-- Report Menu -->
                <div class="report-menu" id="reportMenu">
                    <ul>
                        <li class="red" onclick="handleBlock(<?php echo $user_id; ?>)">Block</li>
                        <li class="red" onclick="handleReport(<?php echo $user_id; ?>)">Report</li>
                        <li onclick="toggleReportMenu(event)">Cancel</li>
                    </ul>
                </div>
            </div>

                    <div class="profile-stats">
                    <button class="Post"><?php echo $posts_count; ?> Posts</button>
                    <button class="follower-btn <?php echo $is_blocked ? 'disabled' : ''; ?>" <?php echo $is_blocked ? 'disabled' : ''; ?>><?php echo $followers_count; ?> Followers</button>
                    <button class="following-btn <?php echo $is_blocked ? 'disabled' : ''; ?>" <?php echo $is_blocked ? 'disabled' : ''; ?>><?php echo $following_count; ?> Following</button>
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
    <?php
    // 如果是私密账号且未关注（或未登录），显示私密提示
    if ($is_blocked) {
        ?>
        <div class="blocked-account">
            <p class="blocked-text">This account has been blocked</p>
        </div>
        <?php
    } else if ($is_private && !$is_following && (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id)) {
        ?>
        <div class="private-account">
            <img src="../!! images/PrivateUser.png" alt="Private Account">
            <p class="private-text">This account is private</p>
            <p class="private-subtext">Follow this account to see their photos and videos.</p>
        </div>
        <?php
    } else {
        // 显示帖子部分
        $posts_query = $conn->prepare("SELECT * FROM post WHERE UserID = ?");
        $posts_query->bind_param("i", $user_id);
        $posts_query->execute();
        $posts_result = $posts_query->get_result();

        if ($posts_result->num_rows > 0) {
            echo '<div class="post-grid">';
            while ($post = $posts_result->fetch_assoc()) {
                echo '<div class="post-item" onclick="fetchPostDetails('.htmlspecialchars($post['PostID']).')">
                        <img src="../!! Images/'.htmlspecialchars($post['fileImage']).'" alt="Post">
                      </div>';
            }
            echo '</div>';
        } else {
            echo '<div class="no-posts">
                <img src="../!! Images/DefaultPost.png" alt="No posts yet">
                <p class="no-post-text">No posts yet</p>
            </div>';
        }
    }
    ?>
    </div>

    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modal-title">Followers</h2>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search users...">
            </div>
            <ul id="userList" class="user-list">
                <!-- User items will be populated dynamically -->
            </ul>
        </div>
    </div>

    <div class="dialog-overlay" id="unfollowOverlay"></div>
    <div class="confirm-dialog" id="unfollowDialog">
        <h3>Are you sure to Unfollow？</h3>
        <div class="buttons">
            <button class="cancel-btn" onclick="cancelUnfollow()">Cancel</button>
            <button class="confirm-btn" onclick="confirmUnfollow()">Confirm</button>
        </div>
    </div>

    <div id="overlay1" style="visibility: hidden; opacity: 0;">
        <div class="overlayBackground">
            <div class="overlayContent1">
                <div class="imgContainer">
                    <img id="overlay-image" src="" alt="Post Image">
                </div> 
                <div class="post-details">
                    <button class="xButton1">&times;</button>
                    <h2 class="post-title" id="overlay-title"></h2>
                    <p class="post-text" id="overlay-caption"></p>
                    <small id="overlay-date" class="dateCreated"></small>

                    <div class="commentList">
                        <!-- Comments will be loaded dynamically -->
                    </div>

                    <!-- Comment Section -->
                    <div class="commentSection">
                        <form id="comment-form" method="POST" autocomplete="off">
                            <input type="hidden" name="post_id" id="overlay-post-id">
                            <input type="text" class="commentInput" name="comment" placeholder="Write a comment..." required>
                            <button type="submit" class="commentButton">Submit</button>
                        </form>
                    </div>

                    <div class="post-footer">
                        <div class="post-actions">
                            <div class="heart-bg">
                                <span class="heart-icon" data-post-id="" onclick="handleLike(this)"></span>
                            </div>
                             &nbsp;
                            <button id="report-btn" class="ReportButton">🚩</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Post Modal -->
    <div id="reportPostModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReportPostModal()">&times;</span>
            <h2>Report Post</h2>
            <form id="reportPostForm" method="POST">
                <input type="hidden" id="reportPostId" name="post_id" value="">
                <textarea id="reportPostReason" name="reason" placeholder="Enter reason for report..." required></textarea>
                <button type="submit">Submit Report</button>
            </form>
        </div>
    </div>

    <!-- Report User Modal -->
    <div id="reportUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReportUserModal()">&times;</span>
            <h2>Report User</h2>
            <form id="reportUserForm" method="POST">
                <input type="hidden" id="reportUserId" name="user_id" value="">
                <textarea id="reportUserReason" name="reason" placeholder="Enter reason for report..." required></textarea>
                <button type="submit">Submit Report</button>
            </form>
        </div>
    </div>

    <!-- Overlay for modals -->
    <div id="modalOverlay" class="overlay" onclick="closeReportPostModal(); closeReportUserModal();"></div>

    <script src="View_Post.js?v=<?php echo time(); ?>"></script>


    <style>
        /* Report Modal styles */


.modal {
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
    width: 80%;
    max-width: 500px;
}

.modal-content {
    position: relative;
}

.close {
    position: absolute;
    right: 10px;
    top: 5px;
    font-size: 24px;
    cursor: pointer;
}

#reportPostForm, #reportUserForm {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

#reportPostForm textarea, #reportUserForm textarea {
    width: 100%;
    height: 100px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
}

#reportPostForm button, #reportUserForm button {
    padding: 10px 20px;
    background: #ff4d4d;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    font-size: 16px;
    transition: background-color 0.3s;
}

#reportPostForm button:hover, #reportUserForm button:hover {
    background: #cc0000;
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


    .disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Overlay Items */
    #overlay1 {
        margin-top: 0px;
        transition: opacity 0.8s ease, visibility 0.8s;
        position: fixed;
        visibility: hidden;
        opacity: 0;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .overlayBackground {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        max-width: 1500px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    }

    .overlayContent1 {
        display: flex;
        gap: 10px;
    }

    .imgContainer {
        width: 500px;
        height: 400px;
        background-color: #ddd;
        border-radius: 20px;
    }

    .imgContainer img {
        width: 100%;
        height: 100%;
        border-radius: 20px;
        object-fit: cover;
    }

    .post-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        width: 400px;
        font-family: "DM Sans", serif;
    }

    .post-title {
        font-size: 20px;
        font-weight: bold;
    }

    .post-text {
        margin-top: 10px;
    }

    .xButton1 {
        position: absolute;
        top: 10px;
        right: 15px;
        cursor: pointer;
        font-size: 22px;
        color: #222;
        text-decoration: none;
        background: none;
        border: none;
    }

    .xButton1:hover {
        transition: .3s ease;
        color: #f00;
        transform: scale(1.08);
    }

    .post-actions {
        font-size: 20px;
        display: flex;
        gap: 10px;
    }

    .post-footer {
        display: flex;
        align-items: center;
        margin-top: 10px;
        font-size: 14px;
        gap: 232px;
    }

    .dateCreated {
        font-size: 14px;
        color: #666;
        font-weight: bold;
        font-family: "DM Sans", serif;
        margin-top: 10px;
    }

    /* Comment Styling */
    .commentList {
        background: #f4f4f4;
        padding: 10px;
        margin-top: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        border: 1px solid #ddd;
        height: 200px;
        max-height: 200px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .comment-item {
        padding: 8px;
        border-bottom: 1px solid #ddd;
        margin-bottom: 8px;
    }

    .comment-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .comment-details {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .comment-text {
        margin: 0;
        word-wrap: break-word;
    }

    .noComments {
        text-align: center;
        color: #777;
        font-style: italic;
        padding: 20px 0;
    }

    .error-message {
        color: red;
        margin: 10px 0;
        font-weight: bold;
        text-align: center;
    }

    .commentSection {
        margin-top: 20px;
    }

    .commentInput {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .commentButton {
        width: 100%;
        padding: 8px 12px;
        background: #222;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
    }

    .commentButton:hover {
        background: #444;
    }

    .commentList::-webkit-scrollbar {
        width: 2px;
    }

    .commentItem p {
        margin: 5px 0;
    }

    .commentItem small {
        display: block;
        color: #777;
        margin-top: 5px;
        font-size: 12px;
    }

    /* Like Button Styling */
    .heart-icon {
        height: 100px;
        width: 100px;
        background: url("../!! Images/heart.png");
        background-position: left;
        cursor: pointer;
        position: absolute;
        
    }

    .heart-bg {
        background: rgba(255, 192, 200, 0);
        border-radius: 50%;
        height: 30px;
        width: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 100ms ease;
    }

    .heart-bg:hover {
        background: rgba(255, 192, 200, 0.7);
        height: 30px;
        width: 30px;
    }

    .heart-icon.liked {
        animation: like-anim 0.7s steps(28) forwards;
    }

    @keyframes like-anim {
        to {
            background-position: right;
        }
    }

 

    .error-message {
        color: red; 
  margin-bottom: 10px;
  font-weight: bold;
  margin-top: -30px;
  text-transform: capitalize;
    }
    </style>

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
            if (event.target.closest(".post-item")) {
                event.preventDefault();
                const postId = event.target.closest(".post-item").getAttribute("onclick").match(/\d+/)[0];
                fetchPostDetails(postId);
                overlay.style.visibility = "visible";
                overlay.style.opacity = "1";
                window.history.pushState({}, "", `ViewProfile.php?user_id=<?php echo $user_id; ?>&post_id=${postId}`);
            }
        });

        closeButton.addEventListener("click", function (event) {
            event.preventDefault();
            overlay.style.opacity = "0";
            setTimeout(() => {
                overlay.style.visibility = "hidden";
                window.history.pushState({}, "", `ViewProfile.php?user_id=<?php echo $user_id; ?>`);
            }, 500);
        });

        overlay.addEventListener("click", function (event) {
            if (event.target === overlay) {
                overlay.style.opacity = "0";
                setTimeout(() => {
                    overlay.style.visibility = "hidden";
                    window.history.pushState({}, "", `ViewProfile.php?user_id=<?php echo $user_id; ?>`);
                }, 500);
            }
        });

        // Like functionality
        window.handleLike = async function(button) {
            const postId = document.getElementById("overlay-post-id").value;
            if (!postId) {
                console.error("Error: Could not find post ID");
                return;
            }

            try {
                const formData = new FormData();
                formData.append('post_id', postId);

                const response = await fetch('../Explore/handle_like.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                console.log('Like response:', data);
                
                if (data.success) {
                    // Update the heart icon appearance
                    if (data.liked) {
                        button.classList.add('liked');
                    } else {
                        button.classList.remove('liked');
                    }
                    
                    // Refresh notifications if needed
                    if (typeof fetchLikes === 'function') {
                        fetchLikes();
                    }
                } else {
                    console.error("Failed to update like:", data.message);
                }
            } catch (error) {
                console.error('Error handling like:', error);
            }
        }

        // Comment functionality
        function toggleCommentSection(button) {
            const commentSection = document.querySelector('.commentSection');
            commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
        }

        // Handle comment submission
        document.getElementById('comment-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const postId = document.getElementById("overlay-post-id").value;
            const commentInput = this.querySelector('.commentInput');
            const comment = commentInput.value.trim();
            
            if (!comment) return;

            try {
                // First check for blacklisted words
                const blacklistResponse = await fetch('../View_Post/check_blacklist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `text=${encodeURIComponent(comment)}`
                });

                const blacklistData = await blacklistResponse.json();
                
                if (blacklistData.hasBlacklistedWord) {
                    // Remove any existing error message
                    const existingError = this.querySelector('.error-message');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    // Add new error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.textContent = 'Your comment contains inappropriate language and cannot be posted.';
                    this.insertBefore(errorDiv, commentInput);
                    return;
                }

                // If no blacklisted words, proceed with comment submission
                const formData = new FormData();
                formData.append('postId', postId);
                formData.append('comment', comment);

                const response = await fetch('../Profile Backend/add_comments.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Clear input
                    commentInput.value = '';
                    
                    // Remove any existing error message
                    const existingError = this.querySelector('.error-message');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    // Refresh comments
                    fetchPostComments(postId);
                } else {
                    throw new Error(data.message || "Failed to add comment");
                }
            } catch (error) {
                console.error('Error adding comment:', error);
                if (error.message.includes('inappropriate')) {
                    alert('Comment contains inappropriate language!');
                } else {
                    alert('Failed to add comment: ' + error.message);
                }
            }
        });

        // Update fetchPostDetails to use Profile Backend
        function fetchPostDetails(postId) {
            fetch(`../Profile Backend/fetch_post_details.php?post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("overlay-image").src = "../!! Images/" + data.post.fileImage;
                        document.getElementById("overlay-title").textContent = data.post.titleText;
                        document.getElementById("overlay-caption").textContent = data.post.captionText;
                        document.getElementById("overlay-date").textContent = "Posted On: " + data.post.date;
                        document.getElementById("overlay-post-id").value = postId;

                        // Set post ID and update like status on the heart icon
                        const heartIcon = document.querySelector('.heart-icon');
                        if (heartIcon) {
                            heartIcon.setAttribute('data-post-id', postId);
                            // Update like status based on database value
                            if (data.is_liked) {
                                heartIcon.classList.add('liked');
                            } else {
                                heartIcon.classList.remove('liked');
                            }
                        }

                        // Fetch comments
                        fetchPostComments(postId);

                        const overlay = document.getElementById("overlay1");
                        overlay.style.visibility = "visible";
                        overlay.style.opacity = "1";
                    } else {
                        console.error("Failed to fetch post details:", data.message);
                    }
                })
                .catch(error => {
                    console.error("Error fetching post details:", error);
                });
        }

        // Add fetchPostComments function with error handling
        function fetchPostComments(postId) {
            const commentList = document.querySelector('.commentList');
            commentList.innerHTML = '<p>Loading comments...</p>';

            fetch(`../Profile Backend/fetch_comments.php?post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.comments && data.comments.length > 0) {
                        commentList.innerHTML = data.comments.map(comment => `
                            <div class="comment-item">
                                <div class="comment-details">
                                    <strong>${comment.username}</strong>
                                    <p class="comment-text">${comment.comment}</p>
                                    <small>${new Date(comment.date).toLocaleString()}</small>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        commentList.innerHTML = '<p class="noComments">No comments yet</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching comments:', error);
                    commentList.innerHTML = '<p>Failed to load comments. Please try again.</p>';
                });
        }
    });

    // 保留原有的 toggleReportMenu 和其他功能
    function toggleReportMenu(event) {
        event.stopPropagation(); // 阻止事件冒泡
        const reportMenu = document.getElementById("reportMenu");
        const reportOverlay = document.getElementById("reportOverlay");
        
        if (reportMenu.style.display === "block") {
            reportMenu.style.display = "none";
            reportOverlay.style.display = "none";
        } else {
            reportMenu.style.display = "block";
            reportOverlay.style.display = "block";
        }
    }

    function handleBlock(userId) {
        if (confirm('Are you sure you want to block this user? This is a two-way block - you won\'t see each other\'s content.')) {
            const formData = new FormData();
            formData.append('blocked_user_id', userId);

            fetch('../Profile Backend/block_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User has been blocked. This is a two-way block - neither of you will see each other\'s content.');
                    // Redirect to homepage
                    window.location.href = '../Homepage/Homepage.php';
                } else {
                    alert(data.message || 'Failed to block user');
                    // If user is already blocked, refresh the page
                    if (data.message === 'This user is already blocked') {
                        location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Operation failed, please try again');
            });
        }
        toggleReportMenu(event);
    }

    function handleReport(userId) {
        openReportUserModal(userId);
        toggleReportMenu(event);
    }

    // 点击页面任何地方关闭菜单
    document.addEventListener('click', function(event) {
        const reportMenu = document.getElementById("reportMenu");
        const reportOverlay = document.getElementById("reportOverlay");
        const menuIcon = document.querySelector(".material-symbols-outlined");
        
        if (!reportMenu.contains(event.target) && event.target !== menuIcon) {
            reportMenu.style.display = "none";
            reportOverlay.style.display = "none";
        }
    });

    // 添加取消关注相关的变量和函数
    let unfollowUserId = null;
    const unfollowOverlay = document.getElementById('unfollowOverlay');
    const unfollowDialog = document.getElementById('unfollowDialog');

    function showUnfollowDialog(userId) {
        unfollowUserId = userId;
        unfollowOverlay.style.display = 'block';
        unfollowDialog.style.display = 'block';
    }

    function cancelUnfollow() {
        unfollowOverlay.style.display = 'none';
        unfollowDialog.style.display = 'none';
        unfollowUserId = null;
    }

    async function confirmUnfollow() {
        if (!unfollowUserId) return;

        try {
            const formData = new FormData();
            formData.append('following_id', unfollowUserId);
            formData.append('action', 'unfollow');

            console.log('Sending unfollow request:', {
                following_id: unfollowUserId,
                action: 'unfollow'
            });

            const response = await fetch('../Profile Backend/follow.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Unfollow response:', data);

            if (data.success) {
                // 更新按钮状态
                const followBtn = document.querySelector('.follow-btn');
                if (followBtn) {
                    followBtn.textContent = 'Follow';
                    followBtn.classList.remove('following');
                    followBtn.classList.remove('requested');
                }

                // 更新关注数量
                const followingBtn = document.querySelector('.following-btn');
                if (followingBtn) {
                    const currentCount = parseInt(followingBtn.textContent);
                    followingBtn.textContent = `${currentCount - 1} Following`;
                }

                // 如果模态框是打开的，更新用户列表
                const modal = document.getElementById('modal');
                if (modal.style.display === 'block') {
                    const userList = document.getElementById('userList');
                    const users = JSON.parse(userList.dataset.users || '[]');
                    const updatedUsers = users.filter(user => user.id != unfollowUserId);
                    renderUserList(updatedUsers);
                }

                alert('Successfully unfollowed');
            } else {
                alert(data.message || 'Failed to unfollow');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to unfollow. Please try again later.');
        } finally {
            cancelUnfollow();
        }
    }

    // 关注按钮事件处理
    const followBtn = document.querySelector('.follow-btn');
    if (followBtn) {
        followBtn.addEventListener('click', async function() {
            try {
                const userId = this.dataset.userId;
                const isPrivate = this.dataset.isPrivate === 'true';
                
                if (this.classList.contains('following') || this.classList.contains('requested')) {
                    // 处理取消关注
                    if (confirm('You sure you want to unfollow this user？')) {
                        const formData = new FormData();
                        formData.append('following_id', userId);
                        formData.append('action', 'unfollow');

                        const response = await fetch('../Profile Backend/follow.php', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.textContent = isPrivate ? 'Request' : 'Follow';
                            this.classList.remove('following', 'requested');
                            
                            // 如果是私密账号，取消关注后需要刷新页面显示私密内容提示
                            if (isPrivate) {
                                location.reload();
                            }
                        } else {
                            throw new Error(data.message || 'Follow operation failed');
                        }
                    }
                } else {
                    // 处理关注请求
                    const formData = new FormData();
                    formData.append('following_id', userId);
                    formData.append('action', 'follow');

                    const response = await fetch('../Profile Backend/follow.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        if (data.is_private) {
                            this.textContent = 'Requested';
                            this.classList.add('requested');
                        } else {
                            this.textContent = 'Following';
                            this.classList.add('following');
                            // 如果不是私密账号，直接刷新页面显示内容
                            location.reload();
                        }
                    } else {
                        throw new Error(data.message || 'Follow operation failed');
                    }
                }
            } catch (error) {
                console.error('Follow operation failed:', error);
                alert(error.message || 'Follow operation failed');
            }
        });
    }

    // 修改DOMContentLoaded事件处理中的关注按钮监听
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById('modal');
        const modalTitle = document.getElementById('modal-title');
        const userList = document.getElementById('userList');
        const searchInput = document.getElementById('search-input');
        const closeBtn = document.querySelector('.close');
        const followerBtn = document.querySelector('.follower-btn');
        const followingBtn = document.querySelector('.following-btn');
        const currentUserId = <?php echo json_encode($user_id); ?>;

        // 关闭模态框的函数
        function closeModal() {
            console.log('Closing modal');
            modal.style.display = 'none';
            if (searchInput) {
                searchInput.value = '';
            }
        }

        // 打开模态框的函数
        function openModal(title, users) {
            console.log('Opening modal with:', { title, users });
            modalTitle.textContent = title;
            renderUserList(users);
            modal.style.display = 'block';
        }

        // 渲染用户列表
        function renderUserList(users) {
            console.log('Rendering users:', users);
            
            if (!userList) {
                console.error('UserList element not found');
                return;
            }
            
            if (!users || users.length === 0) {
                console.log('No users to display');
                userList.innerHTML = '<p class="no-users-message">No users found</p>';
                return;
            }

            userList.dataset.users = JSON.stringify(users);
            
            const userHTML = users.map(user => {
                console.log('Processing user:', user);
                return `
                    <li>
                        <div class="user-details">
                            <a href="ViewProfile.php?user_id=${user.id}" class="profile-link">
                                <img src="${user.profile_picture || '../!! Images/DefaultUser.jpg'}" alt="${user.username}" onerror="this.src='../!! Images/DefaultUser.jpg'">
                            </a>
                            <div>
                                <a href="ViewProfile.php?user_id=${user.id}" class="profile-link">
                                    <strong>${user.username}</strong>
                                </a>
                            </div>
                        </div>
                    </li>
                `;
            }).join('');
            
            console.log('Generated HTML:', userHTML);
            userList.innerHTML = userHTML;
        }

        // 搜索功能
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const users = JSON.parse(userList.dataset.users || '[]');
                const filtered = users.filter(user => 
                    user.username.toLowerCase().includes(query)
                );
                renderUserList(filtered);
            });
        }

        // 关闭按钮事件
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        // 点击模态框外部关闭
        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Followers按钮点击事件
        if (followerBtn) {
            followerBtn.addEventListener('click', async () => {
                if (<?php echo $is_blocked ? 'true' : 'false'; ?>) {
                    return; // 直接返回，不显示任何提示
                }
                try {
                    console.log('Fetching followers...');
                    const response = await fetch('../Profile Backend/get_follower.php?user_id=' + currentUserId);
                    console.log('Follower response:', response);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    console.log('Follower data:', data);
                    
                    if (data.success) {
                        console.log('Opening modal with followers:', data.followers);
                        openModal('Followers', data.followers || []);
                    } else {
                        throw new Error(data.message || 'Failed to fetch followers');
                    }
                } catch (error) {
                    console.error('Error fetching followers:', error);
                    alert('Failed to load followers. Please try again later.');
                }
            });
        }

        // Following按钮点击事件
        if (followingBtn) {
            followingBtn.addEventListener('click', async () => {
                if (<?php echo $is_blocked ? 'true' : 'false'; ?>) {
                    return; // 直接返回，不显示任何提示
                }
                try {
                    console.log('Fetching following...');
                    const response = await fetch('../Profile Backend/get_following.php?user_id=' + currentUserId);
                    console.log('Following response:', response);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    console.log('Following data:', data);
                    
                    if (data.success) {
                        console.log('Opening modal with following:', data.following);
                        openModal('Following', data.following || []);
                    } else {
                        throw new Error(data.message || 'Failed to fetch following');
                    }
                } catch (error) {
                    console.error('Error fetching following:', error);
                    alert('Failed to load following list. Please try again later.');
                }
            });
        }
    });

    // Add unblock functionality
    document.addEventListener('DOMContentLoaded', function() {
        const unblockBtn = document.querySelector('.follow-btn.unblock');
        if (unblockBtn) {
            unblockBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to unblock this user? This will remove the two-way block.')) {
                    const userId = this.dataset.userId;
                    const formData = new FormData();
                    formData.append('blocked_user_id', userId);

                    fetch('../Profile Backend/unblock_user.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('User has been unblocked. The two-way block has been removed.');
                            location.reload();
                        } else {
                            alert(data.message || 'Failed to unblock user');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Operation failed, please try again');
                    });
                }
            });
        }
    });

// Report Post Functions
function openReportPostModal(postId) {
    document.getElementById('reportPostId').value = postId;
    document.getElementById('reportPostModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}

function closeReportPostModal() {
    document.getElementById('reportPostModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
}

// Report User Functions
function openReportUserModal(userId) {
    document.getElementById('reportUserId').value = userId;
    document.getElementById('reportUserModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}

function closeReportUserModal() {
    document.getElementById('reportUserModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
}

// Handle report post form submission
document.getElementById('reportPostForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    
    fetch('../View_Post/report_post.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Post reported successfully!');
            closeReportPostModal();
        } else {
            alert(data.message || 'Failed to report post');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting report.');
    });
});

// Handle report user form submission
document.getElementById('reportUserForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    
    fetch('../Profile Backend/report_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User reported successfully!');
            closeReportUserModal();
        } else {
            alert(data.message || 'Failed to report user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting report.');
    });
});

// Add event listener for the report button in the post overlay
document.addEventListener('click', function(e) {
    if (e.target.closest('#report-btn')) {
        const postId = document.getElementById('overlay-post-id').value;
        if (postId) {
            openReportPostModal(postId);
        }
    }
});
    </script>
 
</body>
</html>