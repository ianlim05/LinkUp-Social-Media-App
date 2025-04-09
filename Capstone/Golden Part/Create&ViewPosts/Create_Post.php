<?php
session_start();

// if (!isset($_SESSION['userid'])) {
//     header('Location: ../login/login.php');
//     exit();
// }

$_SESSION['user_id'] = 2; // For testing Purposes
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Posts</title>
    <link rel="stylesheet" href="Create_Post.css?v=<?php echo time(); ?>">

</head>
<header><?php include "../header.html";?> </header>

<body>
<div class="container">
        <div class="inputContainer">
            <form action="ViewPost.php" method="post" enctype="multipart/form-data" autocomplete="off">
            <h2>Create A Post</h2>

            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

            <div class="input-box" style="width: 400px; height: 400px; position: relative; ">
                <img id="preview" class="previewImage">
                <input type="file" id="fileInput" name="fileImage" accept="image/*"> 
            </div>
        </div>    
        <div class="inputContainer2">
            <div class="input-box">
                <input type="text" id="myText" name="titleText" required>
                <label>Enter Title</label>
            </div>
            <div class="input-box">
                <input type="text" id="myText" name="captionText" required>
                <label>Enter Caption</label>
            </div>      
                <input type="submit" class="submit" name="submit" value="Upload">       
            </form>
        </div>
        
</div>    

<script src="Create_Post.js?v=<?php echo time(); ?>"></script>


</body>
</html>

