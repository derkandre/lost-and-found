<?php

require_once 'database/connection.php';
include_once 'security/encryption.php';

$successMsg = $errorMsg = $warningMsg = "";

session_start();

if (isset($_SESSION['success_msg'])) {
    $successMsg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
    $errorMsg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

if (isset($_SESSION['warning_msg'])) {
    $warningMsg = $_SESSION['warning_msg'];
    unset($_SESSION['warning_msg']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $successMsg = "";
    $warningMsg = "";
    $errorMsg = "";

    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "SELECT * FROM users WHERE username = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $passwordMatch = password_verify($password, $data["password"]);

        if ($passwordMatch) {
            $_SESSION["user_id"] = encryptData($data["user_id"]);
            $userRole = $_SESSION["user_role"] = $data["role"];
            $verifiedStatus = $_SESSION["verified_status"] = $data["status"];

            if ($verifiedStatus == "Unverified") {
                unset($_SESSION["user_id"]);
                unset($_SESSION["user_role"]);
                unset($_SESSION["verified_status"]);

                header("Location: login.php?status=unverified");
                exit();
            } else if ($userRole == "Admin") {
                header("Location: admin/");
                exit();
            } else {
                header("Location: dashboard/");
                exit();
            }
        } else {
            $_SESSION['error_msg'] = "Incorrect username or password!";
            header("Location: login.php");
        }
    } else {
        $_SESSION['error_msg'] = "Incorrect username or password!";
        header("Location: login.php");
    }
}

if (isset($_GET["status"]) && $_GET["status"] === "unverified") {
    $warningMsg = "Your account is currently <b>unverified</b>. You will recieve an email once the admin has verified and approved your registration. Kindly check your email regularly.";
}

if (isset($_GET["ref"]) && $_GET["ref"] === "logout") {
    $successMsg = "You have been logged out successfully!";
}

?>

<html>

<head>
    <title>ICTS LostTrack | Log In</title>
    <link rel="stylesheet" href="/styles/style.css">
    <link rel="stylesheet" href="/styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>

<body class="non-admin-body">
    <div class="container-solid">
        <form action="" method="POST">
            <div class="logo-title">
                <img src="/assets/images/ccsitlogo.png" width="50px" height="50px">
                <h2>LOGIN</h2>
            </div>
            <hr>
            <label for="username">Username</label>
            <div class="input-container">
                <i class="ri-user-fill input-icon"></i>
                <input type="text" id="username" name="username" maxlength="32" required>
            </div>
            <label for="password">Password</label>
            <div class="input-container">
                <i class="ri-lock-password-fill input-icon"></i>
                <input type="password" id="password" name="password" maxlength="32" required>
            </div>
            <button type="submit">Login</button>
            <p class="form-additional-links"><a class="form-additional-links" href="../error/401.php?ref=login">Forgot
                    Password</a> |
                <a class="form-additional-links" href="register.php">Register</a>
            </p>
            <?php if (!empty($successMsg)): ?>
                <span class="success-message"><?php echo $successMsg; ?></span>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <span class="error-message"><?php echo $errorMsg; ?></span>
            <?php endif; ?>

            <?php if (!empty($warningMsg)): ?>
                <span class="warning-message"><?php echo $warningMsg; ?></span>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>