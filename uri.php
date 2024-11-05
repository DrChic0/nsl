<?php
$lines = file('C:/novetus-windows/serverinfo.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    if (strpos($line, 'novetus://') === 0) {
        echo "Online URI Link: $line";
        break;
    }
}
?>
