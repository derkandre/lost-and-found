<?php

// Using this instead of include helped fixed:
// Fatal error: Cannot redeclare encryptData() 
include_once '../security/encryption.php';

function getNameOfUser($id, $conn) {
    $query = "SELECT first_name, middle_name, last_name FROM students WHERE user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        if ($data['middle_name'] == "N/A")
            return "{$data['first_name']} {$data['last_name']}";

        return "{$data['first_name']} {$data['middle_name']} {$data['last_name']}";
    }

    return "N/A";
}

function getUserID($username, $conn) {
    $query = "SELECT user_id FROM users WHERE username = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        return "{$data['user_id']}";
    }
}

function getStudentID($id, $conn) {
    $query = "SELECT student_id FROM students WHERE user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        return $data["student_id"];
    }

    return "N/A";
}

function getUsername($id, $conn) {
    $query = "SELECT username FROM users WHERE user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        return $data["username"];
    }

    return "N/A";
}

function getUserEmail($id, $conn) {
    $query = "SELECT email FROM students WHERE user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        return $data["email"];
    }

    return "N/A";
}

?>