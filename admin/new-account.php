<?php
require '../database/connection.php';
include '../security/encryption.php';

session_start();
$successMsg = $errorMsg = "";

if (isset($_SESSION['success_msg'])) {
    $successMsg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
    $errorMsg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    $student_id = $_POST["student-id"];
    $first_name = $_POST["first-name"];
    $middle_name = $_POST["middle-name"];
    $last_name = $_POST["last-name"];
    $email = $_POST["email"];
    $contact = $_POST["contact"];

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $userQuery = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("sss", $username, $hashedPassword, $role);

    if ($userStmt->execute()) {
        // An easy way instead of manually getting or selecting the ID, unlike in my WPF applications I have to manually
        // create a new function just to get the ID first before doing an insert query again.
        // Ref: https://www.w3schools.com/php/php_mysql_insert_lastid.asp
        $user_id = $conn->insert_id;

        $studentQuery = "INSERT INTO students (student_id, first_name, middle_name, last_name, email, contact, user_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
        $studentStmt = $conn->prepare($studentQuery);
        $studentStmt->bind_param("ssssssi", $student_id, $first_name, $middle_name, $last_name, $email, $contact, $user_id);

        if ($studentStmt->execute()) {
            $_SESSION["success_msg"] = "User has been registered successfully!";
        } else {
            $_SESSION["success_msg"] = "Failed to register user.";
        }

        header("Location: new-account.php");

    } else {
        $_SESSION["success_msg"] = "Failed to register user.";
        header("Location: new-account.php");
    }
}
?>

<html>

<head>
    <title>LostTrack | Register Account</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="non-admin-body">
    <?php require 'sidebar.php' ?>

    <div class="main-content" style="background-color: #fff; border-radius: 8px;">
        <form action="" method="POST">
            <div class="logo-title">
                <img src="../assets/images/ccsitlogo.png" width="50px" height="50px">
                <h2>REGISTER</h2>
            </div>
            <hr>

            <label>Credentials</label>
            <div class="grouped-inputs">
                <div class="input-container">
                    <i class="ri-user-fill input-icon"></i>
                    <input type="text" name="username" placeholder="Username" maxlength="32" required>
                </div>

                <div class="input-container">
                    <i class="ri-lock-password-fill input-icon"></i>
                    <input type="password" name="password" placeholder="Password" maxlength="32" required>
                </div>

                <div class="input-container">
                    <i class="ri-shield-fill input-icon"></i>
                    <select style="height: 46px; padding-left: 40px;" name="role">
                        <option value="none" disabled selected hidden>Select Role</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <hr>

            <label for="student_id">Student ID</label>
            <div class="input-container">
                <i class="ri-id-card-line input-icon"></i>
                <input type="text" name="student-id" placeholder="ID Number" required>
            </div>

            <label>Full Name</label>
            <div class="grouped-inputs">
                <div class="input-container">
                    <i class="ri-user-smile-line input-icon"></i>
                    <input type="text" name="first-name" placeholder="First" required>
                </div>
                <div class="input-container">
                    <i class="ri-user-smile-line input-icon"></i>
                    <input type="text" name="middle-name" placeholder="Middle" required>
                </div>
                <div class="input-container">
                    <i class="ri-user-smile-line input-icon"></i>
                    <input type="text" name="last-name" placeholder="Last" required>
                </div>
            </div>

            <label>Contact Information</label>
            <div class="grouped-inputs">
                <div class="input-container">
                    <i class="ri-mail-line input-icon"></i>
                    <input type="text" name="email" placeholder="Email" required>
                </div>

                <div class="input-container">
                    <i class="ri-phone-line input-icon"></i>
                    <input type="text" name="contact" placeholder="Contact Number" required>
                </div>
            </div>
            
            <div style="text-align: center;">
                <?php if (!empty($successMsg)): ?>
                    <span class="success-message"><?php echo $successMsg; ?></span>
                <?php endif; ?>

                <?php if (!empty($errorMsg)): ?>
                    <span class="error-message"><?php echo $errorMsg; ?></span>
                <?php endif; ?>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>