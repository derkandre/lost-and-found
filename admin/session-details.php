<?php

require '../database/connection.php';

// Using this instead of include helped fixed:
// Fatal error: Cannot redeclare encryptData() 
include_once '../security/encryption.php';

function getNameOfUser($id) {
    global $conn;

    $id = decryptData($id);

    $query = "SELECT * FROM students WHERE user_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        return "{$data['first_name']} {$data['middle_name']} {$data['last_name']}";
    }

    return "null";
}

?>