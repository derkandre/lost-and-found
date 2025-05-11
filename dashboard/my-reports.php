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
        $_SESSION["warning-message"] = "Your account is currently <br>unverified</b>. You will recieve an email once the admin has verified and approved your registration.";
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

$groups = [
    'Electronic Devices' => ['Mobile Phone', 'Laptop', 'Tablet', 'Headphones'],
    'Stationery' => ['Notebook', 'Pen', 'Calculator'],
    'Personal Items' => ['ID Card', 'ATM Card', 'Wallet', 'Umbrella'],
    'Other' => ['Other']
];

$summary = [];
foreach ($groups as $label => $categories) {
    $insert = "'" . implode("','", $categories) . "'";

    $getCountQuery = "SELECT COUNT(*) AS count FROM items WHERE reported_by = ? AND category IN ($insert)";
    
    $stmt = $conn->prepare($getCountQuery);
    $stmt->bind_param("i", $userId);

    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $summary[$label] = (int)$data["count"];
    $stmt->close();
}

$getItemsQuery = "SELECT item_id, item_name, category, date_reported, status FROM items WHERE reported_by = ? ORDER BY date_reported DESC";
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
    <div class="main-content"">
            <h2 style=" color: whitesmoke; margin-bottom: 20px;">My Reports</h2>

        <?php if ($successMsg): ?>
            <div class=" success-message"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <div class="dashboard">
            <div class="card">
                <div class="card-header">
                    <h2>Report Inventory Summary</h2>
                </div>

                <div class="card-body">
                    <?php
                    foreach ($summary as $label => $count) {
                        echo '<div>';
                        echo '<h4>' . htmlspecialchars($label) . '</h4>';
                        echo '<p>' . htmlspecialchars($count) . ' reported</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <table style="width:100%; margin-top:1em;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Date Reported</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows) {
                    while ($data = $result->fetch_assoc()) {
                        echo '<tr>'
                            . '<td style="text-align: center;">' . $data['item_id'] . '</td>'
                            . '<td>' . htmlspecialchars($data['item_name']) . '</td>'
                            . '<td style="text-align: center;">' . htmlspecialchars($data['category']) . '</td>'
                            . '<td style="text-align: center;">' . htmlspecialchars($data['date_reported']) . '</td>'
                            . '<td style="text-align: center;">' . htmlspecialchars($data['status']) . '</td>'
                            . '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5" style="text-align:center;">No reports found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>