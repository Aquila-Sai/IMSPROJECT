<?php
// user/logout.php - User Logout (Delete session)
session_start();
session_destroy();
header('Location: /index.php');
exit();
?>