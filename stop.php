<?php
$port = $_GET['port'];

if(!isset($_GET['key'])) {
	echo "RobloxApp_server.exe process using port $port was not found.\n";
	exit();
}

if($_GET['key'] != "verysecretomgomgskibiti") {
	echo "RobloxApp_server.exe process using port $port was not found.\n";
	exit();
}

exec("netstat -ano | findstr :$port", $output);

if (!empty($output)) {
    $parts = explode(" ", $output[0]);
    $pid = trim(end($parts));

    exec("taskkill /F /PID $pid", $kill_output, $kill_status);

    if ($kill_status === 0) {
        echo "RobloxApp_server.exe process using port $port has been killed.\n";
    } else {
        echo "Failed to kill the RobloxApp_server.exe process.\n";
    }
} else {
    echo "RobloxApp_server.exe process using port $port was not found.\n";
}