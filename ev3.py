#!/usr/bin/env python3

from ev3dev2.motor import MoveTank, OUTPUT_A, OUTPUT_D, SpeedPercent
from ev3dev2._platform.ev3 import INPUT_2
from ev3dev2.sensor.lego import ColorSensor

def drive():
    tank_drive.on(SpeedPercent(30), SpeedPercent(30))


tank_drive = MoveTank(OUTPUT_A, OUTPUT_D)
cs = ColorSensor(INPUT_2)

drive()
while True:
    if cs.color != 6:
        tank_drive.stop()
        tank_drive.on_for_rotations(SpeedPercent(-35), SpeedPercent(-35), 1)
        tank_drive.on_for_degrees(SpeedPercent(40), SpeedPercent(0), 300)
        drive()

#I have selected version 3.8 in VSC (which i can see, because its on the bottom right), however i still cant download this pip library:
#pip install ev3dev2simulator