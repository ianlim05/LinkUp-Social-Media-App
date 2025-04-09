<?php
session_start();

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location:../../Auth/login.php");
    exit();
}

require_once(__DIR__ . '/../../!! General/database.php');



$message = ""; 
$searchMessage = "";

// Handle adding a blacklisted word
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['BlacklistedWord'])) {
    $blacklistedword = trim($_POST['BlacklistedWord']);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM filter WHERE BlacklistedWord = ?");
    $stmt->bind_param("s", $blacklistedword);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $message = "The word '$blacklistedword' already exists in the database!";
    } else {
        // Insert the new blacklisted word
        $stmt = $conn->prepare("INSERT INTO filter (BlacklistedWord) VALUES (?)");
        $stmt->bind_param("s", $blacklistedword);
        $stmt->execute();
        $stmt->close();
        header("Location: admin-dashboard.php"); 
    }
}

// Handle deleting a blacklisted word
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM filter WHERE FilterID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin-dashboard.php"); 
    exit();
}

// Handle searching for blacklisted words
$searchQuery = "";
$searchResults = [];
if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    $sql = "SELECT * FROM filter WHERE BlacklistedWord LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $searchQuery . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $searchMessage = "No results found for '$searchQuery'.";
    } else {
        $searchResults = $result->fetch_all(MYSQLI_ASSOC);
    }
} else {
    // Fetch all blacklisted words if no search query
    $sql = "SELECT * FROM filter";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);
}
include __DIR__ . '/../../!! General/admin-header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(#0671B7 0%,#67A3D9 30%, #FDE2ED 60%, #F3C4D6 80%, #F6ACC5 100%);
            min-height: 100vh;
        }
        .header {
            background: #2c3e50;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }
        .logo img {
            width: 200px;
            height: auto;
            max-height: 80px;
        }
        .nav {
            display: flex;
            gap: 20px;
        }
        .nav button {
            padding: 10px 20px;
            border: none;
            background: #4a90e2;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        .nav button:hover {
            background: #357ABD;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            gap: 10px;
            position: relative;
        }
        .profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile span {
            font-size: 18px;
            color: white;
        }
        .dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            display: none;
            flex-direction: column;
            min-width: 120px;
            z-index: 100;
        }
        .profile:hover .dropdown {
            display: flex;
        }
        .dropdown a {
            padding: 10px;
            text-decoration: none;
            color: #333;
            display: block;
            text-align: center;
            font-weight: bold;
        }
        .dropdown a:hover {
            background: #f0f0f0;
            border-radius: 10px;
        }
        .content {
            padding: 20px;
        }
        .container {
            width: 50%;
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        h2 {
            color: #333;
        }
        input, button {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #28a745;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .delete-btn {
            color: red;
            font-weight: bold;
            text-decoration: none;
            margin-left: 10px;
        }
        .delete-btn:hover {
            color: darkred;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background: #fff;
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0px 0px 5px rgba(0,0,0,0.1);
        }
        .form-container {
            display: flex;
            justify-content: center;
            gap: 10px; 
            margin-bottom: 20px;
        }
        .form-container form {
            display: flex;
            align-items: center;
            gap: 5px; 
        }
    </style>
</head>
<body>


    <div class="content">
        <h1>Welcome, Admin!</h1>

        <!-- Filtering System -->
        <div class="container">
            <h2>Blacklisted Words</h2>

            <!-- Display error message if word already exists -->
            <?php if (!empty($message)): ?>
                <p class="error-message"><?php echo $message; ?></p>
            <?php endif; ?>

            <!-- Search and Add Forms -->
            <div class="form-container">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search words" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit">Search</button>
                </form>

                <form method="POST" action="">
                    <input type="text" name="BlacklistedWord" placeholder="Add a word" required>
                    <button type="submit">Add</button>
                </form>
            </div>

            <!-- Display search message if no results found -->
            <?php if (!empty($searchMessage)): ?>
                <p class="error-message"><?php echo $searchMessage; ?></p>
            <?php endif; ?>

            <!-- List of Blacklisted Words -->
            <ul>
                <?php foreach ($searchResults as $row): ?>
                    <li>
                        <?php echo htmlspecialchars($row['BlacklistedWord']); ?>
                        <a class="delete-btn" href="?delete=<?php echo $row['FilterID']; ?>">❌</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>