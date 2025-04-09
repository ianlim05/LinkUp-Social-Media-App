<?php
session_start();

// Check if the user is logged in
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
            </style>
        </head>
        <body>
            <div class="modal">
                <div class="icon">🔒</div>
                <h2>Login Required</h2>
                <p>To view and manage your profile settings, please log in to your account.</p>
                <button class="login-btn" onclick="window.location.href='../Auth/login.php'">Go to Login</button>
                <button class="cancel-btn" onclick="window.location.href='../Homepage/Homepage.php'">Cancel</button>
            </div>
        </body>
        </html>
        <?php
        exit();
    }

include "../!! General/header.php";
include "../!! General/database.php"; // Database connection

// Always use the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch user details
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
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

// Get followers count
$followers_query = $conn->prepare("SELECT COUNT(*) AS count FROM follow WHERE FollowingID = ? AND FollowStatus = 'approved'");
$followers_query->bind_param("i", $user_id);
$followers_query->execute();
$followers_result = $followers_query->get_result();
$followers_count = $followers_result->fetch_assoc()['count'];

// Get following count
$following_query = $conn->prepare("SELECT COUNT(*) AS count FROM follow WHERE FollowerID = ? AND FollowStatus = 'approved'");
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="ViewProfile.css">
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
                                   $profile_picture = '../!! Images/DefaultUser.jpg'; // Set default profile picture
                                }
                        ?>
                        <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
                            </div>       
                <div class="profile-details">
                    <div class="top-section">
                    <h2 class="profile-name"><?php echo $users['username']; ?></h2> 
                    </div>
                    <div class="profile-stats">
                    <button class="Post"><?php echo $posts_count; ?> Posts</button>
                    <button class="follower-btn"><?php echo $followers_count; ?> Followers</button>
                    <button class="following-btn"><?php echo $following_count; ?> Following</button>
                    </div>
                    <div class="profile-bio">
                    <p><?php echo nl2br($users['bio']); ?></p>

                    </div>
                    <div class="button-notification-container">
                        <button class="edit-profile" onclick="window.location.href='../Setting/setting.php?category=profile-settings&show=true'">
                            Edit Profile
                        </button>
                        <span class="notification-icon">
                            <span class="material-symbols-outlined">notifications</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>
    
    <div class="post-section">
    <?php
    $posts_query = $conn->prepare("SELECT * FROM post WHERE USERID = ?");
    $posts_query->bind_param("i", $user_id);
    $posts_query->execute();
    $posts_result = $posts_query->get_result();

    if ($posts_result->num_rows > 0) {
        echo '<div class="post-grid">';
        while ($post = $posts_result->fetch_assoc()) {
            echo '<div class="post-item">
                    <a href="../View Post/viewpost.php?post_id='.htmlspecialchars($post['PostID']).'">
                        <img src="../!! images/'.htmlspecialchars($post['fileImage']).'" alt="Post">
                    </a>
                  </div>';
        }
        echo '</div>';
    } else {
        echo '<div class="no-posts">
            <img src="../!! Images/DefaultPost.png" alt="No posts yet">
            <p class="no-post-text">No posts yet</p>
        </div>';
    }
    ?>
    </div>
    <div id="followModal" class="followModal">
        <div class="followModal-content">
            <span class="followClose">&times;</span>
            <h2 id="followModal-title">Followers</h2>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search">
            </div>
            <ul id="userList" class="userList">
                <!-- User items will populate dynamically -->
            </ul>
        </div>
    </div>

    <!-- 添加确认对话框 -->
    <div class="followOverlay" id="followOverlay"></div>
    <div class="confirm-dialog" id="confirmDialog">
        <h3>Are you sure to Unfollow?</h3>
        <div class="buttons">
            <button class="cancel-btn" id="cancelBtn">Cancel</button>
            <button class="confirm-btn" id="confirmBtn">Confirm</button>
        </div>
    </div>

    <div id="overlay1" style="visibility: hidden; opacity: 0;">
        <div class="overlayBackground">
            <div class="overlayContent1">
                <div class="imgContainer">
                    <img id="overlay-image" src="" alt="Post Image">
                </div> 
                <div class="post-details">
                    <div class="post-header">
                        <div class="header-right">
                            <div class="more-options-container">
                                <span class="material-symbols-outlined more-vert-icon">more_vert</span>
                                <div class="post-options-menu">
                                    <button class="option-btn edit-btn">
                                        <span class="material-symbols-outlined">edit</span>
                                        edit
                                    </button>
                                    <button class="option-btn delete-btn">
                                        <span class="material-symbols-outlined">delete</span>
                                        delete
                                    </button>
                                    <button class="option-btn cancel-btn">
                                        <span class="material-symbols-outlined">close</span>
                                        cancel
                                    </button>
                                </div>
                            </div>
                    <button class="xButton1">&times;</button>
                        </div>
                    </div>
                    <div class="post-content">
                    <h2 class="post-title" id="overlay-title"></h2>
                    <p class="post-text" id="overlay-caption"></p>
                    </div>
                    <div class="edit-form" style="display: none;">
                        <input type="text" id="edit-title" class="edit-input" placeholder="Edit title">
                        <textarea id="edit-caption" class="edit-input" placeholder="Edit caption"></textarea>
                        <div class="edit-buttons">
                            <button class="save-edit-btn">Save</button>
                            <button class="cancel-edit-btn">Cancel</button>
                        </div>
                    </div>
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
                            <button class="comment-button" onclick="toggleCommentSection(this)">
                                💬
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="notificationModal" class="notificationModal">
    <div class="notificationModal-content">
        <span class="notificationClose">&times;</span>
        <h2>Notifications</h2>

        <div class="notification-list">
            <!-- Follow Requests Section -->
            <h3>Follow Requests</h3>
            <ul id="pendingFollowList">
                <!-- Dynamic follow requests will appear here -->
            </ul>
        </div>

        <div class="notification-list">
            <!-- Likes Section -->
            <h3>Likes</h3>
            <ul id="LikesList">
                <!-- Dynamic likes will appear here -->
            </ul>
        </div>

        <div class="notification-list">
            <!-- Comments Section -->
            <h3>Comments</h3>
            <ul id="CommentsList">
                
            </ul>
        </div>

        <div class="notification-list">

        <h3>Warnings</h3>
        <ul id="WarningsList">

        </ul>
    </div>
