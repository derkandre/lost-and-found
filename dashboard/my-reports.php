<?php

require '../database/connection.php';
include '../security/encryption.php';
include '../admin/session-details.php';

session_start();
$_SESSION["active-page"] = "my-reports";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "User") {
    header("Location: ../error/401.php?ref=login&role=user");
    exit();
}

if (isset($_SESSION["verified_status"])) {
    if ($_SESSION["verified_status"] == "Unverified") {
        $_SESSION["warning-message"] = "Your account is currently unverified. You will receive an email once the admin verifies your registration.";
        header("Location: ../login.php?status=unverified");
        exit();
    }
    $_SESSION["verified_status"] = "Verified";
}

$userId = decryptData($_SESSION["user_id"]);

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
    if (isset($_POST['approve_claim']) && isset($_POST['item_id_for_action']) && isset($_POST['claimer_id_for_action'])) {
        $itemId = (int) $_POST['item_id_for_action'];
        $claimerId = (int) $_POST['claimer_id_for_action'];

        $verifyStmt = $conn->prepare("SELECT reported_by, item_name FROM items WHERE item_id = ? AND reported_by = ? AND status = 'Pending'");
        $verifyStmt->bind_param("ii", $itemId, $userId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        if ($verifyResult->num_rows > 0) {
            $row = $verifyResult->fetch_assoc();
            $itemName = $row['item_name'];

            $approveStmt = $conn->prepare("UPDATE items SET status = 'Claimed' WHERE item_id = ? AND status = 'Pending'");
            $approveStmt->bind_param("i", $itemId);
            if ($approveStmt->execute() && $approveStmt->affected_rows > 0) {
                $_SESSION['success_msg'] = "Claim for '" . htmlspecialchars($itemName) . "' approved successfully.";
            } else {
                $_SESSION['error_msg'] = "Unable to approve the claim.";
            }
            $approveStmt->close();
        } else {
            $_SESSION['error_msg'] = "Verification failed or you are not authorized.";
        }
        $verifyStmt->close();
        header("Location: my-reports.php");
        exit();
    }

    if (isset($_POST['reject_claim']) && isset($_POST['item_id_for_action']) && isset($_POST['claimer_id_for_action'])) {
        $itemId = (int) $_POST['item_id_for_action'];
        $claimerId = (int) $_POST['claimer_id_for_action'];

        $verifyStmt = $conn->prepare("SELECT reported_by, item_name FROM items WHERE item_id = ? AND reported_by = ? AND status = 'Pending'");
        $verifyStmt->bind_param("ii", $itemId, $userId);
        $verifyStmt->execute();
        $verifyResult = $verifyStmt->get_result();
        if ($verifyResult->num_rows > 0) {
            $row = $verifyResult->fetch_assoc();
            $itemName = $row['item_name'];

            $rejectStmt = $conn->prepare("UPDATE items SET status = 'Found', claimed_by = NULL, date_claimed = NULL WHERE item_id = ? AND status = 'Pending'");
            $rejectStmt->bind_param("i", $itemId);
            if ($rejectStmt->execute() && $rejectStmt->affected_rows > 0) {
                $_SESSION['success_msg'] = "Claim for '" . htmlspecialchars($itemName) . "' rejected.";
            } else {
                $_SESSION['error_msg'] = "Unable to reject the claim.";
            }
            $rejectStmt->close();
        } else {
            $_SESSION['error_msg'] = "Verification failed or you are not authorized.";
        }
        $verifyStmt->close();
        header("Location: my-reports.php");
        exit();
    }
}

$userId = decryptData($_SESSION["user_id"]);

$getItemsQuery = "SELECT i.item_id, i.item_name, i.category, i.date_reported, i.status, i.claimed_by, u.username as claimer_username
                  FROM items i
                  LEFT JOIN users u ON i.claimed_by = u.user_id
                  WHERE i.reported_by = ?
                  ORDER BY i.date_reported DESC";
$stmt = $conn->prepare($getItemsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>
<html>

<head>
    <title>LostTrack | My Reports</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="admin-body">
    <?php include 'sidebar.php'; ?>
    <div class="main-content" style="background-color: #fff; border-radius: 8px;">
        <div class="logo-title">
            <img src="../assets/images/ccsitlogo.png" width="50px" height="50px">
            <h2>MY REPORTS</h2>
        </div>
        <hr>

        <?php if ($successMsg): ?>
            <div class="success-message" style="margin-bottom: -15px"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="error-message" style="margin-bottom: -15px"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <br>

        <table width="100%">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Date Reported</th>
                    <th>Status</th>
                    <th>Actions / Claimer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = 1;

                if ($result->num_rows > 0) {
                    while ($data = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='text-align: center;'>" . $count++ . "</td>";
                        echo "<td>" . htmlspecialchars($data['item_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($data['category']) . "</td>";
                        echo "<td>" . htmlspecialchars($data['date_reported']) . "</td>";
                        echo "<td>" . htmlspecialchars($data['status']) . "</td>";
                        $textAlign = ($data['status'] == 'Pending') ? 'center' : 'left';
                        echo "<td style='text-align: $textAlign;'>";

                        if ($data['status'] == 'Pending' && !empty($data['claimed_by'])) {
                            echo "Claim by: <strong>" . htmlspecialchars($data['claimer_username'] ?: 'Unknown') . "</strong><br>";
                            echo "<a class='success-button' href='action.php?type=claim&mode=pending&action=approve&id="
                                . urlencode(encryptData($data['item_id']))
                                . "&claimer=" . urlencode(encryptData($data['claimed_by']))
                                . "&source=my-reports'>";
                            echo "<i class='ri-check-line'></i> Approve</a> ";

                            echo "<a class='danger-button' href='action.php?type=claim&mode=pending&action=reject&id="
                                . urlencode(encryptData($data['item_id']))
                                . "&claimer=" . urlencode(encryptData($data['claimed_by']))
                                . "&source=my-reports'>";
                            echo "<i class='ri-close-line'></i> Reject</a>";
                        } else if ($data['status'] == 'Claimed' && !empty($data['claimed_by'])) {
                            echo "<i class='ri-checkbox-circle-fill success-icon'></i>Claimed by: <b>"
                                . htmlspecialchars($data['claimer_username'] ?: 'Unknown')
                                . "</b>";
                        } else {
                            echo "<i class='ri-question-fill unknown-icon'></i>Claimed by: <b>N/A</b>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>