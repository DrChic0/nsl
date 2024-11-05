<?php
ini_set('display_errors', 0);
$serverIP = '127.0.0.1';
$serverPort = 24419;

$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) {
    echo 0;
    exit;
}

$message = "Ping";

if (!socket_sendto($socket, $message, strlen($message), 0, $serverIP, $serverPort)) {
    echo 0;
    socket_close($socket);
    exit;
}

$timeout = 5;
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$timeout, "usec"=>0));
$response = "";
$from = "";
$port = 0;
if (socket_recvfrom($socket, $response, 1024, 0, $from, $port) === false) {
    echo 1;
} else {
    echo 0;
}

socket_close($socket);
?>
