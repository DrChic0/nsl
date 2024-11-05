<?php
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

$command = "C:/Users/NovetusServerr/AppData/Local/Programs/Python/Python38/python.exe C:/xampp/htdocs/get_players.py " . $_GET['port'];
$result = executeCommandWithTimeout($command, 10);

echo isset($result['stdout']) && !empty($result['stdout']) ? $result['stdout'] : $result['stderr'];

