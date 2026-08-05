<?php
// File: public_html/project/index.php

require_once(__DIR__ . "/../../lib/app.php");
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Jason's Meal Project</title>
</head>

<body>
    <?php render_nav(); ?>

    <main>
        <div class="jumbotron">
            <h1 class="display-4">
                Welcome to Jason's Meal Project
            </h1>

            <p>
                Browse meals, save favorites, and manage user meal associations.
            </p>

            <p class="lead">
                This project was created for IT202 during the Summer 2026 semester.
            </p>
        </div>
    </main>

    <?php render_flash_messages(); ?>
</body>

</html>