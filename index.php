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
            if (is_dir("{$path}/{$dir[$i]}")) {
                $folder = true;
                $href_dir = "index.php?dir={$path}/{$dir[$i]}";
                $type = 'Directory';
                $file_name = $dir[$i];
            } elseif (is_file("{$path}/{$dir[$i]}")) {
                $folder = false;
                //TODO:: if ending .php then no delete action
                $del_btn = true;
                $href_dir = "index.php?action=delete&filename={$path}/{$dir[$i]}";
                $type = 'File';
                $file_name = $dir[$i];
            }
    ?>
            <div class="table__row">
                <div class="table__row-left"><?php echo $type ?></div>
                <div class='table__row-right'>
                    <?php
                    if ($folder) {
                        print("<a class='table__row-link' href='{$href_dir}'>{$file_name}
                        </a>");
                    } else {
                        print("{$file_name}");
                        if ($del_btn) {
                            print("<a href='{$href_dir}' class='table__row-right-btn'>DELETE</a>");
                        }
                    }
                    ?>
                </div>
            </div>
    <?php
        }
    }
    ?>

    <script> </script>
</body>

</html>