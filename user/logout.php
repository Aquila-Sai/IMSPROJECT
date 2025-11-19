<?php
// user/logout.php - User Logout (Delete session)
session_start();
session_destroy();
header('Location: /lumen/index.php');
exit();
?>