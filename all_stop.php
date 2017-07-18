<?php
    /**
     * all_stop.php
     * Created by Matthew Marshall, 2017
     * Contributors:
     *   Matthew Marshall
     *
     * Prepends the all_stop command to the front of the command queue.
     */

    // Get the current queue.
    $current_queue = file_get_contents("/var/www/html/command-queue.dat");

    // Prepend the all_stop command.
    if ($current_queue == false) {
        file_put_contents("/var/www/html/command-queue.dat", "0\n");
    } else {
        file_put_contents("/var/www/html/command-queue.dat", "0\n" . $current_queue);
    }
?>