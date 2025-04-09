<?php

$conn = new mysqli("localhost", "root", "", "linkup");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = ""; 
$searchMessage = "";


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
       
        $stmt = $conn->prepare("INSERT INTO filter (BlacklistedWord) VALUES (?)");
        $stmt->bind_param("s", $blacklistedword);
        $stmt->execute();
        $stmt->close();
        header("Location: filter.php"); 
    }
}


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM filter WHERE FilterID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: filter.php"); 
    exit();
}

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
    $sql = "SELECT * FROM filter";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keyword Filter System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: #f4f4f4;
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

<div class="container">
    <h2>Blacklisted Words</h2>

   
    <?php if (!empty($message)): ?>
        <p class="error-message"><?php echo $message; ?></p>
    <?php endif; ?>

  
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

   
    <?php if (!empty($searchMessage)): ?>
        <p class="error-message"><?php echo $searchMessage; ?></p>
    <?php endif; ?>

    <ul>
        <?php foreach ($searchResults as $row): ?>
            <li>
                <?php echo htmlspecialchars($row['BlacklistedWord']); ?>
                <a class="delete-btn" href="?delete=<?php echo $row['FilterID']; ?>">❌</a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>

<?php $stmt->close(); $conn->close(); ?>
</body>
</html>


