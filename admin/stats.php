<?php
header('Content-Type: application/json');

// Function to get CPU load on Windows
function get_server_load() {
    $load = null;
    if (stristr(PHP_OS, "win")) {
        // Windows specific code
        $wmi = new COM("Winmgmts://");
        $cpu = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");
        $cpuLoad = 0;
        $cpuCount = 0;
        foreach ($cpu as $processor) {
            $cpuLoad += $processor->LoadPercentage;
            $cpuCount++;
        }
        if ($cpuCount > 0) {
            $load = $cpuLoad / $cpuCount;
        }
    }
    return $load;
}

// Function to get memory usage on Windows
function get_memory_usage() {
    $memory = ['total' => 0, 'free' => 0, 'used' => 0];
    if (stristr(PHP_OS, "win")) {
        // Windows specific code
        exec("wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value", $output, $resultCode);

        if ($resultCode === 0) {
            $freeMemory = 0;
            $totalMemory = 0;

            foreach ($output as $line) {
                if (preg_match('/^FreePhysicalMemory=(\d+)/', $line, $matches)) {
                    $freeMemory = (int)$matches[1] * 1024; // KB to Bytes
                }
                if (preg_match('/^TotalVisibleMemorySize=(\d+)/', $line, $matches)) {
                    $totalMemory = (int)$matches[1] * 1024; // KB to Bytes
                }
            }

            $memory['total'] = $totalMemory;
            $memory['free'] = $freeMemory;
            $memory['used'] = $totalMemory - $freeMemory;
        }
    }
    return $memory;
}

// Fetch the data
$data = [
    'cpu_load' => get_server_load(),
    'memory_usage' => get_memory_usage(),
];

// Return the data as JSON
echo json_encode($data);
?>
