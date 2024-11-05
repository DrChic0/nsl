<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php";

if($_SERVER['REQUEST_METHOD'] == "POST") {
    if(!isset($_POST['id'])) {
        exit();
    }

    $stmt = $db->prepare("SELECT * FROM servers WHERE id = :id");
    $stmt->execute([':id' => $_POST['id']]);

    if(!$stmt->rowCount()) {
        exit();
    }

    $stmt = $db->prepare("UPDATE servers SET players = :players WHERE id = :id");
    $stmt->execute([
        ':players' => $_POST['players'],
        ':id' => $_POST['id']
    ]);
}
