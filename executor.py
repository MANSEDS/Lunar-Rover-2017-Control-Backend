# control_backend/executor.py
# Created by Ethan Ramsay, 2017
# Contributors:
#   Ethan Ramsay
#   Matthew Marshall
#
# This file handles initialising the iNEMO inertial module chip.
# This chip contains an accelerometer and a gyroscope.

# Import dependencies
import os
import subprocess
from datetime import datetime

subprocess_handles = {}
all_stop_in_progress = False

def executeAllResume():
    global all_stop_in_progress
    all_stop_in_progress = False

def executeAllStop():
    global subprocess_handles
    global all_stop_in_progress
    all_stop_in_progress = True
    for key, val in subprocess_handles.iteritems():
        val.terminate()

def executeMotorCommand(command_data):
    command = ""
    if (command_data[0] == 1):
        command = "python /home/pi/Robotics/Drive_controller.py -f -v " + str(command_data[1]) + " -d " + str(command_data[2])
    elif (command_data[0] == 2):
        command = "python /home/pi/Robotics/Drive_controller.py -b -v " + str(command_data[1]) + " -d " + str(command_data[2])
    elif (command_data[0] == 3):
        command = "python /home/pi/Robotics/Drive_controller.py -l -a " + str(command_data[1])
    elif (command_data[0] == 4):
        command = "python /home/pi/Robotics/Drive_controller.py -r -a " + str(command_data[1])

    if (command == ""):
        return

    subprocess_handles["motors"] = subprocess.Popen(command, shell=True)

def executeArmCommand(command_data):
    command = ""
    if (command_data[0] == 1):
        command = "python /home/pi/Robotics/Arm_controller.py -s"
    elif (command_data[0] == 2):
        command = "python /home/pi/Robotics/Arm_controller.py -e"
    elif (command_data[0] == 3):
        command = "python /home/pi/Robotics/Arm_controller.py -i"
    elif (command_data[0] == 4):
        command = "python /home/pi/Robotics/Arm_controller.py -g"
    elif (command_data[0] == 5):
        command = "python /home/pi/Robotics/Arm_controller.py -d"
    elif (command_data[0] == 6):
        command = "python /home/pi/Robotics/Arm_controller.py -p " + str(command_data[1])
    elif (command_data[0] == 7):
        command = "python /home/pi/Robotics/Arm_controller.py -o " + str(command_data[1])

    if (command == ""):
        return

    subprocess_handles["arm"] = subprocess.Popen(command, shell=True)

def executeCameraCommand(command_data):
    command = ""
    if (command_data[0] == 1):
        command = "python /home/pi/Robotics/Camera_controller.py -p"
    elif (command_data[0] == 2):
        command = "python /home/pi/Robotics/Camera_controller.py -m -a " + str(command_data[1])

    if (command == ""):
        return

    subprocess_handles["camera"] = subprocess.Popen(command, shell=True)

def marshal_command(command_str):
    command_parts = command_str.split(" ")
    {
        -1: lambda : executeAllResume(),
         0: lambda : executeAllStop(),
         1: lambda : executeMotorCommand(command_parts[1:]),
         2: lambda : executeArmCommand(command_parts[1:]),
         3: lambda : executeCameraCommand(command_parts[1:])
    }[int(command_parts[0])]()

def run():
    while True:
        if os.path.isfile("/var/www/html/command-queue.dat"):
            with open("/var/www/html/command-queue.dat", "r") as handle:
                # Read in next command to be executed and marshal.
                command_str = handle.readline()
                if (command_str == ""):
                    continue
                marshal_command(command_str)

                # Don't change command queue as long as all stop in progress.
                if (all_stop_in_progress):
                    continue

                # Add command to command history.
                with open("/var/www/html/command-history.dat", "a+") as handle:
                    handle.write("{} {}".format(command_str, datetime.now()))

                # Remove command from command queue.
                subprocess.call(["tail -n +2 /var/www/html/command-queue.dat > /tmp/command-queue.dat.tmp && mv -f /tmp/command-queue.dat.tmp /var/www/html/command-queue.dat"], shell=True)

# Main
if __name__ == "__main__":
    run()
