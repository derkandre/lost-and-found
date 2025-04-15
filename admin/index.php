<?php

include '../database/connection.php';
include '../security/encryption.php';

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../error/401.php?ref=login");
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>

<body class="admin-layout">
    <?php require 'sidebar.php' ?>
    <div class="main-content">
        <div class="container-solid">
            <h1>Welcome to Admin Panel</h1>
            <p>You are logged in as user ID: <?php echo htmlspecialchars(decryptData($_SESSION["user_id"])); ?></p>
        </div>
    </div>
</body>

</html>