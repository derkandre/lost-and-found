<?php

require '../database/connection.php';
include '../security/encryption.php';
include 'session-details.php';

session_start();
$_SESSION["active-page"] = "accounts";

if ((!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) || $_SESSION["user_role"] != "Admin") {
    header("Location: ../error/401.php?ref=login&role=admin");
    exit();
}

$errorMsg = $successMsg = "";

if (isset($_SESSION['success_msg'])) {
    $successMsg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
    $errorMsg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

if (isset($_GET["action"]) && isset($_GET["result"])) {
    if ($_GET["action"] === "delete" && $_GET["result"] === "success") {
        $_SESSION["success_msg"] = "The user has been deleted successfully!";
        header("Location: accounts.php");
        exit();
    }
}


?>

<html>

<head>
    <title>LostTrack | Manage Accounts</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>

<body class="admin-body">
    <?php require 'sidebar.php' ?>

    <div class="main-content" style="background-color: #fff; border-radius: 8px;">
        <h1 style="color: white;">Account Management</h1>
        <div style="text-align: right; margin-bottom: 15px; margin-top: -34px;">
            <a href="new-account.php" class="secondary-button">
                <i class="ri-user-add-line"></i> Register New Account
            </a>
        </div>
        <br>

        <table style="width: 100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Contact Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT u.user_id, s.student_id, u.username, s.first_name, s.middle_name, s.last_name, s.contact 
                      FROM users u JOIN students s ON u.user_id = s.user_id";

                $stmt = $conn->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($data = $result->fetch_assoc()) {
                        $id = $data['user_id'];
                        $full_name = getNameOfUser($id, $conn);

                        echo "<tr>";
                        echo "<td style='text-align: center;'>$id</td>";
                        echo "<td>{$data['student_id']}</td>";
                        echo "<td>{$data['username']}</td>";
                        echo "<td>{$full_name}</td>";
                        echo "<td>{$data['contact']}</td>";
                        echo "<td style='text-align: center;'><a class='warning-button' href='edit.php?id=" . encryptData($id) . "'><i class='ri-edit-box-line'></i>
                          <a class='danger-button' href='delete.php?action=pending&id=" . encryptData($id) . "'><i class='ri-delete-bin-line'></i></td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>

        <br>

        <div style="text-align: center;">
            <?php if (!empty($successMsg)): ?>
                <span class="success-message" color="white"><?php echo $successMsg; ?></span>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <span class="error-message"><?php echo $errorMsg; ?></span>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>