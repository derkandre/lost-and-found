<?php
require '../database/connection.php';
include '../security/encryption.php';

if (!isset($_GET['id'])) {
    header('Location: accounts.php');
    exit();
}

$id = "";
if (isset($_GET['id'])) {
    $id = decryptData($_GET['id']);
} 

if (isset($_GET["action"])) {
    if ($_GET["action"] === "pending") {
        $encrypted_id = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');
        
        echo "<script>
                if (confirm('Are you sure you want to delete this user?')) {
                    window.location.href = 'delete.php?action=confirm&id=" . $encrypted_id . "';
                } else {
                    window.history.back();
                }
            </script>";
    } elseif ($_GET["action"] === "confirm") {
        $studentQuery = "DELETE FROM students WHERE user_id = ?";
        $studentStmt = $conn->prepare($studentQuery);
        $studentStmt->bind_param("i", $id);
        $studentStmt->execute();
        
        $userQuery = "DELETE FROM users WHERE user_id = ?";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("i", $id);
        $userStmt->execute();
        
        if ($studentStmt->affected_rows >= 0 && $userStmt->affected_rows > 0) {
            header("Location: accounts.php?action=delete&result=success");
            exit();
        } else {
            echo "Error deleting user with ID: " . htmlspecialchars($id) . "<br>The ID may have become invalid or corrupted.<br>Full Error Details: " 
            . $studentStmt->error AND $userStmt->error;
        }
    } else {
        echo "Invalid action specified.";
    }
} else {
    echo "No action specified.";
}
?>