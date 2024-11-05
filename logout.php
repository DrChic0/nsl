<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php"; ?>
<?php
if(isset($_SESSION['siteusername'])) {
    $_SESSION = [];
    session_destroy();
}
header("Location: /");
exit();