</div>

<style>
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
    position: relative;
    top: 0;
    right: 0;
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
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 10px 0;
}

.post-footer {
    display: flex;
    align-items: center;
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
.noComments {
    text-align: center;
    color: #777;
    font-style: italic;
    margin-top: 30px;
}

.commentList {
    background: #f4f4f4;
    padding: 10px;
    margin-top: 10px;
    margin-bottom: 100px;
    border-radius: 5px;
    border: 1px solid #050505;
    height: 90px;
    max-height: 90px;
    overflow-y: auto;
    padding-right: 10px;
}

.commentSection button {
    margin-top: 5px;
    padding: 8px;
    background: #222;
    color: white;
    border: none;
    cursor: pointer;
}

.commentInput {
    width: 380px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

.commentButton {
    align-items: center;
    padding: 8px 12px;
    background: #222;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 14px;
    text-align: center;
    width: 400px;
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

/* Updated Like Button Styling */
.heart-bg {
    background: rgba(255, 192, 200, 0);
    border-radius: 50%;
    height: 60px;
    width: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 100ms ease;
    position: relative;
}

.heart-bg:hover {
    background: rgba(255, 192, 200, 0.7);
}

.heart-icon {
    height: 60px;
    width: 60px;
    background: url("../!! Images/heart.png") no-repeat;
    background-size: 2900%;
    background-position: left;
    cursor: pointer;
    position: absolute;
}

.heart-icon.liked {
    animation: like-anim 0.7s steps(28) forwards;
}

@keyframes like-anim {
    to {
        background-position: right;
    }
}

.likes-count {
    font-size: 14px;
    color: #666;
    margin-left: 5px;
}

.comment-button {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 20px;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.commentSection {
    margin-top: 10px;
    padding: 10px;
    border-top: 1px solid #eee;
}

.commentInput {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 5px;
}

.commentButton {
    background-color: #0671B7;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.commentButton:hover {
    background-color: #055694;
}

/* 添加关注者列表的样式 */
.followModal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.followModal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 10px;
    position: relative;
}

.followClose {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.followClose:hover,
.followClose:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.followOverlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
}




.button-notification-container {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 15px;
    margin-top: 10px;
    width: 345px;
}

.notification-icon {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.material-symbols-outlined {
    font-size: 24px;
    color: #0671B7;
    vertical-align: middle;
}

.edit-profile {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 4px;
    border: none;
    background-color: #0671B7;
    color: white;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s;
    width: 220px;
    max-width: none;
}

.edit-profile:hover {
    background-color: #055694;
}

.profile-details {
    display: flex;
    flex-direction: column;
    position: relative;
}

.profile-details > div:last-child {
    display: flex;
    align-items: center;
    margin-top: 10px;
}

/* 通知模态框样式 */
.notificationModal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.notificationModal.show {
    display: flex;
    justify-content: center;
    align-items: center;
}

.notificationModal-content {
    background-color: #fefefe;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 10px;
    position: relative;
    margin: 15vh auto;
    max-height: 70vh;
    overflow-y: auto;
}

.notification-list {
    margin-top: 15px;
    overflow-y: visible;
    max-height: none;
}

.notification-list h3 {
    margin-bottom: 10px;
    color: #0671B7;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
    position: sticky;
    top: 0;
    background-color: #fefefe;
    z-index: 1;
}

#pendingFollowList, #LikesList, #CommentsList {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: none;
    overflow: visible;
}

.notificationClose {
    position: sticky;
    top: 0;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    z-index: 2;
}

.notificationClose:hover,
.notificationClose:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
    margin-top: 15px;
}

.notification-list h3 {
    margin-bottom: 10px;
    color: #0671B7;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}

.notification-list img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
}

#pendingFollowList {
    list-style: none;
    padding: 0;
}

#pendingFollowList li {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #eee;
}

