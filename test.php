<?php

require 'vendor/autoload.php';

use raklib\server\UDPServerSocket;
use raklib\protocol\UnconnectedPing;
use raklib\protocol\UnconnectedPong;
use raklib\utils\InternetAddress;

function pingServer($address, $port) {
    $timeout = 5;
    $serverSocket = new UDPServerSocket(new InternetAddress($address, $port), $timeout);

    $ping = new UnconnectedPing();
    $ping->pingID = microtime(true) * 1000;
    $ping->encode();

    $serverSocket->writePacket($ping);

    $startTime = microtime(true);
    while (microtime(true) - $startTime < $timeout) {
        $packet = $serverSocket->readPacket();

        if ($packet instanceof UnconnectedPong) {
            return true;
        }
    }

    return false;
}

$address = '127.0.0.1';
$port = 19132;

if (pingServer($address, $port)) {
    echo "The server at $address:$port is online.";
} else {
    echo "The server at $address:$port is offline.";
}

?>
