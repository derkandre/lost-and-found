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
        <li class="<?php if ($current_page == 'login') {
            echo 'active';
        } ?>">
            <a href="login.php"><i class="ri-login-box-line"></i><span>Login</span></a>
        </li>        
        <li class="register">
            <a href="register.php"><i class="ri-user-add-line"></i><span>Register</span></a>
        </li>     
    </ul>
    <div class="sidebar-footer">
        <small>&copy; 2025 LostTrack System</small>
    </div>
</div>