#pendingFollowList li:last-child {
    border-bottom: none;
}

#pendingFollowList .user-info {
    display: flex;
    align-items: center;
    flex: 1;
}

#pendingFollowList .user-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
}

#pendingFollowList .action-buttons {
    display: flex;
    gap: 10px;
}

#pendingFollowList .accept-btn {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

#pendingFollowList .reject-btn {
    background-color: #f44336;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

#LikesList {
list-style: none; /* Removes bullet points */
padding: 0; /* Removes default padding */
margin: 0; /* Removes default margin */

}

/* Main Like Item Container */
.like-item {
display: flex;
align-items: center;
padding: 10px;
margin-bottom: 10px;
border-bottom: 1px solid #ddd;
justify-content: space-between;
}

/* Like Info Container */
#LikesList .like-info {
display: flex;
align-items: center;
flex: 1;
}

/* Profile Picture */
#LikesList .like-item img {
width: 40px;
height: 40px;
border-radius: 50%;
margin-right: 10px;
object-fit: cover;
}
/* Post Image Container */
.post-image-container {
display: flex;
align-items: center;
gap: 10px;
margin-left: 15px;
}

/* Post Image Styling */
.post-image {
width: 100px;
height: 100px;
object-fit: cover;
border-radius: 0 !important; 
border-radius: 8px; 
box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Text Styling */
.post-image-container p {
font-style: italic;
font-size: 1rem;
margin: 0;
}

.post-header {
display: flex;
justify-content: flex-end;
align-items: center;
margin-bottom: 10px;
}

.header-right {
display: flex;
align-items: center;
gap: 10px;
}

.more-options-container {
position: relative;
}

.post-options-menu {
display: none;
position: absolute;
right: 0;
top: 100%;
background-color: white;
border: 1px solid #ddd;
border-radius: 4px;
box-shadow: 0 2px 8px rgba(0,0,0,0.1);
z-index: 1000;
min-width: 150px;
}

.post-options-menu.show {
display: block;
}

.option-btn {
display: flex;
align-items: center;
width: 100%;
padding: 8px 16px;
border: none;
background: none;
cursor: pointer;
font-size: 14px;
color: #333;
transition: background-color 0.2s;
}

.option-btn:hover {
background-color: #f5f5f5;
}

.option-btn .material-symbols-outlined {
margin-right: 8px;
font-size: 20px;
}

.delete-btn {
color: #ff3b30;
}

.delete-btn:hover {
background-color: #fff5f5;
}

.cancel-btn {
border-top: 1px solid #eee;
color: #666;
}

.edit-form {
margin: 10px 0;
}

.edit-input {
width: 100%;
padding: 8px;
margin-bottom: 10px;
border: 1px solid #ddd;
border-radius: 4px;
font-size: 14px;
}

#edit-caption {
min-height: 100px;
resize: vertical;
}

