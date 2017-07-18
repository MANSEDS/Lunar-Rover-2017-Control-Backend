<?php
    /**
     * command_list.php
     * Created by Matthew Marshall, 2017
     * Contributors:
     *   Matthew Marshall
     *
     * Lists all commands on the live command queue.
     */

     // Get all live commands.
    $commands = [];
    $file = fopen("command-queue.dat", "r");
    $line = "";
    if ($file) {
        // Step through each entry in the queue.
        while (($line = fgets($file)) !== false) {
            // Expand the entry to its individual data points.
            $parts = explode(" ", $line);

            // Construct a JS digestible data object of this entry.
            $command = [
                "section" => $parts[0],
                "id" => $parts[1]
            ];

            if (count($parts) > 2) {
                $command["args"] = array_slice($parts, 2);
            }

            // Append to array of converted entries.
            $commands[] = $command;
        }
    }

    // Return the commands in a JSON-encoded format.
    echo json_encode($commands);
?>