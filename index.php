<?php
session_start();
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

    <h1 class="title">File explorer</h1>

    <?php
    print(substr_compare('file.php', '.php', -4));

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
            //TODO:: place message nicely
            print('Folder name exists');
        }
    } elseif ($_POST['newFolder'] === '') {
        //TODO:: place message nicely
        print('Folder name is empty');
    }

    //DELETE button
    if ($_GET['action'] && $_GET['action'] == 'delete') {
        unlink($_GET['filename']);
        header("Location:index.php");
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