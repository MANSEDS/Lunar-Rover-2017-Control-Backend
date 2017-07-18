<?php
    /**
     * all_resume.php
     * Created by Matthew Marshall, 2017
     * Contributors:
     *   Matthew Marshall
     *
     * Prepends the all_resume command to the front of the command queue.
     */

    // Remove first entry from command queue.
    exec("tail -n +2 /var/www/html/command-queue.dat > /tmp/command-queue.dat.tmp && mv /tmp/command-queue.dat.tmp /var/www/html/command-queue.dat");

    // Get contents of queue after removing first entry.
    $current_queue = file_get_contents("/var/www/html/command-queue.dat");

    // Prepend the all_resume command to the front of the queue.
    if ($current_queue == false) {
        file_put_contents("/var/www/html/command-queue.dat", "-1\n");
    } else {
        file_put_contents("/var/www/html/command-queue.dat", "-1\n" . $current_queue);
    }
?>