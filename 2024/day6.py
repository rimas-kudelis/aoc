#!/usr/bin/env python3

from argparse import ArgumentParser
from enum import Enum
import copy


class LoopDetected(Exception):
    def __init__(self, message, tile, direction):
        super().__init__(message)
        self.tile = tile
        self.direction = direction


class GuardDirection(Enum):
    UP = '^'
    DOWN = 'v'
    LEFT = '<'
    RIGHT = '>'


class MapTile(Enum):
    UNVISITED = '.'
    OBSTRUCTION = '#'


def get_tile_from_char(tile_char):
    is_start_tile = tile_char not in [MapTile.UNVISITED.value, MapTile.OBSTRUCTION.value]
    return {
        'obstruction': tile_char == MapTile.OBSTRUCTION.value,
        'visited': is_start_tile,
        'entered_up': None,
        'entered_left': None,
        'entered_down': None,
        'entered_right': None,
        'could_loop': False,
        'start': is_start_tile,
    }


def get_entered_tile_key_from_direction(direction):
    match direction:
        case GuardDirection.UP:
            return 'entered_up'
        case GuardDirection.DOWN:
            return 'entered_down'
        case GuardDirection.LEFT:
            return 'entered_left'
        case GuardDirection.RIGHT:
            return 'entered_right'


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

            map_row = list(map(get_tile_from_char, list(line.strip())))

            area_map.append(map_row)
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


def move_guard(area_map, start_position, direction):
    for iteration in range(4):
        next_position = get_try_position(start_position, direction)
        next_tile = get_map_tile(area_map, next_position)
        if next_tile is None:
            return None, direction
        if not next_tile['obstruction']:
            entered_key = get_entered_tile_key_from_direction(direction)
            if area_map[next_position[0]][next_position[1]][entered_key]:
                raise LoopDetected('Been there, done that!', area_map[next_position[0]][next_position[1]], direction)
            return next_position, direction
        direction = get_next_direction(direction)

    raise ValueError('Cannot move anywhere from current position.')


def simulate_guard_path(area_map, start_position, start_direction, start_at_step = 1):
    while True:
        next_position, next_direction = move_guard(area_map, start_position, start_direction)
        area_map[start_position[0]][start_position[1]]['visited'] = True
        entered_key = get_entered_tile_key_from_direction(next_direction or start_direction)
        if next_position is None:
            break
        area_map[next_position[0]][next_position[1]][entered_key] = start_at_step
        start_at_step += 1
        start_position, start_direction = next_position, next_direction

    return area_map


def mark_loopable_tiles(area_map):
    for row_index, row in enumerate(area_map):
        for tile_index, tile in enumerate(row):
            if not tile['visited'] or tile['start']:
                continue
            entered_from_row, entered_from_tile, enter_direction = get_entered_data(row_index, tile_index, tile)
            fake_map = copy.deepcopy(area_map)
            fake_map[row_index][tile_index] = get_tile_from_char(MapTile.OBSTRUCTION.value)
            starting_step = tile[get_entered_tile_key_from_direction(enter_direction)]
            # print('fake')
            # print_map(fake_map)
            # print(starting_step, (entered_from_row, entered_from_tile), enter_direction, tile[get_entered_tile_key_from_direction(enter_direction)])
            try:
                simulate_guard_path(fake_map, (entered_from_row, entered_from_tile), enter_direction, starting_step)
            except LoopDetected as e:
                if e.tile[get_entered_tile_key_from_direction(e.direction)] < starting_step:
                    tile['could_loop'] = True
                # print(tile, e.tile, enter_direction, e.direction)

    return area_map


def get_entered_data(row_index, tile_index, tile):
    enter_direction = enter_index = None

    for direction in GuardDirection:
        tile_key = get_entered_tile_key_from_direction(direction)
        if tile[tile_key] is None:
            continue
        if tile[tile_key] <= (enter_index or tile[tile_key]):
            enter_direction, enter_index = direction, tile[tile_key]

    match enter_direction:
        case GuardDirection.UP:
            return row_index + 1, tile_index, enter_direction
        case GuardDirection.DOWN:
            return row_index - 1, tile_index, enter_direction
        case GuardDirection.LEFT:
            return row_index, tile_index + 1, enter_direction
        case GuardDirection.RIGHT:
            return row_index, tile_index - 1, enter_direction


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


def get_char_from_tile(tile):
    before, after = ('\033[31m', '\033[0;39m') if tile['could_loop'] else ('', '')
    if tile['obstruction']:
        char = '#'
    elif (tile['entered_up'] or tile['entered_down']) and (tile['entered_left'] or tile['entered_right']):
        char = '+'
    elif tile['entered_up'] or tile['entered_down']:
        char = '|'
    elif tile['entered_left'] or tile['entered_right']:
        char = '-'
    else:
        char = '.'

    return before + char + after


parser = ArgumentParser(description='Figure out guard\'s path and their looping options for AoC 2024 day 6.')
parser.add_argument('INPUT_FILE', help='Word search puzzle input')
args = parser.parse_args()

area_map, start_position, start_direction = read_area_map(args.INPUT_FILE)
traced_area_map = simulate_guard_path(area_map, start_position, start_direction)
traced_area_map = mark_loopable_tiles(traced_area_map)

print('--')
print_map(traced_area_map)
visited_tiles, loopable_tiles = count_tiles(traced_area_map)

print('Total tiles visited by guard:', visited_tiles)
print('Total loopable tiles:', loopable_tiles)
