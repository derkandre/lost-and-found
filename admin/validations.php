<?php

function doesUsernameExist($conn, $username) {
    $query = "SELECT * FROM users WHERE username = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return ($result->num_rows > 0) ? true : false;
}

function doesEmailExist($conn, $email) {
    $query = "SELECT * FROM students WHERE email = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return ($result->num_rows > 0) ? true : false;
}

?>