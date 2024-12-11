#!/usr/bin/env python3

from argparse import ArgumentParser

change_map = {'0': ['1']}


def read_stone_arrangement(filename):
    with open(filename) as f:
        return [mark for mark in f.read().strip().split(' ')]


def blink(stone_arrangement):
    mutated_arrangement = []
    for stone in stone_arrangement:
        if stone not in change_map:
            change_map[stone] = get_mutation_result(stone)

        mutated_arrangement.extend(change_map[stone])

    return mutated_arrangement


def get_mutation_result(stone):
    if (digits := len(stone)) % 2 == 0:
        return [stone[:digits // 2], str(int(stone[digits // 2:]))]

    return [str(int(stone) * 2024)]


parser = ArgumentParser(description='Simulate the behaviour of physics-defying stones for AoC day 11.')
parser.add_argument('INPUT_FILE', help='Stone arrangement file')
args = parser.parse_args()

stone_arrangement = read_stone_arrangement(args.INPUT_FILE)

for blink_number in range(25):
    stone_arrangement = blink(stone_arrangement)

print('Stones after 25 blinks:', len(stone_arrangement))
