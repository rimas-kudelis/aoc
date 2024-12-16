#!/usr/bin/env python3

from argparse import ArgumentParser


class Tile:
    WALL = '#'
    BOX = 'O'
    BLANK = '.'
    ROBOT = '@'


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


def simulate_robot_movements(warehouse_map, robot_movements, robot_position):
    for movement in robot_movements:
        robot_position = move_if_possible(warehouse_map, robot_position, movement)


def move_if_possible(warehouse_map, robot_position, movement):
    (row, col) = robot_position
    blank_tile = new_position = None

    match movement:
        case '^':
            new_position = (row - 1, col)
            for try_row in range(row - 1, 0, -1):
                match warehouse_map[try_row][col]:
                    case Tile.BOX:
                        continue
                    case Tile.WALL:
                        break
                    case Tile.BLANK:
                        blank_tile = (try_row, col)
                        break
                    case _:
                        raise ValueError('Unexpected map tile: {}'.format(warehouse_map[try_row][col]))
        case '<':
            new_position = (row, col - 1)
            for try_col in range(col - 1, 0, -1):
                match warehouse_map[row][try_col]:
                    case Tile.BOX:
                        continue
                    case Tile.WALL:
                        break
                    case Tile.BLANK:
                        blank_tile = (row, try_col)
                        break
                    case _:
                        raise ValueError('Unexpected map tile: {}'.format(warehouse_map[row][try_col]))
        case 'v':
            new_position = (row + 1, col)
            for try_row in range(row + 1, len(warehouse_map) - 1, 1):
                match warehouse_map[try_row][col]:
                    case Tile.BOX:
                        continue
                    case Tile.WALL:
                        break
                    case Tile.BLANK:
                        blank_tile = (try_row, col)
                        break
                    case _:
                        raise ValueError('Unexpected map tile: {}'.format(warehouse_map[try_row][col]))
        case '>':
            new_position = (row, col + 1)
            for try_col in range(col + 1, len(warehouse_map[row]) - 1, 1):
                match warehouse_map[row][try_col]:
                    case Tile.BOX:
                        continue
                    case Tile.WALL:
                        break
                    case Tile.BLANK:
                        blank_tile = (row, try_col)
                        break
                    case _:
                        raise ValueError('Unexpected map tile: {}'.format(warehouse_map[row][try_col]))
        case _:
            raise ValueError('Unexpected robot movement: {}'.format(movement))

    if blank_tile is not None:
        warehouse_map[blank_tile[0]][blank_tile[1]] = Tile.BOX
        warehouse_map[new_position[0]][new_position[1]] = Tile.BLANK
        return new_position

    return robot_position


def get_sum_of_gps_coordinates(warehouse_map):
    sum = 0

    for row_index, row in enumerate(warehouse_map):
        for tile_index, tile in enumerate(row):
            if tile == Tile.BOX:
                sum += row_index * 100 + tile_index

    return sum


def print_map(warehouse_map):
    for row in warehouse_map:
        print(''.join(row))


parser = ArgumentParser(description='Help lanternfish stop their robot that is running amok in AoC day 15.')
parser.add_argument('INPUT_FILE', help='Warehouse map and robot movements file.')
args = parser.parse_args()

warehouse_map, robot_movements, robot_position = read_input(args.INPUT_FILE)

# print_map(warehouse_map)
# print(robot_movements, robot_position)
simulate_robot_movements(warehouse_map, robot_movements, robot_position)
print('Sum of GPS coordinates:', get_sum_of_gps_coordinates(warehouse_map))
# print_map(warehouse_map)
