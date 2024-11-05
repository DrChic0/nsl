<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php";

header('Content-Type: application/json; charset=utf-8');
$key = "verysecretomgomgskibiti";
$data = (object) [
    'status' => 000,
    'message' => ""
];

if (!isset($_GET['id'])) {
    $data->status = 200;
    $data->message = "Missing parameters";

    echo json_encode($data);
    http_response_code(200);
    exit();
}

if (!isset($_SESSION['siteusername'])) {
    $data->status = 200;
    $data->message = "You need to be logged in";

    echo json_encode($data);
    http_response_code(200);
    exit();
}

$stmt = $db->prepare("SELECT status FROM users WHERE username = :username");
$stmt->execute([':username' => $_SESSION['siteusername']]);
$status = $stmt->fetch(PDO::FETCH_ASSOC)['status'] != "admin";

$stmt = $db->prepare("SELECT * FROM servers WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);
$server = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stmt->rowCount() && ($server['author'] != $_SESSION['siteusername']) && $server['hosting'] == 1 && $server['online'] == 0 && $status != "admin") {
    $data->status = 403;
    $data->message = "You are not authorized to commit this action";

    echo json_encode($data);
    http_response_code(403);
    exit();
}

$stmt = $db->prepare("UPDATE servers SET online = 0, players = '[]', stopped = CURRENT_TIMESTAMP() WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);

$response = file_get_contents("https://api.novetusserverlist.com/stop?port=" . $server['port'] . "&key=" . $key);
$data->status = 200;
$data->message = $response;

echo json_encode($data);
http_response_code(200);
exit();