<?php

require '../database/connection.php';
include '../security/encryption.php';

$successMsg = $errorMsg = "";

session_start();

// Using session fixed where the logout message doesn't clear afterwards.
// Previously, I only used the variable but it had a problem, yeah variables reset
// if I force refresh the page without the ?ref parameter in the URL.
if (isset($_SESSION['success_msg'])) {
    $successMsg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
    $errorMsg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $successMsg = "";

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
            header("Location: index.php");
        } else {
            $_SESSION['error_msg'] = "Incorrect username or password!";
            header("Location: index.php");
        }
    } else {
        $_SESSION['error_msg'] = "Incorrect username or password!";
        // This fixed my problem where the message does not show the first time I try to enter 
        // an incorrect username/password. The message won't take effect unless reloaded due
        // to how session works.
        header("Location: login.php");
    }
}

if (isset($_GET["ref"]) && $_GET["ref"] === "logout") {
    $_SESSION['success_msg'] = "You have been logged out successfully!";
    header("Location: login.php");
    exit;
}
?>

<html>

<head>
    <title>ICTS LostTrack | Log In</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>

<body class="non-admin-body">
    <div class="container-solid">
        <form action="" method="POST">
            <div class="logo-title">
                <img src="../assets/images/ccsitlogo.png" width="50px" height="50px">
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
                <a class="form-additional-links" href="signup.php">Register</a>
            </p>
            <?php if (!empty($successMsg)): ?>
                <span class="success-message"><?php echo $successMsg; ?></span>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <span class="error-message"><?php echo $errorMsg; ?></span>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>