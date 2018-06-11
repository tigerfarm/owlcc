<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Owl CC</title>
        <link href="faviconftp.ico" rel="shortcut icon" type="image/x-icon">
    </head>
    <body>
        <div class="company">
            <h1>Owl Call Center</h1>
            Page not found.
        </div>
        <div>
            <p>URL: 
                <?php
                $request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
                echo $request_uri[0];
                ?>
            </p>
        </div>
</html>