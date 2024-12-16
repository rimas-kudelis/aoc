#!/usr/bin/env python3

from argparse import ArgumentParser


class Tile:
    WALL = '#'
    BOX = 'O'
    BLANK = '.'
    ROBOT = '@'
    BIG_BOX_L = '['
    BIG_BOX_R = ']'


class Movement:
    UP = '^'
    DOWN = 'v'
    LEFT = '<'
    RIGHT = '>'


def read_input(filename):
    warehouse_map = []
    movements = []
    robot_position = None

    with open(filename) as f:
        reading_map = True
        for line in f:
            line = line.strip()
            if line == '':
                reading_map = False
                continue

            if reading_map:
                map_row = [char for char in line]
                warehouse_map.append(map_row)
                try:
                    robot_position_in_row = map_row.index(Tile.ROBOT)
                    robot_position = (len(warehouse_map) - 1, robot_position_in_row)
                    warehouse_map[robot_position[0]][robot_position[1]] = Tile.BLANK
                except ValueError:
                    pass
            else:
                movements.extend([char for char in line])

    return warehouse_map, movements, robot_position


def scale_warehouse(warehouse_map, robot_position):
    scaled_warehouse = []
    for row in warehouse_map:
        scaled_row = []
        for tile in row:
            match tile:
                case Tile.BOX:
                    scaled_row.extend([Tile.BIG_BOX_L, Tile.BIG_BOX_R])
                case Tile.WALL:
                    scaled_row.extend([Tile.WALL, Tile.WALL])
                case Tile.BLANK:
                    scaled_row.extend([Tile.BLANK, Tile.BLANK])
        scaled_warehouse.append(scaled_row)

    return scaled_warehouse, (robot_position[0], robot_position[1] * 2)


def simulate_robot_movements(warehouse_map, robot_movements, robot_position):
    for movement in robot_movements:
        robot_position = move_if_possible(warehouse_map, robot_position, movement)


def move_if_possible(warehouse_map, robot_position, movement):
    move_tiles, new_robot_position = get_movement_result(warehouse_map, robot_position, movement)
    if move_tiles is not None:
        for move_group in move_tiles:
            for (src, dest) in move_group:
                warehouse_map[dest[0]][dest[1]] = warehouse_map[src[0]][src[1]]
                warehouse_map[src[0]][src[1]] = Tile.BLANK

    return new_robot_position


def get_movement_result(warehouse_map, object_position, movement):
    (row, col) = object_position
    match movement:
        case Movement.UP:
            target_position = (row - 1, col)
        case Movement.DOWN:
            target_position = (row + 1, col)
        case Movement.LEFT:
            target_position = (row, col - 1)
        case Movement.RIGHT:
            target_position = (row, col + 1)
        case _:
            raise ValueError('Unexpected robot movement: {}'.format(movement))

    target_tile = warehouse_map[target_position[0]][target_position[1]]
    if target_tile == Tile.BLANK:
        return [{(object_position, target_position)}], target_position

    if target_tile == Tile.WALL:
        return None, object_position

    if target_tile == Tile.BOX \
            or target_tile in [Tile.BIG_BOX_L, Tile.BIG_BOX_R] and movement in [Movement.LEFT, Movement.RIGHT]:

        moves = get_movement_result(warehouse_map, target_position, movement)[0]
        if moves is None:
            return None, object_position

        moves.append({(object_position, target_position)})
        return moves, target_position

    if target_tile in [Tile.BIG_BOX_L, Tile.BIG_BOX_R]:
        target_twin_position = (target_position[0], target_position[1] + 1) if target_tile == Tile.BIG_BOX_L \
            else (target_position[0], target_position[1] - 1)

        moves = get_movement_result(warehouse_map, target_position, movement)[0]
        if moves is None:
            return None, object_position

        moves_twin = get_movement_result(warehouse_map, target_twin_position, movement)[0]
        if moves_twin is None:
            return None, object_position

        priority_delta = len(moves) - len(moves_twin)
        if priority_delta < 0:
            moves, moves_twin = moves_twin, moves
            priority_delta *= -1

        for priority in range(0, len(moves_twin)):
            moves[priority + priority_delta] = moves[priority + priority_delta] | moves_twin[priority]

        moves.append({(object_position, target_position)})
        return moves, target_position

    raise ValueError('Unexpected tile: {}'.format(target_tile))


def get_sum_of_gps_coordinates(warehouse_map):
    sum = 0

    for row_index, row in enumerate(warehouse_map):
        for tile_index, tile in enumerate(row):
            if tile in (Tile.BOX, Tile.BIG_BOX_L):
                sum += row_index * 100 + tile_index

    return sum


def print_map(warehouse_map, robot_position=None):
    for row_index, row in enumerate(warehouse_map):
        row = row.copy()
        if robot_position is not None and row_index == robot_position[0]:
            row[robot_position[1]] = Tile.ROBOT
        print(''.join(row))


parser = ArgumentParser(description='Help lanternfish stop their robot that is running amok in AoC day 15.')
parser.add_argument('INPUT_FILE', help='Warehouse map and robot movements file.')
args = parser.parse_args()

warehouse_map, robot_movements, robot_position = read_input(args.INPUT_FILE)
scaled_warehouse_map, scaled_robot_position = scale_warehouse(warehouse_map, robot_position)

simulate_robot_movements(warehouse_map, robot_movements, robot_position)
print('Sum of GPS coordinates for the smaller warehouse:', get_sum_of_gps_coordinates(warehouse_map))

simulate_robot_movements(scaled_warehouse_map, robot_movements, scaled_robot_position)
print('Sum of GPS coordinates for the bigger warehouse:', get_sum_of_gps_coordinates(scaled_warehouse_map))
