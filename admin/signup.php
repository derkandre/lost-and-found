<?php

require '../database/connection.php';
include '../security/encryption.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $query = "INSERT INTO users(username, password, role) VALUES(?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $username, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Registration failed.";
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

<body class="non-admin-body">
    <div class="container-solid">
        <form action="" method="POST">
            <div class="logo-title">
                <img src="../assets/images/ccsitlogo.png" width="50px" height="50px">
                <h2>REGISTER</h2>
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
            <label for="username">Role</label>
            <div class="input-container">
                <i class="ri-shield-fill input-icon"></i>
                <select name="role" id="role" style="padding-left: 40px;">
                    <!-- <option value="admin">Admin</option> -->
                    <option value="user">User</option>
                </select>
            </div>
            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>