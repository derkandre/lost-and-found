<?php

require '../database/connection.php';
include '../security/encryption.php';
include '../admin/session-details.php';

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "User") {
    header("Location: ../error/401.php?ref=login&role=user");
    exit();
}

$userId = decryptData($_SESSION["user_id"]);

if (!isset($_GET['type']) || $_GET['type'] !== 'claim') {
    echo "Invalid action type.";
    exit();
}

$itemId    = isset($_GET['id']) ? decryptData($_GET['id']) : "";
$claimerId = isset($_GET['claimer']) ? decryptData($_GET['claimer']) : "";
$actionType = isset($_GET['action']) ? $_GET['action'] : ""; 
$mode       = isset($_GET['mode']) ? $_GET['mode'] : "pending"; 

// Determine the return page based on "source" parameter (default my-reports.php)
$source = isset($_GET['source']) ? $_GET['source'] : "my-reports";
$returnPage = ($source === "listings") ? "listings.php" : "my-reports.php";

if ($mode === "pending") {
    $encrypted_id = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');
    $encrypted_claimer = htmlspecialchars($_GET['claimer'], ENT_QUOTES, 'UTF-8');
    echo "<script>
            if (confirm('Are you sure you want to " . $actionType . " this claim?')) {
                window.location.href = 'action.php?type=claim&mode=confirm&action=" . $actionType . "&id=" . $encrypted_id . "&claimer=" . $encrypted_claimer . "&source=" . $source . "';
            } else {
                window.history.back();
            }
          </script>";
    exit();
} elseif ($mode === "confirm") {

    if ($actionType === "submit") {
        $verifyQuery = "SELECT reported_by, item_name FROM items WHERE item_id = ? AND status = 'Found'";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->bind_param("i", $itemId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
    
        if ($verifyResult->num_rows > 0) {
            $row = $verifyResult->fetch_assoc();
            $itemName = $row['item_name'];
    
            if ($row['reported_by'] == $userId) {
                $_SESSION['error_msg'] = "You cannot claim an item you reported.";
            } else {
                $updateQuery = "UPDATE items SET status = 'Pending', claimed_by = ?, date_claimed = NOW() WHERE item_id = ? AND status = 'Found'";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("ii", $userId, $itemId);
    
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $_SESSION['success_msg'] = "Claim request submitted for '" . htmlspecialchars($itemName) . "'. The reporter will be notified to verify your claim.";
                } else {
                    $_SESSION['error_msg'] = "Unable to submit claim request.";
                }
                $stmt->close();
            }
        } else {
            $_SESSION['error_msg'] = "Item is no longer available for claim or does not exist.";
        }
        $verifyStmt->close();
    } elseif ($actionType === "approve") {
        $verifyQuery = "SELECT reported_by, item_name FROM items WHERE item_id = ? AND reported_by = ? AND status = 'Pending'";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->bind_param("ii", $itemId, $userId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();

        if ($verifyResult->num_rows > 0) {
            $row = $verifyResult->fetch_assoc();
            $itemName = $row['item_name'];

            $updateQuery = "UPDATE items SET status = 'Claimed' WHERE item_id = ? AND status = 'Pending'";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $itemId);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $_SESSION['success_msg'] = "Claim for '" . htmlspecialchars($itemName) . "' approved successfully.";
            } else {
                $_SESSION['error_msg'] = "Unable to approve the claim.";
            }
            $stmt->close();
        } else {
            $_SESSION['error_msg'] = "Verification failed or you are not authorized.";
        }
        $verifyStmt->close();
    } elseif ($actionType === "reject") {
        $verifyQuery = "SELECT reported_by, item_name FROM items WHERE item_id = ? AND reported_by = ? AND status = 'Pending'";
        $verifyStmt = $conn->prepare($verifyQuery);
        $verifyStmt->bind_param("ii", $itemId, $userId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();

        if ($verifyResult->num_rows > 0) {
            $row = $verifyResult->fetch_assoc();
            $itemName = $row['item_name'];

            $updateQuery = "UPDATE items SET status = 'Found', claimed_by = NULL, date_claimed = NULL WHERE item_id = ? AND status = 'Pending'";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $itemId);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $_SESSION['success_msg'] = "Claim for '" . htmlspecialchars($itemName) . "' rejected successfully.";
            } else {
                $_SESSION['error_msg'] = "Unable to reject the claim.";
            }
            $stmt->close();
        } else {
            $_SESSION['error_msg'] = "Verification failed or you are not authorized.";
        }
        $verifyStmt->close();
    } else {
        $_SESSION['error_msg'] = "Invalid action specified.";
    }

    header("Location: " . $returnPage);
    exit();
} else {
    echo "Invalid mode specified.";
    exit();
}

?>