.edit-buttons {
display: flex;
gap: 10px;
}

.save-edit-btn, .cancel-edit-btn {
padding: 8px 16px;
border: none;
border-radius: 4px;
cursor: pointer;
font-size: 14px;
}

.save-edit-btn {
background-color: #0671B7;
color: white;
}

.save-edit-btn:hover {
background-color: #055694;
}

.cancel-edit-btn {
background-color: #f1f1f1;
color: #333;
}

.cancel-edit-btn:hover {
background-color: #e1e1e1;
}

.post-content {
transition: opacity 0.3s ease;
}

.comment-item {
display: flex;
align-items: flex-start;
padding: 10px;
margin-bottom: 10px;
border-bottom: 1px solid #ddd;
justify-content: space-between;
}

.comment-info {
display: flex;
align-items: flex-start;
flex: 1;
}

.comment-info img.profile-pic {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    margin-right: 8px;
    object-fit: cover;
}

.comment-details {
flex: 1;
}

.comment-text {
color: #666;
font-style: italic;
margin: 5px 0;
}

.comment-details small {
color: #999;
font-size: 12px;
}

#CommentsList {
list-style: none;
padding: 0;
margin: 0;
max-height: 300px;
}

#CommentsList p {
margin: 10px 0;
}

.warning-item {
display: flex;
align-items: flex-start;
padding: 10px;
margin-bottom: 10px;
border-bottom: 1px solid #ddd;
background-color: #fff3f3;
border-radius: 5px;
}

.warning-info {
display: flex;
align-items: flex-start;
flex: 1;
}

.warning-details {
flex: 1;
}

.warning-text {
color: #d32f2f;
margin: 5px 0;
}

.warning-icon {
margin-right: 8px;
}

