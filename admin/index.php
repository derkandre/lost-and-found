<?php
require '../database/connection.php';
include '../security/encryption.php';
include 'session-details.php';

session_start();

$_SESSION["active-page"] = "home";

if ((!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) || $_SESSION["user_role"] != "Admin") {
    header("Location: ../error/401.php?ref=login&role=admin");
    exit();
}
?>

<html>

<head>
    <title>LostTrack | Admin Panel</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>

<body class="admin-body">
    <?php require 'sidebar.php' ?>

    <div class="main-content">
        <div class="dashboard">
            <div class="card">
                <div class="card-header">
                    <h2>Admin Dashboard</h2>
                </div>
                <div class="card-body">
                    <p><?php echo date('m/d/Y'); ?></p>
                    <p>Welcome back, <?php echo htmlspecialchars(getNameOfUser(decryptData(($_SESSION["user_id"])), $conn)); ?>!</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>