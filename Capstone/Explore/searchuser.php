<?php
session_start();
require_once '../!! General/database.php';
$connection = $conn;

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (isset($_GET['search'])) {
    $search = $connection->real_escape_string($_GET['search']);
    
    // Add debugging
    $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    error_log("Current user ID: " . $current_user_id); // Debug log
    
    // Modified query to check both ways in the block table
    $query = "SELECT DISTINCT u.id, u.username, u.profile_picture 
             FROM users u 
             WHERE u.role = 'user' 
             AND u.id NOT IN (
                 SELECT BlockedUserID 
                 FROM block 
                 WHERE UserID = " . $current_user_id . "
             )
             AND u.id NOT IN (
                 SELECT UserID 
                 FROM block 
                 WHERE BlockedUserID = " . $current_user_id . "
             )";
    
    error_log("Query: " . $query); // Debug log
    
    if (!empty($search)) {
        $query .= " AND u.username LIKE '%$search%'";
    }

    $result = $connection->query($query);
    $users = [];

    while ($row = $result->fetch_assoc()) {
        // If profile_picture is empty or doesn't exist, it will be handled by the onerror attribute in HTML
        $row['profile_picture'] = !empty($row['profile_picture']) ? "../!! Images/" . $row['profile_picture'] : "../!! Images/DefaultUser.jpg"; 
        $users[] = $row;
    }

    echo json_encode($users);
    exit; // Stop further HTML output
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'], $_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "You must be logged in to comment.";
        header("Location: searchuser.php?id=" . $_POST['post_id']);
        exit();
    }

    $PostID = $_POST['post_id'];
    $UserID = $_SESSION['user_id'];
    $comment = trim(mysqli_real_escape_string($conn, $_POST['comment']));
    $date = date("Y-m-d");

    // Checks for blacklisted words
    foreach ($blacklistedKeywords as $word) {
        if (stripos($comment, $word) !== false) {
            $_SESSION['error_message'] = "<div class='error-message'>Your comment contains inappropriate language and cannot be posted.</div>";
            header("Location: searchuser.php?id=" . $PostID);
            exit();
        }
    }

    // Insert clean comment into the database
    $insert_sql = "INSERT INTO comments (PostID, UserID, comment, date) VALUES ('$PostID', '$UserID', '$comment', '$date')";
    mysqli_query($conn, $insert_sql);

    // Redirect to refresh page without error message
    header("Location: searchuser.php?id=" . $PostID);
    exit();
}

error_log("Session contents: " . print_r($_SESSION, true));
include '../!! General/header.php';
?>

