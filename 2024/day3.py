#!/usr/bin/env python3

from argparse import ArgumentParser
import re

parser = ArgumentParser(description='Analyze unusual data from reports for AoC 2024 day 2.')
parser.add_argument('INPUT_FILE', help='Reports input file')
args = parser.parse_args()


def find_instructions(memory):
    return re.findall(r'mul\(\d{1,3},\d{1,3}\)', memory)


def execute(instruction):
    multiplicants = re.findall(r'\d{1,3}', instruction)
    return int(multiplicants[0]) * int(multiplicants[1])


with open(args.INPUT_FILE) as f:
    memory = f.read()

instructions = find_instructions(memory)

sum = 0
for instruction in instructions:
    sum += execute(instruction)

print('Sum of multiplications:', sum)
