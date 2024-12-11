#!/usr/bin/env python3

from argparse import ArgumentParser

after_change_count_map = {('0', 1): 1}


def read_stone_arrangement(filename):
    with open(filename) as f:
        return [mark for mark in f.read().strip().split(' ')]


def count_stones_after_blinking(stone_arrangement, number_of_times=1):
    stone_count = 0

    for stone in stone_arrangement:
        if (stone, number_of_times) not in after_change_count_map:
            changed = get_mutation_result(stone)
            after_change_count_map[(stone, number_of_times)] = len(changed) if number_of_times == 1 \
                else count_stones_after_blinking(changed, number_of_times - 1)

        stone_count += after_change_count_map[(stone, number_of_times)]

    return stone_count


def get_mutation_result(stone):
    return ['1'] if stone == '0' \
        else [stone[:digits // 2], str(int(stone[digits // 2:]))] if (digits := len(stone)) % 2 == 0 \
        else [str(int(stone) * 2024)]


parser = ArgumentParser(description='Simulate the behaviour of physics-defying stones for AoC day 11.')
parser.add_argument('INPUT_FILE', help='Stone arrangement file')
args = parser.parse_args()

stone_arrangement = read_stone_arrangement(args.INPUT_FILE)

stone_count = count_stones_after_blinking(stone_arrangement, 25)
print('Stones after 25 blinks:', stone_count)
stone_count = count_stones_after_blinking(stone_arrangement, 75)
print('Stones after 75 blinks:', stone_count)
