#!/usr/bin/env python3

from argparse import ArgumentParser
import re


def find_instructions(memory):
    return re.findall(r'(mul\(\d{1,3},\d{1,3}\)|do\(\)|don\'t\(\))', memory)


def multiply(instruction):
    multiplicants = re.findall(r'\d{1,3}', instruction)
    return int(multiplicants[0]) * int(multiplicants[1])


parser = ArgumentParser(description='Salvage what\'s possible from program\'s corrupted memory for AoC 2024 day 3.')
parser.add_argument('INPUT_FILE', help='Corrupted memory input file')
args = parser.parse_args()

with open(args.INPUT_FILE) as f:
    memory = f.read()

instructions = find_instructions(memory)
original_sum = sum = 0
do = True
for instruction in instructions:
    if instruction.startswith('don\'t'):
        do = False
    elif instruction.startswith('do'):
        do = True
    else:
        product = multiply(instruction)
        original_sum += product
        if do:
            sum += product

print('Original sum of multiplications:', original_sum)
print('More precise sum of multiplications:', sum)
