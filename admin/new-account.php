<?php
require '../database/connection.php';
include '../security/encryption.php';
include 'validations.php';

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../error/401.php?ref=login");
}

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
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role = trim(string: $_POST["role"]);
    $student_id = trim($_POST["student-id"]);
    $first_name = trim($_POST["first-name"]);
    $middle_name = trim($_POST["middle-name"]);
    $last_name = trim($_POST["last-name"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);

    $first_name = ucwords(strtolower($first_name));
    $middle_name = ucwords(strtolower($middle_name));
    $last_name = ucwords(strtolower($last_name));

    // I'm still conflicted whether to store emails in all lowercase or remain case-sensitive based on actual input.
    // Either it's only going to be the checking process where the lowercase should be enforced for any unintended duplicates
    // or I'll store the email as lowercase in the database.
    // $email = strtolower($email); - if stored as lowercase
    // strtolower($data["email"]) == $email - if only during checking

    $_SESSION["username-input"] = $username;
    $_SESSION["stud-id-input"] = $student_id;
    $_SESSION["first-name-input"] = $first_name;
    $_SESSION["middle-name-input"] = $middle_name;
    $_SESSION["last-name-input"] = $last_name;
    $_SESSION["email-input"] = $email;
    $_SESSION["contact-input"] = $contact;

    // Start of validations
    if (doesUsernameExist($conn, $username)) {
        $_SESSION['error_msg'] = "Username is already taken.";
        header("Location: new-account.php");
        exit();
    }

    if (doesEmailExist($conn, $email)) {
        $_SESSION['error_msg'] = "Email is already registered with an account.";
        header("Location: new-account.php");
        exit();
    }

    if (doesStudentIDExist($conn, $student_id)) {
        $_SESSION['error_msg'] = "Email is already registered with an account.";
        header("Location: new-account.php");
        exit();
    }
    // For all fields to not be empty
    if (
        empty($username) || empty($password) || $role == "none" || empty($student_id) ||
        empty($first_name) || empty($middle_name) || empty($last_name) ||
        empty($email) || empty($contact)
    ) {
        $_SESSION['error_msg'] = "All fields are required. Please leave no field blank.";
        header("Location: new-account.php");
        exit();
    }

    // For USERNAME validation
    if (strlen($username) < 4 || !preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        $_SESSION['error_msg'] = "Username must only be alphanumeric and be at least 4 characters long";
        header("Location: new-account.php");
        exit();
    }

    // For PASSWORD validation
    if (strlen($password) < 8) {
        $_SESSION['error_msg'] = "Password must be at least 8 characters long!";
        header("Location: new-account.php");
        exit();
    }

    if (!preg_match('/[a-z]/', $password)) {
        $_SESSION['error_msg'] = "Password must contain at least 1 lowercase letter!";
        header("Location: new-account.php");
        exit();
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $_SESSION['error_msg'] = "Password must contain at least 1 uppercase letter!";
        header("Location: new-account.php");
        exit();
    }

    if (!preg_match('/[0-9]/', $password)) {
        $_SESSION['error_msg'] = "Password must contain at least 1 number!";
        header("Location: new-account.php");
        exit();
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $_SESSION['error_msg'] = "Password must contain at least 1 special character";
        header("Location: new-account.php");
        exit();
    }

    // For the STUDENT ID validation
    if (!preg_match("/\b\d{7}-(1|2)\b/", $student_id)) {
        $_SESSION['error_msg'] = "Student ID must follow official format: XXXXXXX-S where X is 0-9 and S is either 1 or 2.";
        header("Location: new-account.php");
        exit();
    }

    // For the NAME validation
    if (
        !preg_match('/^[a-zA-Z\s\-]{2,50}$/', $first_name) ||
        !preg_match('/^[a-zA-Z\s\-]{1,50}$/', $middle_name) ||
        !preg_match('/^[a-zA-Z\s\-]{2,50}$/', $last_name)
    ) {
        $_SESSION['error_msg'] = "Names must contain only letters, spaces, and hyphens";
        header("Location: new-account.php");
        exit();
    }

    // For the EMAIL validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_msg'] = "Please enter a valid email address";
        header("Location: new-account.php");
        exit();
    }

    // For CONTACT NO. validation
    if (!preg_match('/^(\+639\d{9}|09\d{9})$/', $contact)) {
        $_SESSION['error_msg'] = "Contact number must follow Philippine format: +639XXXXXXXXX or 09XXXXXXXXX";
        header("Location: new-account.php");
        exit();
    }

    // For the ROLE, just in case tho, even if the options of the dropdown is only Admin and User
    if (strtolower($role) != "user" && strtolower($role) != "admin") {
        $_SESSION['error_msg'] = "The role must only either be User or Admin.";
        header("Location: new-account.php");
        exit();
    }

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
            unset($_SESSION["username-input"]);
            unset($_SESSION["stud-id-input"]);
            unset($_SESSION["first-name-input"]);
            unset($_SESSION["middle-name-input"]);
            unset($_SESSION["last-name-input"]);
            unset($_SESSION["email-input"]);
            unset($_SESSION["contact-input"]);

            $_SESSION["success_msg"] = "User has been registered successfully!";
        } else {
            // This will remain as is until I can get an A-OK from my instructor to use transactions
            // But this seems like good already but afaik using transactions can simplify this?
            $deleteUserQuery = "DELETE FROM users WHERE user_id = ?";
            $deleteUserStmt = $conn->prepare($deleteUserQuery);
            $deleteUserStmt->bind_param("i", $user_id);

            if ($deleteUserStmt->execute() && $deleteUserStmt->affected_rows > 0) {
                $_SESSION["error_msg"] = "Registration failed, but system successfully rolled back.";
            } else {
                $_SESSION["error_msg"] = "Registration failed and could not rollback corrupted registration. Please contact administrator.";
            }
        }

        header("Location: new-account.php");
        exit();
    } else {
        $_SESSION["error_msg"] = "Failed to register user: " . $userStmt->error;
        header(header: "Location: new-account.php");
        exit();
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
                    <input type="text" name="username" placeholder="Username" value="<?php if (!empty($_SESSION["username-input"]))
                        echo htmlspecialchars($_SESSION["username-input"]); ?>" maxlength="32" required>
                </div>

                <div class="input-container">
                    <i class="ri-lock-password-fill input-icon"></i>
                    <input type="password" name="password" placeholder="Password" maxlength="32" required>
                </div>

                <div class="input-container">
                    <i class="ri-shield-fill input-icon"></i>
                    <select style="height: 46px; padding-left: 40px;" name="role">
                        <option value="none" disabled <?php if (empty($_SESSION["role-input"]))
                            echo "selected"; ?>
                            hidden>Select Role</option>
                        <option value="user" <?php if (!empty($_SESSION["role-input"]) && $_SESSION["role-input"] == "user")
                            echo "selected"; ?>>User</option>
                        <option value="admin" <?php if (!empty($_SESSION["role-input"]) && $_SESSION["role-input"] == "admin")
                            echo "selected"; ?>>Admin</option>
                    </select>
                </div>
            </div>

            <hr>

            <label for="student_id">Student ID</label>
            <div class="input-container">
                <i class="ri-id-card-line input-icon"></i>
                <input type="text" name="student-id" placeholder="ID Number" value="<?php if (!empty($_SESSION["stud-id-input"]))
                    echo htmlspecialchars($_SESSION["stud-id-input"]); ?>" required>
            </div>

            <label>Full Name</label>
            <div class="grouped-inputs">
                <div class="input-container">
                    <i class="ri-user-smile-line input-icon"></i>
                    <input type="text" name="first-name" value="<?php if (!empty($_SESSION["first-name-input"]))
                        echo htmlspecialchars($_SESSION["first-name-input"]); ?>" placeholder="First" required>
                </div>
                <div class="input-container">
                    <i class="ri-user-smile-line input-icon"></i>
                    <input type="text" name="middle-name" value="<?php if (!empty($_SESSION["middle-name-input"]))
                        echo htmlspecialchars($_SESSION["middle-name-input"]); ?>" placeholder="Middle" required>
                </div>
                <div class="input-container">
                    <i class="ri-user-smile-line input-icon"></i>
                    <input type="text" name="last-name" value="<?php if (!empty($_SESSION["last-name-input"]))
                        echo htmlspecialchars($_SESSION["last-name-input"]); ?>" placeholder="Last" required>
                </div>
            </div>

            <label>Contact Information</label>
            <div class="grouped-inputs">
                <div class="input-container">
                    <i class="ri-mail-line input-icon"></i>
                    <input type="text" name="email" value="<?php if (!empty($_SESSION["email-input"]))
                        echo htmlspecialchars($_SESSION["email-input"]); ?>" placeholder="Email" required>
                </div>

                <div class="input-container">
                    <i class="ri-phone-line input-icon"></i>
                    <input type="text" name="contact" value="<?php if (!empty($_SESSION["contact-input"]))
                        echo htmlspecialchars($_SESSION["contact-input"]); ?>" placeholder="Contact Number" required>
                </div>
            </div>

            <div style="text-align: center;">
                <?php if (!empty($successMsg)): ?>
                    <span class="success-message"><?php echo htmlspecialchars($successMsg) ?></span>
                <?php endif; ?>

                <?php if (!empty($errorMsg)): ?>
                    <span class="error-message"><?php echo htmlspecialchars($errorMsg); ?></span>
                <?php endif; ?>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>