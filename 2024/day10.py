#!/usr/bin/env python3

from argparse import ArgumentParser


def read_map(filename):
    with open(filename) as f:
        area_map = [[int(char) for char in line.strip()] for line in f if line.strip() != '']

    return area_map


def get_trailheads_with_trails_and_peaks(area_map):
    total_rows = len(area_map)
    total_cols = len(area_map[0])
    trailheads = set()
    for row_index, row in enumerate(area_map):
        for col_index, tile in enumerate(row):
            if tile != 0:
                continue
            trails, peaks = find_trails_and_peaks(area_map, row_index, col_index, total_rows, total_cols)
            trailheads.add((row_index, col_index, frozenset(trails), frozenset(peaks)))

    return trailheads


def find_trails_and_peaks(map, start_row, start_col, total_rows, total_cols):
    current_height = map[start_row][start_col]
    if current_height == 9:
        return frozenset([(start_row, start_col)]), {(start_row, start_col)}

    trails = set()
    peaks = set()
    try_tiles = {
        (start_row - 1, start_col),
        (start_row + 1, start_col),
        (start_row, start_col - 1),
        (start_row, start_col + 1),
    }
    for tile in {tile for tile in try_tiles if 0 <= tile[0] < total_rows and 0 <= tile[1] < total_cols}:
        if map[tile[0]][tile[1]] == current_height + 1:
            tile_trails, tile_peaks = find_trails_and_peaks(map, tile[0], tile[1], total_rows, total_cols)
            for tile_trail in tile_trails:
                (full_trail := [tile]).extend(tile_trail)
                trails.add(frozenset(full_trail))
            peaks = peaks | tile_peaks

    return trails, peaks


def get_trail_scores(trailheads_with_peaks):
    scores_by_peaks = ratings_by_trails = 0
    for trailhead in trailheads_with_peaks:
        scores_by_peaks += len(trailhead[3])
        ratings_by_trails += len(trailhead[2])

    return scores_by_peaks, ratings_by_trails


parser = ArgumentParser(description='Help the little reindeer map the hiking trails for AoC day 10.')
parser.add_argument('INPUT_FILE', help='Topographic map file')
args = parser.parse_args()

area_map = read_map(args.INPUT_FILE)
trailheads_with_trails_and_peaks = get_trailheads_with_trails_and_peaks(area_map)
scores_by_peaks, ratings_by_trails = get_trail_scores(trailheads_with_trails_and_peaks)

print('Sum of trailhead scores by reacheable peaks:', scores_by_peaks)
print('Sum of trailhead ratings by amount of trails:', ratings_by_trails)
