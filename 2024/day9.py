#!/usr/bin/env python3

from argparse import ArgumentParser


def read_disk_map(filename):
    with open(filename) as f:
        disk_map = [[int(char) for char in line.strip()] for line in f if line.strip() != ''][0]

    return disk_map


def compact_with_fragmentation(disk_map):
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
                if end_region_index % 2 != 0:
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


def compact_without_fragmentation(disk_map):
    verbose_map = build_verbose_map(disk_map)
    moved_something = True

    while moved_something:
        moved_something = False

        for file_region_index in range(len(verbose_map) - 1, 0, -1):
            file_region = verbose_map[file_region_index]

            if not file_region['can_be_moved']:
                continue

            for target_region_index in range(0, file_region_index):
                target_region = verbose_map[target_region_index]

                if target_region['file_index'] is not None or target_region['size'] < file_region['size']:
                    continue

                if (size_difference := target_region['size'] - file_region['size']) > 0:
                    verbose_map.insert(
                        target_region_index + 1,
                        {'size': size_difference, 'can_be_moved': False, 'file_index': None},
                    )
                    target_region['size'] = file_region['size']

                target_region['file_index'] = file_region['file_index']
                file_region['file_index'] = None
                moved_something = True
                break

            file_region['can_be_moved'] = False

    return build_compacted_map_from_verbose_map(verbose_map)


def build_verbose_map(disk_map):
    return [
        {
            'size': region_size,
            'can_be_moved': region_index % 2 == 0,
            'file_index': region_index // 2 if region_index % 2 == 0 else None,
        }
        for region_index, region_size in enumerate(disk_map)
    ]


def build_compacted_map_from_verbose_map(verbose_map):
    compacted_map = []

    for region in verbose_map:
        for i in range(region['size']):
            compacted_map.append(region['file_index'])

    return compacted_map


def get_checksum(disk_map):
    checksum = 0
    for block_index, file_id in enumerate(disk_map):
        if file_id is not None:
            checksum += block_index * file_id

    return checksum


parser = ArgumentParser(description='Compact amphipod\'s hard drive and calculate its checksum for AoC day 9.')
parser.add_argument('INPUT_FILE', help='Disk map file')
args = parser.parse_args()

disk_map = read_disk_map(args.INPUT_FILE)

print('Compacted fragmented filesystem checksum:', get_checksum(compact_with_fragmentation(disk_map.copy())))
print('Compacted non-fragmented filesystem checksum:', get_checksum(compact_without_fragmentation(disk_map)))
