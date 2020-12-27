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

    //on load initiate
    if (!isset($_GET['dir'])) {
        $curr_dir = '..';
        $home = scanHere($curr_dir);
        createTable($home, $curr_dir);
    } else if (isset($_GET['dir'])) {
        $new_dir = $_GET['dir'];
        $test = scanHere($new_dir);
        createTable($test, $new_dir);
    }


    function scanHere($dir)
    {
        $a = scandir($dir);
        return $a;
    }

    function createTable($dir, $path)
    {
        print('<div class="container">');

        //BACK button
        if (isset($_GET['dir']) and $_GET['dir'] != '..') {
            $_SERVER['REQUEST_URI'] = preg_replace('#\/[^/]*$#', '$1', $_SERVER['REQUEST_URI']) . "\n";;
            print('<a class="table__nav" href=' . $_SERVER['REQUEST_URI'] . '>Back</a>');
        }

        //CREATE NEW button
        print('<button class="table__nav table__nav--create-new">Create new</button>');

        print('<div class="table">');

        //table
        $i = 2;
        for ($i; $i < count($dir); $i++) {

            if (is_dir("{$path}/{$dir[$i]}")) {
                print('<div class="table__row">');
                print('<div class="table__row-left">Directory</div>');
                print("<div class='table__row-right'>
                            <a 
                                class='table__row-link' 
                                href='index.php?dir={$path}/{$dir[$i]}'
                            >{$dir[$i]}
                            </a>
                        </div>");
                print('</div>');
            } else if (is_file("{$path}/{$dir[$i]}")) {
                print('<div class="table__row">');
                print("<div class='table__row-left'>File</div>");
                print("<div class='table__row-right'>
                    {$dir[$i]}
                    <button class='table__row-right-btn'>DELETE</button>
                    </div>");
                // print('<button class="table__row-right-btn">DELETE</button>');
                print('</div>');
            }
        }
        print('</div>');
        print('</div>');
    }

    ?>

    <script src="index.js"></script>
</body>

</html>