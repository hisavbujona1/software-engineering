<?php

session_start();


// Remove admin session

unset($_SESSION['admin_id']);

unset($_SESSION['admin_name']);


// Remove all session

session_destroy();


// Redirect common login page

header("Location: ../login.php");

exit();

?>