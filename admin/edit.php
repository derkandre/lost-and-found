<?php
require '../database/connection.php';
include '../security/encryption.php';
include 'validations.php';
include 'session-details.php';

session_start();

if ((!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"])) || $_SESSION["user_role"] != "Admin") {
    header("Location: ../error/401.php?ref=login&role=admin");
    exit();
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

if (isset($_GET["id"])) {
    $encryptedId = $_GET["id"];
    $decryptedId = decryptData($encryptedId);

    $query = "SELECT u.user_id, u.username, u.role, s.student_id, s.first_name, s.middle_name, s.last_name, s.email, s.contact
              FROM users u 
              JOIN students s ON u.user_id = s.user_id
              WHERE u.user_id = ? AND s.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $decryptedId, $decryptedId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $userID = $data["user_id"];
        $username = $data["username"];
        $first_name = $data["first_name"];
        $middle_name = $data["middle_name"];
        $last_name = $data["last_name"];
        $role = $data["role"];
        $student_id = $data["student_id"];
        $email = $data["email"];
        $contact = $data["contact"];
    } else {
        $_SESSION["error_msg"] = "Could not load student information or no records found.";
        header("Location: new-account.php");
        exit();
    }
    if (empty($student_id)) {
        $_SESSION["error_msg"] = "Invalid or corrupted ID. Please try again or contact administrator.";
        header("Location: new-account.php");
        exit();
    }
} else {
    $_SESSION["error_msg"] = "Invalid or corrupted ID. Please try again or contact administrator.";
    header("Location: new-account.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_GET["id"])) {
        $userID = decryptData($_GET["id"]);
    } elseif (isset($_POST["id"])) {
        $userID = decryptData($_POST["id"]);
    }

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm-password"]);
    $role = trim($_POST["role"]);
    $studentID = trim($_POST["student-id"]);
    $first_name = trim($_POST["first-name"]);
    $middle_name = trim($_POST["middle-name"]);
    $last_name = trim($_POST["last-name"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);

    $first_name = ucwords(strtolower($first_name));
    $last_name = ucwords(strtolower($last_name));

    $oldUsername = trim(getUsername($userID, $conn));
    $oldStudentID = trim(getStudentID($userID, $conn));
    $oldEmail = trim(getUserEmail($userID, $conn));

    $_SESSION["edit_username_input"] = $username;
    $_SESSION["edit_role_input"] = $role;
    $_SESSION["edit_student_id_input"] = $studentID;
    $_SESSION["edit_first_name_input"] = $first_name;
    $_SESSION["edit_middle_name_input"] = $middle_name;
    $_SESSION["edit_last_name_input"] = $last_name;
    $_SESSION["edit_email_input"] = $email;
    $_SESSION["edit_contact_input"] = $contact;

    // Fixed the issue where this still runs even tho the username remains unchanged
    // simply because I decrypted the ID in the function as it's already decrypted pala dri.
    if ($username != $oldUsername) {
        if (doesUsernameExist($conn, $username)) {
            $_SESSION['error_msg'] = "Username is already taken.";
            header("Location: edit.php?id=" . encryptData($userID));
            exit();
        }
    }
    if ($studentID != $oldStudentID) {
        if (doesStudentIDExist($conn, $studentID)) {
            $_SESSION['error_msg'] = "Student ID is already associated with an account.";
            header("Location: edit.php?id=" . encryptData($userID));
            exit();
        }
    }
    if ($email != $oldEmail) {
        if (doesEmailExist($conn, $email)) {
            $_SESSION['error_msg'] = "Email is already registered with an account.";
            header("Location: edit.php?id=" . encryptData($userID));
            exit();
        }
    }

    validateEditedInputs($username, $password, $confirm_password, $role, $student_id, $first_name, $middle_name, $last_name, $email, $contact);

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $updateUserQuery = "UPDATE users SET username = ?, password = ?, role = ? WHERE user_id = ?";
        $updateUserStmt = $conn->prepare($updateUserQuery);
        $updateUserStmt->bind_param("sssi", $username, $hashedPassword, $role, $userID);
    } else {
        $updateUserQuery = "UPDATE users SET username = ?, role = ? WHERE user_id = ?";
        $updateUserStmt = $conn->prepare($updateUserQuery);
        $updateUserStmt->bind_param("ssi", $username, $role, $userID);
    }

    if ($updateUserStmt->execute()) {
        $updateStudentQuery = "UPDATE students SET student_id = ?, first_name = ?, middle_name = ?, last_name = ?, email = ?, contact = ? WHERE user_id = ?";
        $updateStudentStmt = $conn->prepare($updateStudentQuery);
        $updateStudentStmt->bind_param("ssssssi", $studentID, $first_name, $middle_name, $last_name, $email, $contact, $userID);

        if ($updateStudentStmt->execute()) {
            if ($updateStudentStmt->affected_rows > 0) {
                $_SESSION["success_msg"] = "User credentials and student details updated successfully!";
            } else {
                $_SESSION["success_msg"] = "User credentials updated but no changes detected in student details.";
            }

            unset($_SESSION["edit_username_input"]);
            unset($_SESSION["edit_role_input"]);
            unset($_SESSION["edit_student_id_input"]);
            unset($_SESSION["edit_first_name_input"]);
            unset($_SESSION["edit_middle_name_input"]);
            unset($_SESSION["edit_last_name_input"]);
            unset($_SESSION["edit_email_input"]);
            unset($_SESSION["edit_contact_input"]);
        } else {
            $_SESSION["error_msg"] = "Student update failed: " . $updateStudentStmt->error;
        }
    } else {
        $_SESSION["error_msg"] = "User update failed: " . $updateUserStmt->error;
    }

    header("Location: edit.php?id=" . encryptData($userID));
    exit();
}
?>

<html>

<head>
    <title>LostTrack | Edit User</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="non-admin-body">
    <?php require 'sidebar.php'; ?>
    <div class="main-content" style="background-color: #fff; border-radius: 8px;">
        <form action="edit.php?id=<?php echo $_GET['id']; ?>" method="POST">
            <div class="logo-title">
                <img src="../assets/images/ccsitlogo.png" width="50px" height="50px">
                <h2>EDIT</h2>
            </div>

            <hr>

            <label>Credentials</label>
            <div class="grouped-inputs">
                <div>
                    <label for="username" style="display: block; margin-bottom: 5px;">Username</label>
                    <div class="input-container">
                        <i class="ri-user-fill input-icon"></i>
                        <input type="text" id="username" name="username" placeholder="Username"
                            value="<?php echo htmlspecialchars(isset($_SESSION['edit_username_input']) ? $_SESSION['edit_username_input'] : $username); ?>"
                            maxlength="32">
                    </div>
                </div>
                <div>
                    <label for="password" style="display: block; margin-bottom: 5px;">Password</label>
                    <div class="input-container">
                        <i class="ri-lock-password-fill input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Password" maxlength="32">
                    </div>
                </div>
                <div>
                    <label for="confirm-password" style="display: block; margin-bottom: 5px;">Confirm Password</label>
                    <div class="input-container">
                        <i class="ri-lock-password-fill input-icon"></i>
                        <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password" maxlength="32">
                    </div>
                </div>
            </div>

            <div>
                <label for="role" style="display: block; margin-bottom: 5px;">Role</label>
                <div class="input-container">
                    <i class="ri-shield-fill input-icon"></i>
                    <?php $selected_role = isset($_SESSION['edit_role_input']) ? $_SESSION['edit_role_input'] : $role; ?>
                    <select style="height: 46px; padding-left: 40px;" id="role" name="role">
                        <option value="none" disabled hidden <?php if ($selected_role == "none" || empty($selected_role))
                            echo "selected"; ?>>Select Role</option>
                        <option value="user" <?php if ($selected_role == "user")
                            echo "selected"; ?>>User</option>
                        <option value="admin" <?php if ($selected_role == "admin")
                            echo "selected"; ?>>Admin</option>
                    </select>
                </div>
            </div>

            <hr>

            <div>
                <label for="student-id" style="display: block; margin-bottom: 5px;">Student ID</label>
                <div class="input-container">
                    <i class="ri-id-card-line input-icon"></i>
                    <input type="text" id="student-id" name="student-id" placeholder="ID Number"
                        value="<?php echo htmlspecialchars(isset($_SESSION['edit_student_id_input']) ? $_SESSION['edit_student_id_input'] : $student_id); ?>"
                       >
                </div>
            </div>

            <label>Full Name</label>
            <div class="grouped-inputs">
                <div>
                    <label for="first-name" style="display: block; margin-bottom: 5px;">First Name</label>
                    <div class="input-container">
                        <i class="ri-user-smile-line input-icon"></i>
                        <input type="text" id="first-name" name="first-name"
                            value="<?php echo htmlspecialchars(isset($_SESSION['edit_first_name_input']) ? $_SESSION['edit_first_name_input'] : $first_name); ?>"
                            placeholder="First">
                    </div>
                </div>
                <div>
                    <label for="middle-name" style="display: block; margin-bottom: 5px;">Middle Name</label>
                    <div class="input-container">
                        <i class="ri-user-smile-line input-icon"></i>
                        <input type="text" id="middle-name" name="middle-name"
                            value="<?php echo htmlspecialchars(isset($_SESSION['edit_middle_name_input']) ? $_SESSION['edit_middle_name_input'] : $middle_name); ?>"
                            placeholder="Middle">
                    </div>
                </div>
                <div>
                    <label for="last-name" style="display: block; margin-bottom: 5px;">Last Name</label>
                    <div class="input-container">
                        <i class="ri-user-smile-line input-icon"></i>
                        <input type="text" id="last-name" name="last-name"
                            value="<?php echo htmlspecialchars(isset($_SESSION['edit_last_name_input']) ? $_SESSION['edit_last_name_input'] : $last_name); ?>"
                            placeholder="Last">
                    </div>
                </div>
            </div>

            <label>Contact information</label>
            <div class="grouped-inputs">
                <div>
                    <label for="email" style="display: block; margin-bottom: 5px;">Email</label>
                    <div class="input-container">
                        <i class="ri-mail-line input-icon"></i>
                        <input type="text" id="email" name="email"
                            value="<?php echo htmlspecialchars(isset($_SESSION['edit_email_input']) ? $_SESSION['edit_email_input'] : $email); ?>"
                            placeholder="Email">
                    </div>
                </div>
                <div>
                    <label for="contact" style="display: block; margin-bottom: 5px;">Contact Number</label>
                    <div class="input-container">
                        <i class="ri-phone-line input-icon"></i>
                        <input type="text" id="contact" name="contact"
                            value="<?php echo htmlspecialchars(isset($_SESSION['edit_contact_input']) ? $_SESSION['edit_contact_input'] : $contact); ?>"
                            placeholder="Contact Number">
                    </div>
                </div>
            </div>

            <div style="text-align: center;">
                <?php if (!empty($successMsg)): ?>
                    <span class="success-message"><?php echo htmlspecialchars($successMsg); ?></span>
                <?php endif; ?>

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

            <button type="submit">Submit</button>
        </form>
    </div>
</body>

</html>