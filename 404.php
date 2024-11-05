<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php"; ?>
<html>
    <head>
        <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/head.php"; ?>
    </head>
    <body>
        <div id="particles-js"></div>
        <div class="container">
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/header.php"; ?>
            <div id="app" class="card mt-5">
                <div class="card-header">404 - Not Found</div>
                <div class="card-body text-center align-items-center">
					<h1>This page was not found</h1>
					<img src="/img/nsl.png" style="width: 50%;height: auto;">
					<a href="/" class="btn btn-primary mt-3" style="background-color: #510985;border-color: #510985;">Home</a>
                </div>
            </div>
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/footer.php"; ?>
        </div>
    </body>
</html>