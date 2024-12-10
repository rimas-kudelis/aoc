#!/usr/bin/env python3

from argparse import ArgumentParser


def read_map(filename):
    with open(filename) as f:
        area_map = [[int(char) for char in line.strip()] for line in f if line.strip() != '']

    return area_map


def get_trailheads_with_peaks(area_map):
    total_rows = len(area_map)
    total_cols = len(area_map[0])
    trailheads = set()
    for row_index, row in enumerate(area_map):
        for col_index, tile in enumerate(row):
            if tile != 0:
                continue
            trailheads.add((
                row_index,
                col_index,
                frozenset(find_reachable_peaks(area_map, row_index, col_index, total_rows, total_cols)),
            ))

    return trailheads


def find_reachable_peaks(map, start_row, start_col, total_rows, total_cols):
    current_height = map[start_row][start_col]
    if current_height == 9:
        return {(start_row, start_col)}

    peaks = set()
    try_tiles = {
        (start_row - 1, start_col),
        (start_row + 1, start_col),
        (start_row, start_col - 1),
        (start_row, start_col + 1),
    }
    for tile in {tile for tile in try_tiles if 0 <= tile[0] < total_rows and 0 <= tile[1] < total_cols}:
        if map[tile[0]][tile[1]] == current_height + 1:
            peaks = peaks | find_reachable_peaks(map, tile[0], tile[1], total_rows, total_cols)

    return peaks


def sum_of_scores(trailheads_with_peaks):
    sum = 0
    for trailhead in trailheads_with_peaks:
        sum += len(trailhead[2])

    return sum


parser = ArgumentParser(description='Help the little reindeer map the hiking trails for AoC day 10.')
parser.add_argument('INPUT_FILE', help='Topographic map file')
args = parser.parse_args()

area_map = read_map(args.INPUT_FILE)
trailheads_with_peaks = get_trailheads_with_peaks(area_map)

print('Sum of trailhead scores:', sum_of_scores(trailheads_with_peaks))
