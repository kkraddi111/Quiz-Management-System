<?php
session_start();
session_destroy();
header("Location: logout-successful.php");
exit();
?>