<html>

<head>
    <title>401| Unautohrized</title>
    <link rel="stylesheet" href="../styles/style.css">
    <meta http-equiv="refresh" content="3;url=<?php echo isset($_GET['ref']) && $_GET['ref'] == 'login' ? '../admin/login.php' : '../'; ?>" />
</head>

<body class="non-admin-body">
    <div class="container">
        <h1 class="error-page-danger">ERROR 401 | Unauthorized</h1>

        <?php
        echo "<p>The page you are trying to access is for authenticated users only!</p>";

        if (isset($_GET["ref"])) {
            if ($_GET["ref"] == "login") {
                echo "<Redirecting>You need to <a class='success-highlight' href='../admin/login.php'>LOGIN</a> first. <span class='warning-highlight'>Redirecting you in 3 seconds.</span></p>";
            } else
                echo "<p>Go back to <a class='error-highlight' href='../'>homepage</a>.</p>";
        } else {
            echo "<p>Go back to <a class='error-highlight' href='../'>homepage</a>.</p>";
        }
        ?>
    </div>
</body>

</html>