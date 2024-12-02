#!/usr/bin/env python3

import re
from argparse import ArgumentParser

parser = ArgumentParser(description='Calculate distance and similarity between location lists for AoC 2024 day 1.')
parser.add_argument('INPUT_FILE', help='Locations input file')

args = parser.parse_args()

left = []
right = []

with open(args.INPUT_FILE) as f:
    while True:
        line = f.readline()
        if not line:
            break

        vals = re.split('\s+', line.strip())
        if len(vals) < 2:
            break

        left.append(int(vals[0]))
        right.append(int(vals[1]))

left.sort()
right.sort()

distance = similarity = 0
for index, leftValue in enumerate(left):
    distance += abs(leftValue - right[index])
    similarity += leftValue * right.count(leftValue)

print('Distance is:', distance)
print('Similarity score is:', similarity)
