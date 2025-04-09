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


$sql = "SELECT BlacklistedWord FROM filter"; 
$result = mysqli_query($conn, $sql);

$blacklistedKeywords = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $blacklistedKeywords[] = $row['BlacklistedWord'];
    }
}

// Retrieve any error message from session
$errorMessage = "";
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear message after displaying
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titleText = $_POST['titleText'];
    $captionText = $_POST['captionText'];

    foreach ($blacklistedKeywords as $keyword) {
        if (stripos($titleText, $keyword) !== false || stripos($captionText, $keyword) !== false) {
            $_SESSION['error_message'] = "Your Post Contains a Blacklisted Word: '$keyword'. Please Remove It and Try gain.";
            header("Location: Create_Post.php"); // Redirect back to form
            exit();
        }
    }

    $date = date("Y-m-d");
    $UserID = $_SESSION['user_id'];

    if (isset($_FILES['fileImage']) && $_FILES['fileImage']['error'] == 0) {
        $file_name = $_FILES['fileImage']['name'];
        $tempname = $_FILES['fileImage']['tmp_name'];
        $folder = '../!! Images/' . $file_name;

        // Validate file type
        $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $valid_extensions = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $valid_extensions)) {
            if (move_uploaded_file($tempname, $folder)) {
                $titleText = mysqli_real_escape_string($conn, $titleText);
                $captionText = mysqli_real_escape_string($conn, $captionText);
                $file_name = mysqli_real_escape_string($conn, $file_name);

                // Insert into database
                $sql = "INSERT INTO post (titleText, captionText, fileImage, date, UserID) 
                        VALUES ('$titleText', '$captionText', '$file_name', '$date', '$UserID')";

                if (mysqli_query($conn, $sql)) {
                    header("Location: ../Homepage/Homepage.php");
                    exit();
                } else {
                    $_SESSION['error_message'] = "Error saving the record: " . mysqli_error($conn);
                    header("Location: Create_Post.php");
                    exit();
                }
            } else {
                $_SESSION['error_message'] = "Failed to move the uploaded file.";
                header("Location: Create_Post.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type.";
            header("Location: Create_Post.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "No file uploaded.";
        header("Location: Create_Post.php");
        exit();
    }
}
include "../!! General/header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../!! Images/LinkUp-logo.png">
    <title>Create Posts</title>
    <script src="../!! General/Countdown_Timer.js"></script>
    <link rel="stylesheet" href="Create_Post.css?v=<?php echo time(); ?>">
</head>


<body>
<div class="container">
    <div class="inputContainer">
        <form action="" method="post" enctype="multipart/form-data" autocomplete="off">
            <h2>Create A Post</h2>

            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

            <div class="input-box" style="width: 400px; height: 400px; position: relative;">
                <img id="preview" class="previewImage">
                <input type="file" id="fileInput" name="fileImage" accept="image/*"> 
            </div>
    </div>    

    <div class="inputContainer2">
        <!-- Displays an error message if a blacklisted keyword is found -->
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message" >
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <div class="input-box">
            <input type="text" name="titleText" required>
            <label>Enter Title</label>
        </div>
        <div class="input-box">
            <input type="text" name="captionText" required>
            <label>Enter Caption</label>
        </div>      

        <input type="submit" class="submit" name="submit" value="Upload">       
        </form>
    </div>
</div>    

<script src="Create_Post.js?v=<?php echo time(); ?>"></script>

</body>
</html>
