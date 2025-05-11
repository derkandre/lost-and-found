<?php
require 'database/connection.php';
include 'admin/validations.php';

session_start();

$successMsg = $warningMsg = $errorMsg = "";

if (isset($_SESSION['success_msg'])) {
    $successMsg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['warning_message'])) {
    $errorMsg = $_SESSION['warning_message'];
    unset($_SESSION['warning_message']);
}

if (isset($_SESSION['error_msg'])) {
    $errorMsg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm-password"]);
    $role = "User";
    $student_id = trim($_POST["student-id"]);
    $first_name = trim($_POST["first-name"]);
    $middle_name = trim($_POST["middle-name"]);
    $last_name = trim($_POST["last-name"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);

    $first_name = ucwords(strtolower($first_name));
    $last_name = ucwords(strtolower($last_name));

    $_SESSION["username-input"] = $username;
    $_SESSION["stud-id-input"] = $student_id;
    $_SESSION["first-name-input"] = $first_name;
    $_SESSION["middle-name-input"] = $middle_name;
    $_SESSION["last-name-input"] = $last_name;
    $_SESSION["email-input"] = $email;
    $_SESSION["contact-input"] = $contact;

    if (doesUsernameExist($conn, $username)) {
        $_SESSION['error_msg'] = "Username is already taken.";
        header("Location: register.php");
        exit();
    }

    if (doesStudentIDExist($conn, $student_id)) {
        $_SESSION['error_msg'] = "Student ID is already associated with an account.";
        header("Location: register.php");
        exit();
    }

    if (doesEmailExist($conn, $email)) {
        $_SESSION['error_msg'] = "Email is already registered with an account.";
        header("Location: register.php");
        exit();
    }

    validateAllFieldInputs($username, $password, $confirm_password, $role, $student_id, $first_name, $middle_name, $last_name, $email, $contact);

    $image_path = "";
    $targetDir = "uploads/documents";

    $imageFileType = strtolower(pathinfo($_FILES["supporting-document-image"]["name"], PATHINFO_EXTENSION));

    $sanitizedItemName = preg_replace("/[^a-zA-Z0-9]+/", "", $username);

    $customFileName = $sanitizedItemName . "_" . date("Ymd_His") . "." . $imageFileType;
    $targetFile = $targetDir . $customFileName;

    if (move_uploaded_file($_FILES["supporting-document-image"]["tmp_name"], $targetFile)) {
        $image_path = $targetFile;
    } else {
        $_SESSION["error_msg"] = "There was a problem while uploading the image.";
        header("Location: register.php");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $userQuery = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("sss", $username, $hashedPassword, $role);

    if ($userStmt->execute()) {
        $user_id = $conn->insert_id;

        $studentQuery = "INSERT INTO students (student_id, first_name, middle_name, last_name, email, contact, supporting_document, user_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $studentStmt = $conn->prepare($studentQuery);
        $studentStmt->bind_param("sssssssi", $student_id, $first_name, $middle_name, $last_name, $email, $contact, $image_path, $user_id);

        if ($studentStmt->execute()) {
            unset($_SESSION["username-input"]);
            unset($_SESSION["stud-id-input"]);
            unset($_SESSION["role-input"]);
            unset($_SESSION["first-name-input"]);
            unset($_SESSION["middle-name-input"]);
            unset($_SESSION["last-name-input"]);
            unset($_SESSION["email-input"]);
            unset($_SESSION["contact-input"]);

            $_SESSION["success_msg"] = "User has been registered successfully!";
        } else {
            $deleteUserQuery = "DELETE FROM users WHERE user_id = ?";
            $deleteUserStmt = $conn->prepare($deleteUserQuery);
            $deleteUserStmt->bind_param("i", $user_id);

            if ($deleteUserStmt->execute() && $deleteUserStmt->affected_rows > 0) {
                $_SESSION["error_msg"] = "Registration failed, but system successfully rolled back.";
            } else {
                $_SESSION["error_msg"] = "Registration failed and could not rollback corrupted registration. Please contact administrator.";
            }
        }

        header("Location: register.php");
        exit();
    } else {
        $_SESSION["error_msg"] = "Failed to register user: " . $userStmt->error;
        header(header: "Location: register.php");
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
    <?php require 'sidebar.php'; ?>

    <div class="main-content" style="background-color: #fff; border-radius: 8px;">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="logo-title">
                <img src="../assets/images/ccsitlogo.png" width="50px" height="50px">
                <h2>REGISTER</h2>
            </div>

            <h3>Credentials</h3>
            <hr>
            <div class="grouped-inputs">
                <div>
                    <label for="username" class="form-label">Username</label>
                    <div class="input-container">
                        <i class="ri-user-fill input-icon"></i>
                        <input type="text" id="username" name="username" placeholder="Username" value="<?php if (!empty($_SESSION["username-input"]))
                            echo htmlspecialchars($_SESSION["username-input"]); ?>" maxlength="32" required>
                    </div>
                </div>

                <div>
                    <label for="password" class="form-label">Password</label>
                    <div class="input-container">
                        <i class="ri-lock-password-fill input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Password" maxlength="32"
                            required>
                    </div>
                </div>

                <div>
                    <label for="confirm-password" class="form-label">Confirm Password</label>
                    <div class="input-container">
                        <i class="ri-lock-password-fill input-icon"></i>
                        <input type="password" id="confirm-password" name="confirm-password"
                            placeholder="Confirm Password" maxlength="32" required>
                    </div>
                </div>
            </div>            

            <hr>

            <div>
                <label for="student-id-input" class="form-label">Student ID</label>
                <div class="input-container">
                    <i class="ri-id-card-line input-icon"></i>
                    <input type="text" id="student-id-input" name="student-id" placeholder="ID Number" value="<?php if (!empty($_SESSION["stud-id-input"]))
                        echo htmlspecialchars($_SESSION["stud-id-input"]); ?>" required>
                </div>
            </div>

            <div class="grouped-inputs">
                <div>
                    <label for="first-name" class="form-label">First Name</label>
                    <div class="input-container">
                        <i class="ri-user-smile-line input-icon"></i>
                        <input type="text" id="first-name" name="first-name" value="<?php if (!empty($_SESSION["first-name-input"]))
                            echo htmlspecialchars($_SESSION["first-name-input"]); ?>" placeholder="First" required>
                    </div>
                </div>
                <div>
                    <label for="middle-name" class="form-label">Middle Name</label>
                    <div class="input-container">
                        <i class="ri-user-smile-line input-icon"></i>
                        <input type="text" id="middle-name" name="middle-name" value="<?php if (!empty($_SESSION["middle-name-input"]))
                            echo htmlspecialchars($_SESSION["middle-name-input"]); ?>" placeholder="Middle">
                    </div>
                </div>
                <div>
                    <label for="last-name" class="form-label">Last Name</label>
                    <div class="input-container">
                        <i class="ri-user-smile-line input-icon"></i>
                        <input type="text" id="last-name" name="last-name" value="<?php if (!empty($_SESSION["last-name-input"]))
                            echo htmlspecialchars($_SESSION["last-name-input"]); ?>" placeholder="Last" required>
                    </div>
                </div>
            </div>

            <div class="grouped-inputs">
                <div>
                    <label for="email" class="form-label">Email</label>
                    <div class="input-container">
                        <i class="ri-mail-line input-icon"></i>
                        <input type="text" id="email" name="email" value="<?php if (!empty($_SESSION["email-input"]))
                            echo htmlspecialchars($_SESSION["email-input"]); ?>" placeholder="Email" required>
                    </div>
                </div>

                <div>
                    <label for="contact" class="form-label">Contact Number</label>
                    <div class="input-container">
                        <i class="ri-phone-line input-icon"></i>
                        <input type="text" id="contact" name="contact" value="<?php if (!empty($_SESSION["contact-input"]))
                            echo htmlspecialchars($_SESSION["contact-input"]); ?>" placeholder="Contact Number"
                            required>
                    </div>
                </div>
            </div>
            <div>
                <label for="supporting-document-image" class="form-label">Supporting Document</label>
                <div class="input-container">
                    <i class="ri-image-line input-icon"></i>
                    <input id="supporting-document-image" name="supporting-document-image" type="file" name="supporting-document-image" accept="image/*" required>
                </div>
            </div>

            <span class="input-description">Please upload your Student ID or Official Registration Form (ORF)</span>

            <div style="text-align: center;">
                <div style="text-align: center;">
                    <?php if (!empty($successMsg)) { ?>
                        <span class="success-message"><?php echo htmlspecialchars($successMsg); ?></span>
                    <?php } ?>

                    <?php if (!empty($warningMsg)) { ?>
                        <span class="warning-message"><?php echo $warningMsg; ?></span>
                    <?php } ?>

                    <?php
                    if (!empty($errorMsg)) {
                        if (is_array($errorMsg)) {
                            foreach ($errorMsg as $msg) { ?>
                                <p class="error-message" style="margin-bottom: -32px; text-align: left;">
                                    <?php echo $msg; ?>
                                </p><br>
                            <?php }
                        } else { ?>
                            <span class="error-message"><?php echo htmlspecialchars($errorMsg); ?></span>
                        <?php }
                    }
                    ?>
                </div>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>