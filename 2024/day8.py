#!/usr/bin/env python3

from argparse import ArgumentParser


def read_map(filename):
    with open(filename) as f:
        area_map = [[char for char in line.strip()] for line in f]

    return area_map


def collect_antennas_by_frequency(map):
    antennas = {}
    for row_index, row in enumerate(map):
        for col_index, frequency in enumerate(row):
            if frequency == '.':
                continue
            if frequency not in antennas:
                antennas[frequency] = [(row_index, col_index)]
            else:
                antennas[frequency].append((row_index, col_index))
    return antennas


def count_antinodes(area_map, account_for_resonant_harmonics=False):
    map_rows = len(area_map)
    map_cols = len(area_map[0])
    antinodes = set()
    antennas = collect_antennas_by_frequency(area_map)

    for frequency in antennas:
        for antenna1_index, antenna1 in enumerate(antennas[frequency]):
            for antenna2 in antennas[frequency][antenna1_index + 1:]:
                row_diff = antenna2[0] - antenna1[0]
                col_diff = antenna2[1] - antenna1[1]
                if not account_for_resonant_harmonics:
                    potential_antinodes = {
                        (antenna1[0] - row_diff, antenna1[1] - col_diff),
                        (antenna2[0] + row_diff, antenna2[1] + col_diff),
                    }
                    for antinode in potential_antinodes:
                        if 0 <= antinode[0] < map_rows and 0 <= antinode[1] < map_cols:
                            antinodes.add(antinode)
                else:
                    antinode = antenna1
                    while 0 <= antinode[0] < map_rows and 0 <= antinode[1] < map_cols:
                        antinodes.add(antinode)
                        antinode = (antinode[0] - row_diff, antinode[1] - col_diff)
                    antinode = antenna2
                    while 0 <= antinode[0] < map_rows and 0 <= antinode[1] < map_cols:
                        antinodes.add(antinode)
                        antinode = (antinode[0] + row_diff, antinode[1] + col_diff)

    return len(antinodes)


parser = ArgumentParser(description='Count trasmission antinodes for AoC day 8.')
parser.add_argument('INPUT_FILE', help='Antenna map file')
args = parser.parse_args()

area_map = read_map(args.INPUT_FILE)

print('Initially calculated number of antinodes is:', count_antinodes(area_map, False))
print('Corrected number of antinodes is:', count_antinodes(area_map, True))
