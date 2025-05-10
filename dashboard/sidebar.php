<?php
if (isset($_SESSION["active-page"]))
    $current_page = $_SESSION["active-page"];
?>

<input type="checkbox" class="sidebar-toggle-checkbox">
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="ri-search-eye-line"></i>
        </div>
        <h3>Lost & Found</h3>
        <label for="sidebar-toggle" class="sidebar-toggle-label">
            <i class="ri-menu-line"></i>
        </label>
    </div>
    <ul class="sidebar-menu">
        <li class="<?php if ($current_page == 'home') {
            echo 'active';
        } ?>">
            <a href="index.php"><i class="ri-home-4-line"></i><span>Home</span></a>
        </li>        
        <li class="<?php if ($current_page == 'report-item') {
            echo 'active';
        } ?>">
            <a href="report.php"><i class="ri-alarm-warning-line"></i><span>Report Item</span></a>
        </li>     
        <li class="<?php if ($current_page == 'reports') {
            echo 'active';
        } ?>">
            <a href="my-reports.php"><i class="ri-file-chart-line"></i><span>My Reports</span></a>
        </li>        
        <li class="<?php if ($current_page == 'profile') {
            echo 'active';
        } ?>">
            <a href="profile.php"><i class="ri-user-5-line"></i><span>Profile</span></a>
        </li>
        <li class="logout">
            <a href="logout.php"><i class="ri-logout-box-line"></i><span>Logout</span></a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <small>&copy; 2025 LostTrack System</small>
    </div>
</div>