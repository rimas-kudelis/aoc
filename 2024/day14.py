#!/usr/bin/env python3

from argparse import ArgumentParser
import re


def read_robot_setup(filename):
    robot_setup = []
    max_x = max_y = 0
    with open(filename) as f:
        for line in f:
            if line.strip() != '':
                (px, py, vx, vy) = [int(num) for num in
                                    re.match(r'^p=(\d+),(\d+) v=(-?\d+),(-?\d+)$', line.strip()).groups()]
                robot_setup.append({'px': px, 'py': py, 'vx': vx, 'vy': vy})
                max_x = max(max_x, px)
                max_y = max(max_y, py)

    return robot_setup, max_x, max_y


def calculate_safety_factor(robot_setup, seconds, max_x, max_y):
    div_x = max_x + 1
    div_y = max_y + 1

    def get_quadrant(px, py):
        return 0 if px < max_x / 2 and py < max_y / 2 \
            else 1 if px > max_x / 2 and py < max_y / 2 \
            else 2 if px < max_x / 2 and py > max_y / 2 \
            else 3 if px > max_x / 2 and py > max_y / 2 \
            else None

    quadrants = [0, 0, 0, 0]
    for robot in robot_setup:
        px = (robot['px'] + robot['vx'] * seconds) % div_x
        py = (robot['py'] + robot['vy'] * seconds) % div_y
        if (quadrant := get_quadrant(px, py)) is not None:
            quadrants[quadrant] += 1

    return quadrants[0] * quadrants[1] * quadrants[2] * quadrants[3]


parser = ArgumentParser(description='Calculate bathroom safety factor for AoC day 14.')
parser.add_argument('INPUT_FILE', help='Initial robot setup file')
parser.add_argument('-x', '-map-size-x', help='Map size X')
parser.add_argument('-y', '-map-size-y', help='Map size Y')
args = parser.parse_args()

robot_setup, max_x, max_y = read_robot_setup(args.INPUT_FILE)
if args.x is not None:
    if int(args.x) < max_x + 1:
        raise ValueError('map-size-x is too small for this map')
    max_x = int(args.x) - 1
if args.y is not None:
    if int(args.y) < max_y + 1:
        raise ValueError('map-size-y is too small for this map')
    max_y = int(args.y) - 1

seconds = 100
print('Safety factor after {} seconds: {}'.format(seconds, calculate_safety_factor(robot_setup, seconds, max_x, max_y)))