<!DOCTYPE html>
<html lang="en">        
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../!! Images/LinkUp-logo.png">
    <title>Search</title>
    <script src="../!! General/Countdown_Timer.js"></script>
    <link rel="stylesheet" href="searchuser.css">
    <style>
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
            width: 400px;
            text-align: center;
        }
        #reportModal textarea {
            width: 90%;
            height: 100px;
            margin: 15px auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: none;
            display: block;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        #reportModal button {
            width: 90%;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin: 0 auto;
            display: block;
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
        .commentSection {
            position: relative;
        }
        .commentSection form {
            display: flex;
            flex-direction: column;
        }
        .commentInput {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .commentButton {
            background-color: #000;
            color: white;
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 5px;
        }
        .commentButton:hover {
            background-color: #333;
        }
    </style>
</head>
<body>

<div class="search-container">
    <input type="text" id="search" placeholder="Search users...">
</div>
<div class="button-container">
    <button id="userBtn">User</button>
    <button id="postBtn">Post</button>
</div>
<div id="userList"></div>
<div id="postList"></div>

<div id="overlay1">
    <div class="overlayBackground">
        <div class="overlayContent1">
            <div class="imgContainer">
                <img src="" alt="Post Image">
            </div> 
            <div class="post-details">
                <h2 class="posts-title"></h2>
                <p class="post-text"></p>

                <div class="commentList">
                    <!-- Comments will be loaded dynamically -->
                </div>
                <?php if (!empty($errorMessage)): ?>
                    <div class="error-message">
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?> 

                <!-- Comment Section -->
                <div class="commentSection">
                    <form autocomplete="off">
                        <input type="hidden" name="post_id" value="">
                        <input type="text" class="commentInput" name="comment" placeholder="Write a comment..." required>
                        <button type="submit" class="commentButton">Submit</button>
                    </form>
                </div>
            </div>
        </div>    
        <div class="post-footer">
            <div class="post-actions">
                <div class="heart-bg">
                    <span id="like-btn" class="heart-icon"></span>
                </div>
                <span id="comment-btn">💬</span>
                <button id="report-btn" class="ReportButton">🚩</button>
            </div>
            <div class="dateCreated">
                Posted On: <span class="date"></span>
            </div>
        </div>
        <a href="searchuser.php" class="xButton1">✖</a>
    </div>
</div>

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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="User_View_Post.js?v=<?php echo time(); ?>"></script>

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

        fetch('../View_Post/report_post.php', {
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

    $(document).ready(function() {
        let currentSearchType = 'user';

        function loadUsers(search = '') {
            $("#userList").html('<div class="loading">Loading...</div>');
            $.ajax({
                url: window.location.href,
                type: "GET",
                data: { search: search },
                success: function(response) {
                    let users = JSON.parse(response);
                    let output = "";

                    if (users.length === 0) {
                        output = `
                            <div class="empty-state" style="text-align: center; font-size: 20px;">
                                <p>No users found</p>
                            </div>`;
                    } else {
                        users.forEach(user => {
                            output += `
                                <div class="user-item" onclick="window.location.href='../View Profile/ViewProfile.php?user_id=${user.id}'">
                                    <img src="../!! Images/${user.profile_picture}" 
                                        alt="${user.username}"
                                        onerror="this.src='../!! Images/DefaultUser.jpg'">
                                    <div class="user-info">
                                        <span class="username">${user.username}</span>
                                    </div>
                                </div>`;
                        });
                    }
                    $("#userList").html(output).show();
                    $("#postList").hide();
                },
                error: function() {
                    $("#userList").html("<p>Error loading users.</p>").show();
                }
            });
        }

        function loadPosts(search = '') {
            $("#postList").html('<div class="loading">Loading...</div>');
            $.ajax({
                url: "fetch_posts.php",
                type: "GET",
                data: { search: search },
                dataType: 'json',
                success: function(response) {
                    let output = `<div class="post-container">`;
                    
                    if (!response.success) {
                        console.error("Error:", response.error);
                        output = `<div class="error-message"><p>Error loading posts</p></div>`;
                    } else {
                        const posts = response.posts || [];
                        
                        if (posts.length === 0) {
                            output = `
                                <div class="no-posts-message" style="text-align: center; font-size: 20px;">
                                    <p>No posts found</p>
                                </div>`;
                        } else {
                            posts.forEach(post => {
                                output += `
                                    <div class="post-card" data-post-id="${post.PostID}">
                                        <div class="post-image-container">
                                            <img src="../!! Images/${post.fileImage}" 
                                                alt="Post Image" 
                                                class="post-img"
                                                onerror="this.src='../!! Images/DefaultPost.png'">
                                        </div>
                                        <div class="post-card-footer">
                                            <div class="post-card-user">
                                                <img src="../!! Images/${post.profile_picture}" 
                                                    alt="User Avatar" 
                                                    class="post-card-avatar"
                                                    onerror="this.src='../!! Images/DefaultUser.jpg'">
                                                <span class="post-card-username">${post.username}</span>
                                            </div>
                                            <div class="post-card-title">
                                                <h3>${post.titleText}</h3>
                                            </div>
                                        </div>
                                    </div>`;
                            });
                        }
                    }
                    output += `</div>`;
                    $("#postList").html(output).show();
                    $("#userList").hide();
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                    console.log("Response:", xhr.responseText);
                    $("#postList").html("<p>Error loading posts.</p>").show();
                }
            });
        }

        // Initial load
        loadUsers();

        // Button click handlers
        $("#userBtn").click(function() {
            currentSearchType = 'user';
            $(this).addClass('active').siblings().removeClass('active');
            $("#search").attr("placeholder", "Search users...");
            loadUsers($("#search").val());
        });

        $("#postBtn").click(function() {
            currentSearchType = 'post';
            $(this).addClass('active').siblings().removeClass('active');
            $("#search").attr("placeholder", "Search posts...");
            loadPosts('');
        });

        // Search input handler
        let searchTimeout;
        $("#search").keyup(function() {
            clearTimeout(searchTimeout);
            let searchValue = $(this).val();
            searchTimeout = setTimeout(() => {
                if (currentSearchType === 'user') {
                    loadUsers(searchValue);
                } else {
                    loadPosts(searchValue);
                }
            }, 300);
        });

        // Post card click handler
        $(document).on('click', '.post-card', function() {
            const overlay = document.getElementById("overlay1");
            const postId = $(this).data('post-id');
            
            console.log('Clicking post with ID:', postId);
            
            overlay.style.visibility = "visible";
            overlay.style.opacity = "1";
            
            $.ajax({
                url: 'fetch_post_details.php',
                type: 'GET',
                data: { post_id: postId },
                dataType: 'json',
                success: function(data) {
                    console.log('Received data:', data);
                    
                    if (!data.success) {
                        console.error('Error:', data.error);
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    const post = data.post;
                    const comments = data.comments;
                    
                    $('.imgContainer img').attr('src', `../!! Images/${post.fileImage}`);
                    $('.posts-title').text(post.titleText);
                    $('.post-text').text(post.captionText);
                    $('.date').text(post.date);
                    
                    let commentHTML = '';
                    if (comments && comments.length > 0) {
                        comments.forEach(comment => {
                            commentHTML += `
                                <div class="commentItem">
                                    <p><strong>${comment.username}</strong>: ${comment.comment}</p>
                                    <small>${comment.date}</small>
                                </div>`;
                        });
                    } else {
                        commentHTML = '<p class="noComments">Start The Comments!</p>';
                    }
                    $('.commentList').html(commentHTML);
                    
                    $('input[name="post_id"]').val(post.PostID);
                    $('#like-btn').attr('data-post-id', post.PostID);
                    window.history.pushState({}, "", `?id=${post.PostID}`);
                },
                error: function(xhr, status, error) {
                    console.error('Error details:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText
                    });
                    alert('Error loading post details. Please check the console for more information.');
                }
            });
        });

        // Comment form submission
        $(document).on('submit', '.commentSection form', function(e) {
            e.preventDefault();
            const form = $(this);
            const commentInput = form.find('.commentInput');
            const comment = commentInput.val();
            const post_id = parseInt(form.find('input[name="post_id"]').val());

            if (!post_id) {
                form.prepend('<div class="error-message" style="color: red; margin-bottom: 10px;">Invalid post ID.</div>');
                return;
            }

            form.find('.error-message').remove();

            if (!comment.trim()) return;

            $.ajax({
                url: '../View_Post/check_blacklist.php',
                type: 'POST',
                data: { text: comment },
                dataType: 'json',
                success: function(response) {
                    if (response.hasBlacklistedWord) {
                        form.prepend('<div class="error-message">Your comment contains inappropriate language and cannot be posted.</div>');
                    } else {
                        $.ajax({
                            url: 'add-comment.php',
                            type: 'POST',
                            data: { post_id: post_id, comment: comment },
                            dataType: 'json',
                            success: function(response) {
                                console.log('Comment submission response:', response);
                                if (response.success) {
                                    commentInput.val('');
                                    form.find('.error-message').remove();
                                    
                                    // Create new comment element and prepend it
                                    const newCommentHTML = `
                                        <div class="commentItem">
                                            <p><strong>${response.username}</strong>: ${comment}</p>
                                            <small>${response.date}</small>
                                        </div>`;
                                    
                                    $('.commentList').prepend(newCommentHTML);
                                    $('.noComments').remove();
                                } else {
                                    form.prepend('<div class="error-message" style="color: red; margin-bottom: 10px;">' + (response.error || 'Failed to post comment.') + '</div>');
                                }
                            },
                            error: function(xhr) {
                                console.error('Comment submission error:', xhr.responseText);
                                form.prepend('<div class="error-message" style="color: red; margin-bottom: 10px;">Failed to post comment. Please try again.</div>');
                            }
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Blacklist check error:', xhr.responseText);
                    form.prepend('<div class="error-message" style="color: red; margin-bottom: 10px;">Error checking comment. Please try again.</div>');
                }
            });
        });

        // Report button click handler
        $(document).on('click', '#report-btn', function() {
            const postId = $('input[name="post_id"]').val();
            if (postId) {
                openReportModal(postId);
            } else {
                console.error('No post ID found');
            }
        });
    });
</script>

</body>
</html>