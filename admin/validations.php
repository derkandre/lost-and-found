<?php

function doesUsernameExist($conn, $username)
{
    $query = "SELECT * FROM users WHERE username = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result->num_rows > 0) ? true : false;
}

function doesEmailExist($conn, $email)
{
    $query = "SELECT * FROM students WHERE email = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $email);
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

function validateAllFieldInputs($username, $password, $role, $student_id, $first_name, $middle_name, $last_name, $email, $contact)
{
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
        if (!strtoupper($middle_name = "N/A")) {
            $_SESSION['error_msg'] = "Names must contain only letters, spaces, and hyphens";
            header("Location: new-account.php");
            exit();
        }
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
}

function validateEditedInputs($username, $password, $role, $student_id, $first_name, $middle_name, $last_name, $email, $contact)
{
    // For USERNAME validation
    if (strlen($username) < 4 || !preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        $_SESSION['error_msg'] = "Username must only be alphanumeric and be at least 4 characters long";
        header("Location: new-account.php");
        exit();
    }

    // For PASSWORD validation
    if (!empty($password)) {
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
        if (!strtoupper($middle_name = "N/A")) {
            $_SESSION['error_msg'] = "Names must contain only letters, spaces, and hyphens";
            header("Location: new-account.php");
            exit();
        }
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
}

?>