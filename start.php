<?php
if(!isset($_GET['port']) or !isset($_GET['filename']) or !isset($_GET['client']) or !isset($_GET['maxplayers'])) {
    exit();
}

function executeCommandWithTimeout($command, $timeout) {
    $process = proc_open($command, array(
        0 => array("pipe", "r"),  // stdin
        1 => array("pipe", "w"),  // stdout
        2 => array("pipe", "w")   // stderr
    ), $pipes);

    if (is_resource($process)) {
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        stream_set_timeout($pipes[1], $timeout);
        stream_set_timeout($pipes[2], $timeout);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return array(
            'stdout' => $stdout,
            'stderr' => $stderr
        );
    }

    return false;
}

$command = "C:/novetus-windows/bin/NovetusCMD.exe --outputinfo --no3d --port " . $_GET['port'] . " --map \"Y:/var/www/www.novetusserverlist.com/assets/places/" . $_GET['filename'] . "\" --client " . $_GET['client'] . " -maxplayers " . $_GET['maxplayers'] . " -script \"dofile('https://www.novetusserverlist.com/assets/test.lua')\"";
$timeout = 10;
$result = executeCommandWithTimeout($command, $timeout);

if ($result === false) {
    echo "Process took longer than {$timeout} seconds and was killed.";
} else {
    $lines = file('C:/novetus-windows/serverinfo.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $uri = "";

    foreach ($lines as $line) {
        if (strpos($line, 'novetus://') === 0) {
            $uri = $line;
            break;
        }
    }

    $data = (object) [
        'stdout' => $result['stdout'],
        'stderr' => $result['stderr'],
        'uri' => $uri,
        'ip' => "147.185.221.16"
    ];

    echo json_encode($data);
    exit();
}