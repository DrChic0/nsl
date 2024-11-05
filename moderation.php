<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php"; ?>
<?php
if(!isset($_SESSION['siteipban'])) {
	header("Location: /");
	exit();
}
?>
<html>
    <head>
        <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/head.php"; ?>
    </head>
    <body>
        <div id="particles-js"></div>
        <div class="container">
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/header.php"; ?>
            <div id="app" class="card mt-5">
                <div class="card-header">Moderation</div>
                <div class="card-body text-center">
                    <div class="col-md-6" style="margin-left: auto;margin-right: auto;">
						You have been banned from Novetus Server List for breaking our rules, you may appeal this ban here:<br><br>
                        <button class="btn btn-success" style="background-color: #510985;border-color: #510985;" onclick="window.open('//www.youtube.com/watch?v=dQw4w9WgXcQ');document.getElementById('realstuff').style.display=''">Ban Appeal Form</button>
                        <div id="realstuff" style="display: none;">
                            <br><br>
                            If your actually looking to appeal your ban send a email to <a href="mailto:support@aesthetiful.com">support@aesthetiful.com</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php require $_SERVER['DOCUMENT_ROOT'] . "/modules/footer.php"; ?>
        </div>
    </body>
</html>