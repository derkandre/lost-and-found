<?php

function doesUsernameExist($conn, $username)
{
    $query = "SELECT * FROM users WHERE username = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result->num_rows > 0) ? true : false;
}

function doesEmailExist($conn, $email)
{
    $query = "SELECT * FROM students WHERE email = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result->num_rows > 0) ? true : false;
}

function doesStudentIDExist($conn, $studentID)
{
    $query = "SELECT * FROM students WHERE student_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $studentID);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result->num_rows > 0) ? true : false;
}

function validateAllFieldInputs($username, $password, $confirm_password, $role, $student_id, $first_name, $middle_name, $last_name, $email, $contact)
{
    $errors = [];

    // For all fields to not be empty
    if (
        empty($username) || empty($password) || $role == "none" || empty($student_id) ||
        empty($first_name) || empty($last_name) ||
        empty($email) || empty($contact)
    ) {
        $errors[] = "[ALL] All fields are required. Please leave no field blank.";
    }

    // USERNAME validation
    if (strlen($username) < 4 || !preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        $errors[] = "<b>[USERNAME]</b> Username must only be alphanumeric and be at least 4 characters long.";
    }

    // PASSWORD validation
    if (strlen($password) < 8) {
        $errors[] = "<b>[PASSWORD]</b> Password must be at least 8 characters long!";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "<b>[PASSWORD]</b> Password must contain at least 1 lowercase letter!";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "<b>[PASSWORD]</b> Password must contain at least 1 uppercase letter!";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "<b>[PASSWORD]</b> Password must contain at least 1 number!";
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "<b>[PASSWORD]</b> Password must contain at least 1 special character.";
    }

    if ($password != $confirm_password) {
        $errors[] = "<b>[PASSWORD]</b> Passwords do not match.";
    }

    // ROLE validation
    if (strtolower($role) != "user" && strtolower($role) != "admin") {
        $errors[] = "<b>[ROLE]</b> The role must only either be User or Admin.";
    }

    // STUDENT ID validation
    if (!preg_match("/\b\d{7}-(1|2)\b/", $student_id)) {
        $errors[] = "<b>[STUDENT ID]</b> Student ID must follow official format: XXXXXXX-S where X is 0-9 and S is either 1 or 2.";
    }

    // NAME validation
    if (!preg_match('/^[a-zA-Z\s\-]{2,50}$/', $first_name)) {
        $errors[] = "<b>[FIRST NAME]</b> First name must contain only letters, spaces, and hyphens.";
    }
    if (!empty($middle_name)) {
        if (!preg_match('/^[a-zA-Z\s\-]{1,50}$/', $middle_name)) {
            $errors[] = "<b>[MIDDLE NAME]</b> Middle name must contain only letters, spaces, and hyphens.";
        }
    } else {
        $middle_name = "";
    }
    if (!preg_match('/^[a-zA-Z\s\-]{2,50}$/', $last_name)) {
        $errors[] = "<b>[LAST NAME]</b> Last name must contain only letters, spaces, and hyphens.";
    }

    // EMAIL validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "<b>[EMAIL]</b> Please enter a valid email address.";
    }

    // CONTACT NO. validation
    if (!preg_match('/^(\+639\d{9}|09\d{9})$/', $contact)) {
        $errors[] = "<b>[CONTACT]</b> Contact number must follow Philippine format: +639XXXXXXXXX or 09XXXXXXXXX.";
    }

    if (!empty($errors)) {
        $_SESSION['error_msg'] = $errors;
        header("Location: new-account.php");
        exit();
    }
}

function validateEditedInputs($username, $password, $confirm_password, $role, $student_id, $first_name, $middle_name, $last_name, $email, $contact)
{
    $errors = [];

    // USERNAME validation
    if (strlen($username) < 4 || !preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        $errors[] = "<b>[USERNAME]</b> Username must only be alphanumeric and be at least 4 characters long.";
    }

    // PASSWORD validation
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = "<b>[PASSWORD]</b> Password must be at least 8 characters long!";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "<b>[PASSWORD]</b> Password must contain at least 1 lowercase letter!";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "<b>[PASSWORD]</b> Password must contain at least 1 uppercase letter!";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "<b>[PASSWORD]</b> Password must contain at least 1 number!";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "<b>[PASSWORD]</b> Password must contain at least 1 special character.";
        }

        if ($password != $confirm_password) {
            $errors[] = "<b>[PASSWORD]</b> Passwords do not match.";
        }
    }
    
    // ROLE validation
    if (strtolower($role) != "user" && strtolower($role) != "admin") {
        $errors[] = "<b>[ROLE]</b> The role must only either be User or Admin.";
    }

    // STUDENT ID validation
    if (!preg_match("/\b\d{7}-(1|2)\b/", $student_id)) {
        $errors[] = "<b>[STUDENT ID]</b> Student ID must follow official format: XXXXXXX-S where X is 0-9 and S is either 1 or 2.";
    }

    // NAME validation
    if (!preg_match('/^[a-zA-Z\s\-]{2,50}$/', $first_name)) {
        $errors[] = "<b>[FIRST NAME]</b> First name must contain only letters, spaces, and hyphens.";
    }
    if (!empty($middle_name)) {
        if (!preg_match('/^[a-zA-Z\s\-]{1,50}$/', $middle_name)) {
            $errors[] = "<b>[MIDDLE NAME]</b> Middle name must contain only letters, spaces, and hyphens.";
        }
    } else {
        $middle_name = "";
    }
    if (!preg_match('/^[a-zA-Z\s\-]{2,50}$/', $last_name)) {
        $errors[] = "<b>[LAST NAME]</b> Last name must contain only letters, spaces, and hyphens.";
    }

    // EMAIL validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "<b>[EMAIL]</b> Please enter a valid email address.";
    }

    // CONTACT NO. validation
    if (!preg_match('/^(\+639\d{9}|09\d{9})$/', $contact)) {
        $errors[] = "<b>[CONTACT]</b> Contact number must follow Philippine format: +639XXXXXXXXX or 09XXXXXXXXX.";
    }

    if (!empty($errors)) {
        $_SESSION['error_msg'] = $errors;
        if (isset($_GET['id'])) {
            header("Location: edit.php?id=" . $_GET['id']);
        } else {
            header("Location: edit.php");
        }
        exit();
    }
}

?>