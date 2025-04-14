<?php

include '../database/connection.php';
include '../security/encryption.php';

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../error/401.php?ref=login");
} else {    
    echo "You are logged in with user id: " . decryptData($_GET["session"]);
}

?>

<html>

</html>