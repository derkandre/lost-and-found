<?php

require '../database/connection.php';
include '../security/encryption.php';

$successMsg = $errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
            session_start();

            $_SESSION["user_id"] = encryptData($data["user_id"]);
            header("Location: index.php");
        } else {
            $errorMsg = "Incorrect username or password!";
        }
    }
}

if (isset($_GET["ref"])) {
    if ($_GET["ref"] === "logout") {
        $successMsg = "You have been logged out successfully!";
    }
}

?>

<html>

<head>
    <title>ICTS LostTrack | Log In</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>

<body>
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