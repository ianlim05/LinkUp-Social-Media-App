<?php
session_start(); // Start the session

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location:../../Auth/login.php");
    exit();
}

$connection = new mysqli("localhost", "root", "", "linkup");

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Handle AJAX request and return JSON
if (isset($_GET['search'])) {
    $search = $connection->real_escape_string($_GET['search']);
    
    // Modified query to only show users with role 'user'
    $query = "SELECT u.id, u.username, u.profile_picture, 
                     CASE 
                         WHEN b.UserID IS NOT NULL THEN 'banned'
                         WHEN d.UserID IS NOT NULL THEN 'deactivated'
                         ELSE 'active'
                     END AS status
              FROM users u
              LEFT JOIN ban b ON u.id = b.UserID
              LEFT JOIN deactivation d ON u.id = d.UserID
              WHERE u.role = 'user'";
    
    if (!empty($search)) {
        $query .= " AND u.username LIKE '%$search%'";
    }

    $result = $connection->query($query);
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $row['profile_picture'] = !empty($row['profile_picture']) ? "../../!! Images/" . $row['profile_picture'] : "../../!! Images/DefaultUser.jpg"; 
        $users[] = $row;
    }

    echo json_encode($users);
    exit; // Stop further HTML output
}

// Include the admin header
require_once(__DIR__ . '/../../!! General/admin-header.php');
?>
<title>Search</title>
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(#0671B7 0%,#67A3D9 30%, #FDE2ED 60%, #F3C4D6 80%, #F6ACC5 100%);
        padding-top: 100px;
        height: 100vh;
        overflow: hidden; /* Prevent scrolling */
    }
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
    .h-nav {
        gap: 40px !important;
    }
    .h-nav a {
        padding: 15px 30px !important;
        font-size: 20px !important;
    }
    .h-logo img {
        width: 200px !important;
        max-height: 80px !important;
    }
    .content {
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: calc(100vh - 150px); /* Set fixed height */
        overflow-y: auto; /* Allow content area to scroll if needed */
    }
    #search {
        width: 80%;
        max-width: 500px;
        padding: 10px;
        margin: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
    }
    #userList {
        width: 80%;
        max-width: 500px;
        margin-top: 20px;
        overflow-y: auto; /* Allow user list to scroll if needed */
    }
    .user-item {
        background: white;
        padding: 15px;
        margin: 10px 0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .user-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .user-item img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #f0f0f0;
    }
    .user-info {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .username {
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }
    .status {
        font-size: 14px;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-block;
    }
    .status-banned {
        background-color: #ffe6e6;
        color: #ff0000;
    }
    .status-deactivated {
        background-color: #fff3e6;
        color: #ffa500;
    }
    .status-active {
        background-color: #e6ffe6;
        color: #28a745;
    }
    h1 {
        color: #333;
        margin-bottom: 20px;
    }
    .loading {
        text-align: center;
        padding: 20px;
        color: #666;
    }
    .empty-state {
        text-align: center;
        padding: 20px;
        color: #666;
        background: white;
        border-radius: 5px;
        margin-top: 20px;
    }
</style>

<div class="content">
    <h1>Search Users</h1>
    <input type="text" id="search" placeholder="Search users">
    <div id="userList"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
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
                            <div class="empty-state">
                                <p>No users found</p>
                            </div>`;
                    } else {
                        users.forEach(user => {
                            let statusClass = "status-active";
                            if (user.status === 'banned') {
                                statusClass = "status-banned";
                            } else if (user.status === 'deactivated') {
                                statusClass = "status-deactivated";
                            }

                            output += `
                                <div class="user-item" onclick="location.href='admin-viewprofile.php?user_id=${user.id}'">
                                    <img src="${user.profile_picture}" 
                                        alt="${user.username}"
                                        onerror="this.src='../../!! Images/DefaultUser.jpg'">
                                    <div class="user-info">
                                        <span class="username">${user.username}</span>
                                        <span class="status ${statusClass}">(${user.status})</span>
                                    </div>
                                </div>`;
                        });
                    }
                    $("#userList").html(output);
                },
                error: function() {
                    $("#userList").html("<p>Error loading users.</p>");
                }
            });
        }

        // Load all users by default
        loadUsers();

        // Filter users as you type with debounce
        let searchTimeout;
        $("#search").keyup(function() {
            clearTimeout(searchTimeout);
            let searchValue = $(this).val();
            searchTimeout = setTimeout(() => {
                loadUsers(searchValue);
            }, 300);
        });
    });
</script>