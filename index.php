<?php
session_start();

//logout
if (isset($_GET['action']) and $_GET['action'] === 'logout') {
    unset($_SESSION['logged_in']);
    unset($_SESSION['timeout']);
    unset($_SESSION['username']);
    header("Refresh:0; url=index.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>

<body>
    <?php
    if (isset($_POST['login']) and !empty($_POST['username']) and !empty($_POST['password'])) {
        if ($_POST['username'] === 'useris' and $_POST['password'] === 'passwordas') {
            $_SESSION['logged_in'] = true;
            $_SESSION['timeout'] = time();
            $_SESSION['username'] = $_POST['username'];
            header("Refresh:0");
            $msg = 'Correct';
        } else {
            $msg = 'Wrong user name or password';
        }
    }

    if (!$_SESSION['logged_in']) {
    ?>
        <h2 class="title title--login">Login</h2>
        <form class="login-form" method="post">
            <div class="login-form__field">
                <label for="username" class="login-form__field-label">Username</label>
                <input type="text" name="username" id="username" class="login-form__field-input" placeholder="name: useris" required autofocus>
            </div>
            <div class="login-form__field">
                <label for="password" class="login-form__field-label">Password</label>
                <input type="password" name="password" id="password" class="login-form__field-input" placeholder="pass: passwordas" required>
            </div>
            <input type="submit" value="Login" name="login" class="login-form__btn">
        </form>
        <h4 class="error-login"><?php echo $msg ?></h4>

    <?php
    } elseif ($_SESSION['logged_in']) {
    ?>
        <a class="logout" href="index.php?action=logout">Logout</a>
        <h1 class="title">File explorer</h1>
        <?php

        if (!isset($_GET['dir'])) {
            $curr_dir = '..';
        } else {
            $curr_dir = $_GET['dir'];
        }

        if (isset($_POST['newFolder']) and $_POST['newFolder'] != '') {
            if (!file_exists($curr_dir . '/' . $_POST['newFolder'])) {
                mkdir($curr_dir . '/' . $_POST['newFolder']);
                array_push(scandir($curr_dir), $_POST['newFolder']);
            } else {
                print('<span class="error">Folder name exists</span>');
            }
        } elseif ($_POST['newFolder'] === '') {
            print('<span class="error">Folder name cannot be empty</span>');
        }

        //DELETE button
        if ($_GET['action'] && $_GET['action'] == 'delete') {
            $file_location = preg_replace('#\/[^/]*$#', '$1', $_GET['filename']) . "\n";;
            $redirect_to = "http://localhost/failu-narsykle/index.php?dir={$file_location}";
            unlink($_GET['filename']);
            header("Location: {$redirect_to}");
            exit();
        }
        ?>

        <div class="container">
            <form action="" method="post" class="form">
                <input type="text" name="newFolder" id="newFolder" class="form__input" placeholder="Folder name">
                <input type="submit" value="Create" class="form__button">
            </form>
            <div class="table">
                <?php
                //BACK button logic
                if (isset($_GET['dir']) and $_GET['dir'] != '..') {
                    $_SERVER['REQUEST_URI'] = preg_replace('#\/[^/]*$#', '$1', $_SERVER['REQUEST_URI']) . "\n";;
                    print('<a class="table__nav" href=' . $_SERVER['REQUEST_URI'] . '>Back</a>');
                }

                createTable($curr_dir);
                ?>
            </div>
        </div>
    <?php
    }
    ?>

    <?php
    function createTable($path)
    {
        $dir = scandir($path);

        $i = 2;
        for ($i; $i < count($dir); $i++) {
            print('<div class="table__row">');
            if (is_dir("{$path}/{$dir[$i]}")) {
                print('<div class="table__row-left">Directory</div>');
                print("<div class='table__row-right'>
                            <a class='table__row-link' href='index.php?dir={$path}/{$dir[$i]}'>
                                {$dir[$i]}
                            </a></div>");
            } elseif (is_file("{$path}/{$dir[$i]}")) {
                print("<div class='table__row-left'>File</div>");
                print("<div class='table__row-right'>{$dir[$i]}");
                if (substr_compare($dir[$i], '.php', -4)) {
                    print("
                        <a href='index.php?action=delete&filename={$path}/{$dir[$i]}' class='table__row-right-btn'>
                            DELETE
                        </a>");
                }
                print("</div>");
            }
            print('</div>');
        }
    }
    ?>

    <script> </script>
</body>

</html>