<?php
function validateReportItemInputs($itemName, $description, $category, $locationFound, $itemImage)
{
    $errors = [];

    if (empty($itemName) || empty($description) || (empty($category) || $category == "") || empty($locationFound))
        $errors[] = "<b>[ALL]</b> All required fields (Item Name, Description, Category, Date, Location) must be filled.";

    // ITEM NAME validation
    if (strlen($itemName) < 3 || strlen($description) > 100)
        $errors[] = "<b>[ITEM NAME]</b> Item name must be between 3 and 100 characters";

    // DESCRIPTION validation
    if (strlen($description) < 10 || strlen($description) > 500)
        $errors[] = "<b>[DESCRIPTION]</b> Description must be between 10 and 500 characters.";

    // CATEGORY validation
    if (empty($category) || $category == "none")
        $errors[] = "<b>[CATEGORY]</b> Please select a category for the item.";

    // LOCATION FOUND validation
    if (strlen($locationFound) < 3 || strlen($locationFound) > 150)
        $errors[] = "<b>[LOCATION FOUND]</b> Location found must be between 3 and 150 characters.";

    if (!isset($itemImage) || $itemImage["error"] == UPLOAD_ERR_NO_FILE) {
        $errors[] = "<b>[ITEM IMAGE]</b> An image file is required. Please upload an image of the item.";
    } elseif (isset($itemImage) && $itemImage["error"] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 5 * 1024 * 1024;

        if (!in_array($itemImage['type'], $allowedTypes)) {
            $errors[] = "<b>[ITEM IMAGE]</b> Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
        }
        if ($itemImage['size'] > $maxFileSize) {
            $errors[] = "<b>[ITEM IMAGE]</b> Image file is too large. Maximum size is 5MB.";
        }
    } elseif (isset($itemImage) && $itemImage["error"] != UPLOAD_ERR_NO_FILE && $itemImage["error"] != UPLOAD_ERR_OK) {
        $errors[] = "<b>[ITEM IMAGE]</b> There was an error uploading the image. Error code: " . $itemImage["error"];
    }

    if (!empty($errors)) {
        $_SESSION['error_msg'] = $errors;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

?>