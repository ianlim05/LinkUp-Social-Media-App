<?php
session_start();
session_unset();  // Clear session data
session_destroy(); // Destroy session

header("Location: ../Homepage/Homepage.php");
exit();