.warning-details small {
color: #999;
font-size: 12px;
display: block;
margin-top: 5px;
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
            if (event.target.closest(".post-item a")) {
                event.preventDefault();
                const postId = event.target.closest("a").getAttribute("href").split("post_id=")[1];
                fetchPostDetails(postId);
                overlay.style.visibility = "visible";
                overlay.style.opacity = "1";
                window.history.pushState({}, "", `ViewOwnProfile.php?post_id=${postId}`);
            }
        });

        closeButton.addEventListener("click", function (event) {
            event.preventDefault();
            overlay.style.opacity = "0";
            setTimeout(() => {
                overlay.style.visibility = "hidden";
                window.history.pushState({}, "", `ViewOwnProfile.php`);
            }, 500);
        });

        overlay.addEventListener("click", function (event) {
            if (event.target === overlay) {
                overlay.style.opacity = "0";
                setTimeout(() => {
                    overlay.style.visibility = "hidden";
                    window.history.pushState({}, "", `ViewOwnProfile.php`);
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
                    
                    // Refresh notifications
                    fetchLikes();
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
                    
                    // Refresh notifications
                    getComments();
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

        // Update fetchPostDetails to include like status
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

        // Update checkLikeStatus to handle the like button appearance
        function checkLikeStatus(postId) {
            fetch(`../Profile Backend/check_like.php?post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    const likeButton = document.querySelector('.heart-icon');
                    if (likeButton) {
                        if (data.isLiked) {
                            likeButton.classList.add('liked');
                        } else {
                            likeButton.classList.remove('liked');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking like status:', error);
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

        // 添加关注者列表相关的代码
        const DOM = {
            modal: document.querySelector('#followModal'),
            modalTitle: document.querySelector('#followModal-title'),
            userList: document.querySelector('#userList'),
            searchInput: document.querySelector('#search-input'),
            closeBtn: document.querySelector('.followClose'),
            followerBtn: document.querySelector('.follower-btn'),
            followingBtn: document.querySelector('.following-btn'),
            overlay: document.getElementById('followOverlay'),
            confirmDialog: document.getElementById('confirmDialog'),
            cancelBtn: document.getElementById('cancelBtn'),
            confirmBtn: document.getElementById('confirmBtn')
        };

        // 全局状态
        const state = {
            currentUserId: <?php echo json_encode($user_id); ?>,
            currentData: []
        };

        // 关闭按钮事件
        DOM.closeBtn.addEventListener('click', () => {
            DOM.modal.style.display = 'none';
            if (DOM.searchInput) {
                DOM.searchInput.value = '';
            }
        });

        // 点击模态框外部关闭
        window.addEventListener('click', (event) => {
            if (event.target === DOM.modal) {
                DOM.modal.style.display = 'none';
            }
        });

        // Followers按钮点击事件
        if (DOM.followerBtn) {
            DOM.followerBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch('../Profile Backend/get_follower.php?user_id=' + state.currentUserId);
                    const data = await response.json();
                    
                    if (data.success) {
                        openFollowModal('Followers', data.followers || []);
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
        if (DOM.followingBtn) {
            DOM.followingBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch('../Profile Backend/get_following.php?user_id=' + state.currentUserId);
                    const data = await response.json();
                    
                    if (data.success) {
                        openFollowModal('Following', data.following || []);
                    } else {
                        throw new Error(data.message || 'Failed to fetch following');
                    }
                } catch (error) {
                    console.error('Error fetching following:', error);
                    alert('Failed to load following list. Please try again later.');
                }
            });
        }

        // 搜索功能
        if (DOM.searchInput) {
            DOM.searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const filtered = state.currentData.filter(user => 
                    user.username.toLowerCase().includes(query)
                );
                renderUserList(filtered);
            });
        }

        function openFollowModal(title, users) {
            DOM.modalTitle.textContent = title;
            state.currentData = users;
            renderUserList(users);
            DOM.modal.style.display = 'block';
        }

        function renderUserList(users) {
            if (!users || users.length === 0) {
                DOM.userList.innerHTML = '<p class="no-users-message">No users found</p>';
                return;
            }

            const userHTML = users.map(user => `
                <li>
                    <div class="user-details" data-user-id="${user.id}">
                        <a href="../View Profile/ViewProfile.php?user_id=${user.id}" class="profile-link">
                            <img src="${user.profile_picture || '../!! Images/DefaultUser.jpg'}" alt="${user.username}" onerror="this.src='../!! Images/DefaultUser.jpg'">
                        </a>
                        <div>
                            <a href="../View Profile/ViewProfile.php?user_id=${user.id}" class="profile-link">
                                <strong>${user.username}</strong>
                            </a>
                        </div>
                        ${DOM.modalTitle.textContent === 'Followers' ? 
                            `<button class="remove-btn" onclick="handleRemoveFollower(${user.id})">Remove</button>` :
                            `<button class="unfollow-btn" onclick="handleUnfollow(${user.id})">Unfollow</button>`
                        }
                    </div>
                </li>
            `).join('');
            
            DOM.userList.innerHTML = userHTML;
        }
        
        // 添加解除屏蔽和屏蔽用户的全局函数
        window.handleRemoveFollower = function(userId) {
            if(confirm('Are you sure you want to remove this follower?')) {
                const formData = new FormData();
                formData.append('follower_id', userId);
                
                fetch('../Profile Backend/remove_follower.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Remove the user from the list immediately
                        const userElements = document.querySelectorAll(`[data-user-id="${userId}"]`);
                        userElements.forEach(el => {
                            const listItem = el.closest('li');
                            if(listItem) listItem.remove();
                        });
                        
                        // Update the follower count immediately
                        const followerBtn = document.querySelector('.follower-btn');
                        const count = parseInt(followerBtn.textContent);
                        if(!isNaN(count)) {
                            followerBtn.textContent = `${count - 1} Followers`;
                        }
                        
                        // If the list is now empty, show a message
                        const userList = document.getElementById('userList');
                        if (userList.children.length === 0) {
                            userList.innerHTML = '<p class="no-users-message">No users found</p>';
                        }
                    } else {
                        alert(data.message || 'Failed to remove follower');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again later.');
                });
            }
        };
        
        window.handleUnfollow = function(userId) {
            if(confirm('Are you sure you want to unfollow this user?')) {
                const formData = new FormData();
                formData.append('following_id', userId);
                formData.append('action', 'unfollow');
                
                fetch('../Profile Backend/follow.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // 从列表中移除用户
                        const userElements = document.querySelectorAll(`[data-user-id="${userId}"]`);
                        userElements.forEach(el => {
                            const listItem = el.closest('li');
                            if(listItem) listItem.remove();
                        });
                        
                        // 更新关注中数量
                        const followingBtn = document.querySelector('.following-btn');
                        const count = parseInt(followingBtn.textContent);
                        if(!isNaN(count)) {
                            followingBtn.textContent = `${count - 1} Following`;
                        }
                        
                        alert('Unfollowed successfully');
                    } else {
                        alert(data.message || 'Failed to unfollow');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again later.');
                });
            }
        };

        // 通知模态框相关
        const notificationIcon = document.querySelector(".notification-icon");
    const notificationModal = document.getElementById("notificationModal");
    const notificationClose = document.querySelector(".notificationClose");

    // Show modal and fetch notifications
    notificationIcon.addEventListener("click", function() {
        fetchPendingFollowRequests();
        fetchLikes();
        getComments();
        fetchWarnings();
        notificationModal.classList.add('show');
    });

    // Close modal
    notificationClose.addEventListener("click", function() {
        notificationModal.classList.remove('show');
    });

    // Close when clicking outside
    window.addEventListener("click", function(event) {
        if (event.target === notificationModal) {
            notificationModal.classList.remove('show');
        }
    });

    // Fetch pending follow requests
    function fetchPendingFollowRequests() {
        const pendingList = document.getElementById("pendingFollowList");
        pendingList.innerHTML = "<li>Loading requests...</li>";
        
        fetch("../Profile Backend/get_pending_follows.php")
            .then(response => response.json())
            .then(data => {
                console.log("Received follow requests data:", data);
                pendingList.innerHTML = "";

                if (data.success && Array.isArray(data.requests) && data.requests.length > 0) {
                    data.requests.forEach(request => {
                        const li = document.createElement("li");
                        li.innerHTML = `
                            <div class="user-info" data-id="${request.FollowerID}">
                                <img src="${request.profile_picture || '../!! Images/DefaultUser.jpg'}" 
                                     alt="${request.username}" 
                                     onerror="this.src='../!! Images/DefaultUser.jpg'">
                                <span>${request.username} wants to follow you</span>
                            </div>
                            <div class="action-buttons">
                                <button class="accept-btn" onclick="handleFollowRequest(${request.FollowerID}, 'approve')">Accept</button>
                                <button class="reject-btn" onclick="handleFollowRequest(${request.FollowerID}, 'reject')">Reject</button>
                            </div>
                        `;
                        pendingList.appendChild(li);
                    });
                } else {
                    pendingList.innerHTML = "<li>No pending follow requests</li>";
                }
            })
            .catch(error => {
                console.error("Error fetching follow requests:", error);
                pendingList.innerHTML = `
                    <li>
                        Could not load requests. 
                        <button onclick='fetchPendingFollowRequests()'>Try Again</button>
                    </li>`;
            });
    }

    // Handle follow request action
    window.handleFollowRequest = function(requestId, action) {
        console.log(`Handling ${action} for request ID: ${requestId}`);
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('action', action);

        fetch("../Profile Backend/handle_follow_request.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("Response from handle_follow_request:", data);
            if (data.success) {
                // Remove the request from the list
                const requestItem = document.querySelector(`[data-id="${requestId}"]`).closest('li');
                if (requestItem) {
                    requestItem.remove();
                }

                // Update the follower count if accepting
                if (action === 'approve') {
                    const followerBtn = document.querySelector('.follower-btn');
                    if (followerBtn) {
                        const currentText = followerBtn.textContent;
                        const currentCount = parseInt(currentText) || 0;
                        followerBtn.textContent = `${currentCount + 1} Followers`;
                    }
                }

                // If no more requests, show message
                const pendingList = document.getElementById("pendingFollowList");
                if (pendingList.children.length === 0) {
                    pendingList.innerHTML = "<li>No pending follow requests</li>";
                }
            } else {
                console.error("Action failed:", data.message);
                alert(data.message || "Action failed. Please try again.");
            }
        })
        .catch(error => {
            console.error("Error handling follow request:", error);
            alert("An error occurred. Please try again.");
        });
    };

   // Function to fetch likes from the database
   function fetchLikes() {
    fetch("../Profile Backend/get_likes.php")
        .then(response => response.json())
        .then(data => {
            console.log("Fetched Likes Data:", data);

            const likeList = document.getElementById("LikesList"); // Ensure exact match with HTML
            if (!likeList) {
                console.error("Element with id 'likesList' not found.");
                return;
            }
            likeList.innerHTML = ""; // Clear previous data

            if (data.success && Array.isArray(data.likes) && data.likes.length > 0) {
                data.likes.forEach(like => {
                    const likeItem = document.createElement("div");
                    likeItem.classList.add("like-item"); // Flex container for each like

                    // Set profile picture and post image
                    const profilePic = like.profile_picture || "../!! Images/DefaultUser.jpg";
                    const postImage = like.fileImage || "../!! Images/DefaultPost.jpg";

                    likeItem.innerHTML = `
                          <div class="like-info">
        <img class="profile-pic" src="../!! Images/${profilePic}" alt="${like.username}">
        <div class="like-details">
            <p><strong>${like.username}</strong> liked post #${like.PostID}</p>
        </div>
    </div>

    <div class="post-image-container">
        <img class="post-image" src="../!! Images/${postImage}" alt="Post Image">
        <p><em>${like.titleText}</em></p>
    </div>
            `;
                    likeList.appendChild(likeItem);
                });
            } else {
                console.log("No likes found.");
                likeList.innerHTML = "<p>No one has liked any posts yet. Be the first to like!</p>";
            }
        })
        .catch(error => {
            console.error("Error fetching likes:", error);
        });
}

document.addEventListener("DOMContentLoaded", fetchLikes);

function fetchWarnings() {
    fetch("../Profile Backend/get_warnings.php")
        .then(response => response.json())
        .then(data => {
            const warningsList = document.getElementById("WarningsList");
            warningsList.innerHTML = "";

            if (data.success && Array.isArray(data.warnings) && data.warnings.length > 0) {
                data.warnings.forEach(warning => {
                    const warningItem = document.createElement("div");
                    warningItem.classList.add("warning-item");

                    warningItem.innerHTML = `
                        <div class="warning-info">
                            <div class="warning-details">
                                <p class="warning-text">
                                    <span class="warning-icon">⚠️</span>
                                    ${warning.warning_reason}
                                </p>
                                <small>${warning.warning_date}</small>
                            </div>
                        </div>
                    `;
                    warningsList.appendChild(warningItem);
                });
            } else {
                warningsList.innerHTML = "<p>No warnings.</p>";
            }
        })
        .catch(error => {
            console.error("Error fetching warnings:", error);
        });
}

// Update fetchComments function for notifications
function getComments() {
    const commentsList = document.getElementById('CommentsList');
    commentsList.innerHTML = '<p>Loading comments...</p>';

    fetch('../Profile Backend/get_comments.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comments.length > 0) {
                commentsList.innerHTML = data.comments.map(comment => `
                    <div class="comment-item">
                        <div class="comment-info">
                            <img src="../!! Images/${comment.commenter_profile_pic}" 
                                 alt="${comment.commenter_name}"
                                 onerror="this.src='../!! Images/DefaultUser.jpg'">
                            <div class="comment-details">
                                <strong>${comment.commenter_name}</strong>
                                <p>Commented on: ${comment.post_title}</p>
                                <p class="comment-text">${comment.comment_text}</p>
                                <small>${new Date(comment.comment_date).toLocaleString()}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                commentsList.innerHTML = '<p>No recent comments</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching comments:', error);
            commentsList.innerHTML = '<p>Failed to load comments</p>';
        });
}

// Add event listeners for more options
document.addEventListener('click', function(event) {
    // Toggle options menu
    if (event.target.closest('.more-vert-icon')) {
        const optionsMenu = event.target.closest('.more-options-container')
                            .querySelector('.post-options-menu');
        optionsMenu.classList.toggle('show');
    }
    
    // Handle edit button
    if (event.target.closest('.edit-btn')) {
        const postId = document.getElementById('overlay-post-id').value;
        const title = document.getElementById('overlay-title').textContent;
        const caption = document.getElementById('overlay-caption').textContent;
        
        // Show edit form
        document.querySelector('.edit-form').style.display = 'block';
        document.getElementById('edit-title').value = title;
        document.getElementById('edit-caption').value = caption;
    }
    
    // Handle save edit
    if (event.target.closest('.save-edit-btn')) {
        const postId = document.getElementById('overlay-post-id').value;
        const newTitle = document.getElementById('edit-title').value.trim();
        const newCaption = document.getElementById('edit-caption').value.trim();
        
        // Validate inputs
        if (!postId) {
            alert('Error: Post ID is missing');
            return;
        }

        if (!newTitle || !newCaption) {
            alert('Please fill in both title and caption');
            return;
        }

        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('title', newTitle);
        formData.append('caption', newCaption);

        fetch('../Profile Backend/update_post.php', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            console.log('Raw server response:', text);
            
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                console.error('Raw response was:', text);
                throw new Error('Server returned invalid JSON');
            }
        })
        .then(data => {
            console.log('Parsed response:', data);
            
            if (data.success) {
                // Update displayed content
                document.getElementById('overlay-title').textContent = newTitle;
                document.getElementById('overlay-caption').textContent = newCaption;
                document.querySelector('.edit-form').style.display = 'none';
                document.querySelector('.post-options-menu').classList.remove('show');
                alert('Post updated successfully!');
                
                // Refresh the post details
                window.location.reload(); // This is a simpler approach to refresh everything
            } else {
                throw new Error(data.message || 'Failed to update post');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update post: ' + error.message);
        });
    }
    
    // Handle delete button
    if (event.target.closest('.delete-btn')) {
        if (confirm('Are you sure you want to delete this post?')) {
            const postId = document.getElementById('overlay-post-id').value;
            
            // Immediately close the overlay
            const overlay = document.getElementById('overlay1');
            overlay.style.opacity = '0';
            overlay.style.visibility = 'hidden';
            
            // Create FormData
            const formData = new FormData();
            formData.append('post_id', postId);

            fetch('../Profile Backend/delete_post.php', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                console.log('Raw delete response:', text);
                
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    console.error('Raw response was:', text);
                    throw new Error('Server returned invalid JSON');
                }
            })
            .then(data => {
                if (data.success) {
                    window.location.href = 'ViewOwnProfile.php'; // Redirect to profile page
                } else {
                    throw new Error(data.message || 'Failed to delete post');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete post: ' + error.message);
            });
        }
    }
    
    // Handle cancel button
    if (event.target.closest('.cancel-btn')) {
        document.querySelector('.post-options-menu').classList.remove('show');
    }
    
    // Handle cancel edit
    if (event.target.closest('.cancel-edit-btn')) {
        document.querySelector('.edit-form').style.display = 'none';
    }
});

    }); // End of DOMContentLoaded

    </script>
    
</body>
</html>