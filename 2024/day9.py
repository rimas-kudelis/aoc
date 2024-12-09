#!/usr/bin/env python3

from argparse import ArgumentParser


def read_disk_map(filename):
    with open(filename) as f:
        disk_map = [[int(char) for char in line.strip()] for line in f if line.strip() != ''][0]

    return disk_map


def compact(disk_map):
    compacted_map = []
    for current_region_index, current_region in enumerate(disk_map):
        if current_region_index % 2 == 0:
            # file
            for i in range(current_region):
                compacted_map.append(current_region_index // 2)
        else:
            # free space
            while True:
                end_region_index = len(disk_map) - 1
                if end_region_index %2 != 0:
                    # end region is free space
                    disk_map.pop()
                    continue

                end_region = disk_map.pop()
                if end_region > current_region:
                    disk_map.append(end_region - current_region)
                    end_region = current_region
                for i in range(end_region):
                    compacted_map.append(end_region_index // 2)
                if end_region == current_region:
                    break

                current_region -= end_region

    return compacted_map


def get_checksum(disk_map):
    checksum = 0
    for block_index, file_id in enumerate(disk_map):
        checksum += block_index * file_id

    return checksum

parser = ArgumentParser(description='Compact amphipod\'s hard drive and calculate its checksum for AoC day 9.')
parser.add_argument('INPUT_FILE', help='Disk map file')
args = parser.parse_args()

disk_map = read_disk_map(args.INPUT_FILE)
compacted_map = compact(disk_map.copy())

print('Compacted filesystem checksum:', get_checksum(compacted_map))
