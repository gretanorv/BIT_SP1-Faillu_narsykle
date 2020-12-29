<?php
session_start();

//logout
if (isset($_GET['action']) and $_GET['action'] === 'logout') {
    unset($_SESSION['logged_in']);
    unset($_SESSION['timeout']);
    unset($_SESSION['username']);
    header("Refresh:0; url=index.php");
}

//file upload logic
if (isset($_FILES['fileToUpload'])) {
    $errors = array();
    $file_name = $_FILES['fileToUpload']['name'];
    $file_size = $_FILES['fileToUpload']['size'];
    $file_tmp = $_FILES['fileToUpload']['tmp_name'];
    $file_type = $_FILES['fileToUpload']['type'];

    $file_ext = strtolower(end(explode('.', $_FILES['fileToUpload']['name'])));
    $extensions = array("jpeg", "jpg", "png");
    if (in_array($file_ext, $extensions) === false) {
        $errors[] = "Extension not allowed, please choose a JPEG or PNG file.";
    }
    if ($file_size > 2097152) {
        $errors[] = 'File size must be exately 2 MB';
    }
    //TODO:: catch Warning: POST Content-Length of 14917283 bytes exceeds the limit of 8388608 bytes in Unknown on line 0
    $upload_path = end(explode('=', $_SERVER['REQUEST_URI']));
    if (empty($errors) == true) {
        move_uploaded_file($file_tmp, $upload_path . '/' . $file_name);
        $success_msg = "File was uploaded successfully";
    } else {
        $err_msg = $errors;
    }
}

// file download logic
if (isset($_POST['download'])) {
    // print('Path to download: ' . './' . $_GET["path"] . $_POST['download']);
    // $file = './' .  $_POST['download'];
    $file = './' . $_GET["path"] . $_POST['download'];
    // a&nbsp;b.txt
    // a b.txt
    $fileToDownloadEscaped = str_replace("&nbsp;", " ", htmlentities($file, null, 'utf-8'));
    ob_clean();
    ob_start();
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf'); // mime type → ši forma turėtų veikti daugumai failų, su šiuo mime type. Jei neveiktų reiktų daryti sudėtingesnę logiką
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file)); // kiek baitų browseriui laukti, jei 0 - failas neveiks nors bus sukurtas
    ob_end_flush();
    readfile($file);
    exit;
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

        <h4 class="error-login">
            <?php
            if ($err_msg) {
                foreach ($err_msg as $key => $value) {
                    echo $value;
                }
            }
            ?>
        </h4>
        <h4 class="success"><?php echo $success_msg; ?></h4>
        <form action="" method="post" enctype="multipart/form-data" class="form--upload">
            <input type="file" name="fileToUpload" id="img" style="display:none;" />
            <button type="button" class="form__button form__button--upload">
                <label for="img">Choose file</label>
            </button>
            <button type="submit" class="form__button form__button--upload">Upload file</button>
        </form>

    <?php
    }

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
                print("<div class='table__row-right'><span class='table__row-right-content'>{$dir[$i]}</span>");
                print('<form action="?path=' . "{$path}/{$dir[$i]}" . '" method="POST" class="table__row-form">');
                print('<label for="download" class="table__row-btn table__row-btn--download">DOWNLOAD</label>');
                print('<input type="submit" id="download" name="download" value="" class="table__row-btn--hidden"/>');
                print('</form>');
                if (substr_compare($dir[$i], '.php', -4)) {
                    print("
                        <a href='index.php?action=delete&filename={$path}/{$dir[$i]}' class='table__row-btn'>
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