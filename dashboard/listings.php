<?php
require '../database/connection.php';
include '../security/encryption.php';
include '../admin/session-details.php';

session_start();
$_SESSION["active-page"] = "listing";

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "User") {
    header("Location: ../error/401.php?ref=login&role=user");
    exit();
}

if (isset($_SESSION["verified_status"])) {
    if ($_SESSION["verified_status"] == "Unverified") {
        header("Location: ../login.php?status=unverified");
        exit();
    }
}

$current_user_id = decryptData($_SESSION["user_id"]);
$successMsg = $errorMsg = $warningMsg = "";

if (isset($_SESSION['success_msg'])) {
    $successMsg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $errorMsg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}
if (isset($_SESSION['warning_msg'])) {
    $warningMsg = $_SESSION['warning_msg'];
    unset($_SESSION['warning_msg']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['claim_item_id'])) {
    $item_id_to_claim = (int) $_POST['claim_item_id'];

    $checkStmt = $conn->prepare("SELECT item_id, item_name, status, reported_by FROM items WHERE item_id = ? AND status = 'Found'");
    $checkStmt->bind_param("i", $item_id_to_claim);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $itemData = $checkResult->fetch_assoc();
        $reporter_user_id = $itemData['reported_by'];
        $item_name_for_notification = $itemData['item_name'];

        if ($reporter_user_id == $current_user_id) {
            $errorMsg = "You cannot make a claim request for an item you reported as found.";
        } else {
            $updateQuery = "UPDATE items SET status = 'Pending', claimed_by = ?, date_claimed = NOW() WHERE item_id = ? AND status = 'Found'";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ii", $current_user_id, $item_id_to_claim);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $successMsg = "Claim request submitted for '" . htmlspecialchars($item_name_for_notification) . "'. The user who found it will be notified to verify.";

                    if ($reporter_user_id) {
                        $claimerStmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
                        $claimerStmt->bind_param("i", $current_user_id);
                        $claimerStmt->execute();
                        $claimerResult = $claimerStmt->get_result();
                        if ($claimerData = $claimerResult->fetch_assoc()) {
                            $claimerName = $claimerData['username'];
                        }
                        $claimerStmt->close();

                        $notification_message = htmlspecialchars($claimerName) . " has requested to claim the item you reported: '" . htmlspecialchars($item_name_for_notification) . "'. Please review and verify this claim.";
                        $notification_type = "claim_verification_request";

                        $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, item_id, message, type, related_user_id) VALUES (?, ?, ?, ?, ?)");
                        $notifyStmt->bind_param("iissi", $reporter_user_id, $item_id_to_claim, $notification_message, $notification_type, $current_user_id);
                        if (!$notifyStmt->execute()) {
                            error_log("Failed to create notification: " . $notifyStmt->error);
                        }
                        $notifyStmt->close();
                    }
                } else {
                    $errorMsg = "Item could not be claimed or was already claimed/pending verification.";
                }
            } else {
                $errorMsg = "Error processing claim request: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        $errorMsg = "Item is no longer available or does not exist.";
    }
    $checkStmt->close();

    $message = !empty($successMsg) ? $successMsg : $errorMsg;
    echo "<script>alert('" . addslashes($message) . "'); window.location='listings.php';</script>";
    exit();
}

$getItemsQuery = "SELECT item_id, item_name, description, category, date_reported, location_found, image_path FROM items WHERE status = 'Found' ORDER BY date_reported DESC";
$stmt = $conn->prepare($getItemsQuery);
$stmt->execute();
$result = $stmt->get_result();

?>
<html>

<head>
    <title>LostTrack | Available Items</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="admin-body">
    <?php include 'sidebar.php'; ?>

    <div class="main-content" style="background-color: #fff; border-radius: 8px;">
        <div class="logo-title">
            <img src="../assets/images/ccsitlogo.png" width="50px" height="50px">
            <h2>PUBLIC LISTING OF CLAIMABLE ITEMS</h2>
        </div>
        <hr><br>

        <?php if ($successMsg): ?>
            <div class="success-message" style="margin-bottom: 15px;"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="error-message" style="margin-bottom: 15px;"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>
        <?php if ($warningMsg): ?>
            <div class="warning-message" style="margin-bottom: 15px;"><?php echo $warningMsg; ?></div>
        <?php endif; ?>

        <?php
        if ($result->num_rows > 0) {
            echo '<table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Date Found</th>
                            <th>Location Found</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
            while ($item = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>';
                if (!empty($item['image_path']) && file_exists($item['image_path'])) {
                    echo '<img src="' . htmlspecialchars($item['image_path']) . '"
                               alt="' . htmlspecialchars($item['item_name']) . '"
                               style="max-width: 60px; max-height: 60px; border-radius: 4px; object-fit: cover;">';
                } else {
                    echo '<img src="../assets/images/no-image.png" alt="No image"
                               style="max-width: 60px; max-height: 60px; border-radius: 4px; object-fit: cover;">';
                }
                echo '</td>';
                echo '<td>' . htmlspecialchars($item['item_name']) . '</td>';
                echo '<td>' . htmlspecialchars($item['category']) . '</td>';
                echo '<td>' . htmlspecialchars(date("M d, Y", strtotime($item['date_reported']))) . '</td>';
                echo '<td>' . htmlspecialchars($item['location_found']) . '</td>';
                echo '<td style="white-space: pre-wrap; word-break: break-word;">' . htmlspecialchars($item['description']) . '</td>';
                echo '<td><a class="claim-button" href="action.php?type=claim&action=submit&id='
                    . urlencode(encryptData($item['item_id']))
                    . '&claimer=' . urlencode(encryptData($current_user_id))
                    . '&source=listings">Claim Item</a></td>';
                echo '</tr>';
            }
            echo '    </tbody>
                  </table>';
        } else {
            echo '<p style="padding: 15px; text-align: center;">No items currently available for claim.</p>';
        }
        $stmt->close();
        ?>
    </div>
</body>

</html>