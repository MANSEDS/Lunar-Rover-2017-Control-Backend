<?php
    /**
     * command_append.php
     * Created by Matthew Marshall, 2017
     * Contributors:
     *   Matthew Marshall
     *
     * Appends a command to the live command queue.
     */

    // Make sure we have data of a command to append to the queue.
    if (!isset($_GET["command_data"])) {
        echo -1;
        exit(0);
    }

    // Decode the data string.
    $pcd = json_decode($_GET["command_data"], true);

    // Make sure the decoding was a success.
    if ($pcd == NULL) {
        echo -1;
        exit(0);
    }

    // Ensure the necessary data was provided to create an entry in the command queue.
    if (!isset($pcd["section"] || !is_numeric($pcd["section"])) {
        echo -2;
        exit(0);
    }
    if (!isset($pcd["id"] || !is_numeric($pcd["id"])) {
        echo -3;
        exit(0);
    }
    if (!isset($pcd["args"])) {
        $pcd["args"] = [];
    } else if (!is_array($pcd["args"])) {
        echo -4;
        exit(0);
    }

    // Append the command to the queue.
    $file = fopen("command-queue.dat", "a+");
    if ($file) {
        $command_str = (string) $pcd["section"] . " " . (string) $pcd["id"] . " " . implode(" ", $pcd["args"]) . " " . date(DATE_ATOM);
        fwrite($file, $command_str);

        fclose($file);
    }

    echo 0;
?>