<?php
    /**
     * data_fetch.php
     * Created by Matthew Marshall, 2017
     * Contributors:
     *   Matthew Marshall
     *
     * Fetches and returns all telemetry from the various sensors.
     */

    // Get the index from which to fetch all inertial data.
    $inertial_start_index = 0;
    if (isset($_GET["start_index_for_inemo"])) {
        $inertial_start_index = $_GET["start_index_for_inemo"];
    }

    $data  = [];

    // Get all inertial data since last index sent.
    $inertial_data = [];
    $file = fopen("inertial.dat", "r");
    $line = "";
    if ($file) {
        // Iterate over each entry in the interial data log,
        // converting the data into a JS-digestible format.
        while (($line = fgets($file)) !== false) {
            $parts = explode(" ", $line);
            if ($parts[0] < $inertial_start_index) 
                continue;
            
            $inertial_data[] = [
                "index" => $parts[0],
                "acc" => [
                    "x" => $parts[1],
                    "y" => $parts[2],
                    "z" => $parts[3]
                ],
                "gyr" => [
                    "x" => $parts[4],
                    "y" => $parts[5],
                    "z" => $parts[6]
                ],
                "temp" => $parts[7],
                "time" => $parts[8]
            ];
        }

        fclose($file);
    }
    // Move current log to an archive and add last line of it to a new "live" log.
    // This keeps file traversal to a minimum for the sake of performance.
    exec("mv inertial.dat " + uniqid() + ".dat");
    $file = fopen("inertial.dat", "w+");
    if ($file) {
        fwrite($file, $line);
        fclose($file);
    }
    $data["inertial"] = $inertial_data;

    // Capture the CPU temperature.
    $cpu_temp_ret = [];
    exec("/opt/vc/bin/vcgencmd measure_temp | cut -c6-9", $cpu_temp_ret);
    $cpu_temp = (float) $cpu_temp_ret[0];

    // Capture the GPU temperature.
    $gpu_temp_ret = [];
    exec("echo $((`cat /sys/class/thermal/thermal_zone0/temp|cut -c1-2`)).$((`cat /sys/class/thermal/thermal_zone0/temp|cut -c3-3`))", $gpu_temp_ret);
    $gpu_temp = (float) $gpu_temp_ret[0];

    $data["rpi_temps"] = [
        "cpu" => $cpu_temp,
        "gpu" => $gpu_temp
    ];



    echo json_encode($data);
?>