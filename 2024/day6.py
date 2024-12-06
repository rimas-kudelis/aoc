#!/usr/bin/env python3

from argparse import ArgumentParser
from enum import Enum
import copy


class LoopDetected(Exception):
    pass


class GuardDirection(Enum):
    UP = '^'
    DOWN = 'v'
    LEFT = '<'
    RIGHT = '>'


class MapTile(Enum):
    UNVISITED = '.'
    OBSTRUCTION = '#'


def get_tile_from_char(tile_char):
    return {
        'obstruction': tile_char == MapTile.OBSTRUCTION.value,
        'visited': tile_char not in [MapTile.UNVISITED.value, MapTile.OBSTRUCTION.value],
        'exited_up': False,
        'exited_left': False,
        'exited_down': False,
        'exited_right': False,
        'could_loop': False,
    }


def get_exited_tile_key_from_direction(direction):
    match direction:
        case GuardDirection.UP:
            return 'exited_up'
        case GuardDirection.DOWN:
            return 'exited_down'
        case GuardDirection.LEFT:
            return 'exited_left'
        case GuardDirection.RIGHT:
            return 'exited_right'


def invert_direction(direction):
    match direction:
        case GuardDirection.UP:
            return GuardDirection.DOWN
        case GuardDirection.DOWN:
            return GuardDirection.UP
        case GuardDirection.LEFT:
            return GuardDirection.RIGHT
        case GuardDirection.RIGHT:
            return GuardDirection.LEFT


def get_char_from_tile(tile):
    before, after = ('\033[31m', '\033[0;39m') if tile['could_loop'] else ('', '')
    if tile['obstruction']:
        char = '#'
    elif (tile['exited_up'] or tile['exited_down']) and (tile['exited_left'] or tile['exited_right']):
        char = '+'
    elif tile['exited_up'] or tile['exited_down']:
        char = '|'
    elif tile['exited_left'] or tile['exited_right']:
        char = '-'
    else:
        char = '.'

    return before + char + after


def read_area_map(filename):
    area_map = []
    start_position = start_direction = None

    with open(filename) as f:
        while True:
            line = f.readline()
            if not line:
                break

            line = line.strip()
            if line == '':
                break

            if start_position is None:
                for dir in GuardDirection:
                    if (col := line.find(dir.value)) > -1:
                        start_position = len(area_map), col
                        start_direction = dir

            map_line = list(map(get_tile_from_char, list(line.strip())))

            area_map.append(map_line)
        print_map(area_map)

    return area_map, start_position, start_direction


def get_map_tile(area_map, position):
    try:
        if position[0] < 0 or position[1] < 0:
            return None
        return area_map[position[0]][position[1]]
    except IndexError:
        return None


def get_next_direction(direction):
    match direction:
        case GuardDirection.UP:
            return GuardDirection.RIGHT
        case GuardDirection.RIGHT:
            return GuardDirection.DOWN
        case GuardDirection.DOWN:
            return GuardDirection.LEFT
        case GuardDirection.LEFT:
            return GuardDirection.UP


def get_try_position(start_position, direction):
    match direction:
        case GuardDirection.UP:
            return start_position[0] - 1, start_position[1]
        case GuardDirection.RIGHT:
            return start_position[0], start_position[1] + 1
        case GuardDirection.DOWN:
            return start_position[0] + 1, start_position[1]
        case GuardDirection.LEFT:
            return start_position[0], start_position[1] - 1


def move_guard(area_map, start_position, direction, test_if_loops=False):
    for iteration in range(4):
        exited_key = get_exited_tile_key_from_direction(direction)
        if area_map[start_position[0]][start_position[1]][exited_key]:
            raise LoopDetected('Been here, done this!')
        next_position = get_try_position(start_position, direction)
        next_tile = get_map_tile(area_map, next_position)
        if next_tile is None:
            return None, direction, False
        if not next_tile['obstruction']:
            could_loop = False
            if test_if_loops and not next_tile['visited']:
                fake_map = copy.deepcopy(area_map)
                fake_map[next_position[0]][next_position[1]] = get_tile_from_char(MapTile.OBSTRUCTION.value)
                try:
                    simulate_guard_path(fake_map, start_position, direction, False)
                except LoopDetected:
                    could_loop = True
            return next_position, direction, could_loop
        direction = get_next_direction(direction)

    raise ValueError('Cannot move anywhere from current position.')


def simulate_guard_path(area_map, start_position, start_direction, test_if_loops=True):
    while True:
        next_position, next_direction, could_loop = move_guard(area_map, start_position, start_direction, test_if_loops)
        area_map[start_position[0]][start_position[1]]['visited'] = True
        exit_direction = get_exited_tile_key_from_direction(next_direction or start_direction)
        area_map[start_position[0]][start_position[1]][exit_direction] = True
        if next_position is None:
            break
        if could_loop:
            area_map[next_position[0]][next_position[1]]['could_loop'] = True
        start_position, start_direction = next_position, next_direction

    return area_map


def count_tiles(area_map):
    visited_tiles = loopable_tiles = 0

    for row in area_map:
        for tile in row:
            if tile['visited']: visited_tiles += 1
            if tile['could_loop']: loopable_tiles += 1

    return visited_tiles, loopable_tiles


def print_map(area_map):
    for line in area_map:
        print(''.join(map(get_char_from_tile, line)))


parser = ArgumentParser(description='Figure out guard\'s path and their looping options for AoC 2024 day 6.')
parser.add_argument('INPUT_FILE', help='Word search puzzle input')
args = parser.parse_args()

area_map, start_position, start_direction = read_area_map(args.INPUT_FILE)
traced_area_map = simulate_guard_path(area_map, start_position, start_direction)

print('--')
print_map(traced_area_map)
visited_tiles, loopable_tiles = count_tiles(traced_area_map)

print('Total tiles visited by guard:', visited_tiles)
print('Total loopable tiles:', loopable_tiles)
