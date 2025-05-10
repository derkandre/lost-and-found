<?php
require '../database/connection.php';
include '../security/encryption.php';
include '../admin/session-details.php';
include 'validations.php';

session_start();

$_SESSION["active-page"] = "report-item";

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "User") {
    header("Location: ../error/401.php?ref=login&role=user");
    exit();
}

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
    $itemName = ucwords(trim($_POST["item_name"]));
    $description = trim($_POST["description"]);
    $category = trim($_POST["category"]);
    $dateReported = trim($_POST["date_reported"]);
    $locationFound = ucwords(trim($_POST["location_found"]));

    $_SESSION["item_name_input"] = $itemName;
    $_SESSION["description_input"] = $description;
    $_SESSION["category_input"] = $category;
    $_SESSION["date_reported_input"] = $dateReported;
    $_SESSION["location_found_input"] = $locationFound;

    validateReportItemInputs($itemName, $description, $category, $locationFound, $_FILES["item_image"] ?? null);

    $image_path = "";
    $targetDir = "../uploads/";

    $imageFileType = strtolower(pathinfo($_FILES["item_image"]["name"], PATHINFO_EXTENSION));

    $sanitizedItemName = preg_replace("/[^a-zA-Z0-9]+/", "", $itemName);

    $customFileName = $sanitizedItemName . "_" . date("Ymd_His") . "." . $imageFileType;
    $targetFile = $targetDir . $customFileName;

    if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $targetFile)) {
        $image_path = $targetFile;
    } else {
        $_SESSION["error_msg"] = "There was a problem while uploading the image.";
        header("Location: report.php");
        exit();
    }

    $status = "Lost";

    $reportedBy = decryptData($_SESSION["user_id"]);

    $query = "INSERT INTO items (item_name, description, category, date_reported, status, location_found, image_path, reported_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sssssssi", $itemName, $description, $category, $dateReported, $status, $locationFound, $image_path, $reportedBy);
        if ($stmt->execute()) {
            unset($_SESSION["item_name_input"]);
            unset($_SESSION["description_input"]);
            unset($_SESSION["category_input"]);
            unset($_SESSION["date_reported_input"]);
            unset($_SESSION["location_found_input"]);
            unset($_SESSION['uploaded_preview']);

            $_SESSION["success_msg"] = "Item reported successfully!";
            header(header: "Location: report.php");
        } else {
            $_SESSION["error_msg"] = "Failed to report item: " . $stmt->error;
            header(header: "Location: report.php");
        }
        $stmt->close();
        exit();
    } else {
        $_SESSION["error_msg"] = "Failed to prepare statement: " . $conn->error;
        header(header: "Location: report.php");
        exit();
    }
}
?>
<html>

<head>
    <title>LostTrack | Report Item</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/remixicon.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="non-admin-body">
    <?php include 'sidebar.php'; ?>
    <div class="main-content" style="background-color:#fff;border-radius:8px;">
        <form method="POST" enctype="multipart/form-data">
            <div class="logo-title">
                <img src="../assets/images/ccsitlogo.png" width="50px" height="50px">
                <h2>REPORT ITEM</h2>
            </div>
            <hr>

            <label>Item Details</label>
            <div class="grouped-inputs">
                <div class="input-container">
                    <i class="ri-folder-3-fill input-icon" style="top: 23px"></i>
                    <input id="item_name" type="text" name="item_name" placeholder="Item Name"
                        value="<?php echo htmlspecialchars($_SESSION['item_name_input'] ?? ''); ?>" required>
                </div>
                <div class="input-container">
                    <i class="ri-file-text-fill input-icon" style="top: 23px"></i>
                    <textarea id="description" name="description"
                        placeholder="Description (explain the item such as its color and brand)" required
                        style="padding:10px 10px 50px 40px;"><?php echo htmlspecialchars($_SESSION['description_input'] ?? ''); ?></textarea>
                </div>
            </div>
            <hr>

            <label>Category &amp; Date Reported</label>
            <div class="grouped-inputs">
                <div class="input-container">
                    <i class="ri-list-check input-icon"></i>
                    <select id="category" name="category" required
                        style="height:46px; padding-left:40px; appearance:menulist-button;">
                        <option value="" disabled <?php echo (!isset($_SESSION['category_input']) || $_SESSION['category_input'] == '') ? 'selected' : ''; ?>>
                            Select Category
                        </option>
                        <optgroup label="Electronic Devices">
                            <option value="Mobile Phone" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Mobile Phone') ? 'selected' : ''; ?>>Mobile Phone
                            </option>
                            <option value="Laptop" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Laptop') ? 'selected' : ''; ?>>Laptop</option>
                            <option value="Tablet" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Tablet') ? 'selected' : ''; ?>>Tablet</option>
                            <option value="Headphones" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Headphones') ? 'selected' : ''; ?>>Headphones</option>
                        </optgroup>
                        <optgroup label="Stationery">
                            <option value="Notebook" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Notebook') ? 'selected' : ''; ?>>Notebook</option>
                            <option value="Pen" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Pen') ? 'selected' : ''; ?>>Pen</option>
                            <option value="Calculator" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Calculator') ? 'selected' : ''; ?>>Calculator</option>
                        </optgroup>
                        <optgroup label="Personal Items">
                            <option value="ID Card" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'ID Card') ? 'selected' : ''; ?>>Student ID</option>
                            <option value="ATM Card" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'ATM Card') ? 'selected' : ''; ?>>ATM Card</option>
                            <option value="Wallet" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Wallet') ? 'selected' : ''; ?>>Wallet</option>
                            <option value="Umbrella" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Umbrella') ? 'selected' : ''; ?>>Umbrella</option>
                        </optgroup>
                        <option value="Other" <?php echo (isset($_SESSION['category_input']) && $_SESSION['category_input'] == 'Other') ? 'selected' : ''; ?>>
                            Other – please specify in description
                        </option>
                    </select>
                </div>
                <div class="input-container">
                    <i class="ri-calendar-line input-icon"></i>
                    <input id="date_reported" type="date" name="date_reported"
                        value="<?php echo htmlspecialchars($_SESSION['date_reported_input'] ?? date('Y-m-d')); ?>"
                        readonly required>
                </div>
            </div>
            <hr>

            <label>Location &amp; Image</label>
            <div class="grouped-inputs">
                <div class="input-container">
                    <i class="ri-map-pin-2-fill input-icon"></i>
                    <input id="location_found" type="text" name="location_found" placeholder="Location Found"
                        value="<?php echo htmlspecialchars($_SESSION['location_found_input'] ?? ''); ?>">
                </div>
                <div class="input-container">
                    <i class="ri-image-line input-icon"></i>
                    <input id="item_image" type="file" name="item_image" accept="image/*">
                </div>
            </div>
            <hr>

            <div style="text-align:center;">
                <?php if ($successMsg): ?><span
                        class="success-message"><?php echo htmlspecialchars($successMsg); ?></span><?php endif; ?>
                
                <?php
                    if (!empty($errorMsg)) {
                        if (is_array($errorMsg)) {
                            foreach ($errorMsg as $msg) { ?>
                                <p class="error-message" style="margin-bottom: -32px; text-align: left;">
                                    <?php echo $msg; ?>
                                </p><br>
                            <?php }
                        } else { ?>
                            <span class="error-message"><?php echo htmlspecialchars($errorMsg); ?></span>
                        <?php }
                    }
                    ?>
            </div>

            <button type="submit" name="submit">Report Item</button>
        </form>
    </div>
</body>