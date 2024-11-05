<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/mysql.php";

header('Content-Type: application/json; charset=utf-8');
$data = (object) [
    'status' => 000,
    'message' => ""
];

if (!isset($_GET['id'])) {
    $data->status = 403;
    $data->message = "Missing parameters";

    echo json_encode($data);
    http_response_code(403);
    exit();
}

if (!isset($_SESSION['siteusername'])) {
    $data->status = 403;
    $data->message = "You need to be logged in";

    echo json_encode($data);
    http_response_code(403);
    exit();
}

$stmt = $db->prepare("SELECT * FROM servers WHERE id = :id");
$stmt->execute([':id' => $_GET['id']]);
$server = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stmt->rowCount() || $server['hosting'] == 1) {
    $data->status = 403;
    $data->message = "You are not authorized to commit this action";

    echo json_encode($data);
    http_response_code(403);
    exit();
}

if($server['author'] != $_SESSION['siteusername']) {
    $data->status = 403;
    $data->message = "You are not authorized to commit this action";

    echo json_encode($data);
    http_response_code(403);
    exit();
}
/*
$datetime_from_db = new DateTime($server['stopped']);
$current_datetime = new DateTime();
$interval = $current_datetime->diff($datetime_from_db);
$minutes_diff = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

if($minutes_diff < 15) {
	$data->status = 403;
    $data->message = "You are on a cooldown";

    echo json_encode($data);
    http_response_code(403);
    exit();
}
*/
//allowedports

$stmt = $db->prepare("SELECT COUNT(*) FROM servers WHERE hosting = 2 AND online = 1 AND author = :username");
$stmt->execute([':username' => $_SESSION['siteusername']]);

if($stmt->fetchColumn() > 0) {
	$data->status = 403;
    $data->message = "You only can use 1 server slot at the same time";

    echo json_encode($data);
    http_response_code(403);
    exit();
}

$stmt = $db->prepare("SELECT * FROM servers WHERE hosting = 2 AND online = 1");
$stmt->execute();

while($servers = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if(in_array($servers['port'], $availablePorts)) {
        $index = array_search($servers['port'], $availablePorts);
        if ($index !== false) {
            unset($availablePorts[$index]);
        }
    }
}

if(count($availablePorts) == 0) {
    $data->status = 403;
    $data->message = "Too many servers are running";

    echo json_encode($data);
    http_response_code(403);
    exit();
}

$port = $availablePorts[array_rand($availablePorts)];
$response = file_get_contents("https://api.novetusserverlist.com/start?port=" . $port . "&filename=" . $server['filename'] . "&client=" . $server['client'] . "&maxplayers=" . $server['maxplayers']);
$data->status = 200;
$response = json_decode($response, true);
$response['stdout'] .= "[NOVETUSSERVERLIST.COM] - The server might take a little more time to be able to accept connections\n[NOVETUSSERVERLIST.COM] - The server console has been modified to hide server information";
$response['stdout'] = str_replace("Y:/var/www/www.novetusserverlist.com/assets/places", "", $response['stdout']);
$response['stdout'] = str_replace("C:\\", "", $response['stdout']);
$response['stdout'] = str_replace("\\", "/", $response['stdout']);
$response['stdout'] = str_replace("-windows", "", $response['stdout']);
$response['stdout'] = str_replace($server['filename'], "place.rbxl", $response['stdout']);
$response = json_encode($response);
$data->message = $response;

$console = json_decode($response, true);
$uri = $console['uri'];
$ip = $console['ip'];
$console = $console['stdout'] . $console['stderr'];

$stmt = $db->prepare("UPDATE servers SET console = :console, online = 1, port = :port, uri = :uri, lastping = CURRENT_TIMESTAMP(), ip = :ip, started = CURRENT_TIMESTAMP() WHERE id = :id");
$stmt->execute([
    ':console' => $console,
    ':port' => $port,
    ':id' => $server['id'],
    ':uri' => $uri,
    ':ip' => $ip
]);

echo json_encode($data);
http_response_code(200);
exit();
