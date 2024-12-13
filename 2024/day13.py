#!/usr/bin/env python3

from argparse import ArgumentParser
import re
import numpy


def read_claw_machine_configuration(filename):
    with open(filename) as f:
        lines = [line.strip() for line in f if line.strip() != '']

    machines = []
    for line_num in range(0, len(lines), 3):
        (ax, ay) = [int(move) for move in re.match(r'^Button A: X([+-]\d+), Y([+-]\d+)$', lines[line_num]).groups()]
        (bx, by) = [int(move) for move in re.match(r'^Button B: X([+-]\d+), Y([+-]\d+)$', lines[line_num + 1]).groups()]
        (px, py) = [int(pos) for pos in re.match(r'^Prize: X=([+-]?\d+), Y=([+-]?\d+)$', lines[line_num + 2]).groups()]

        machines.append({'ax': ax, 'ay': ay, 'bx': bx, 'by': by, 'px': px, 'py': py})

    return machines


def calculate_minimum_tokens_to_win(claw_machines, fix_calculation=False):
    tokens = 0

    for claw_machine in claw_machines:
        if solution := solve_claw_machine(claw_machine, fix_calculation):
            tokens += 3 * solution['a'] + solution['b']

    return tokens


def solve_claw_machine(machine, fix_calculation):
    left = numpy.array([[machine['ax'], machine['bx']], [machine['ay'], machine['by']]])
    right = numpy.array([machine['px'], machine['py']]) if not fix_calculation \
        else numpy.array([10000000000000 + machine['px'], 10000000000000 + machine['py']])

    # Somehow numpy returns quite significantly imprecise floats, so I have to round them.
    (a, b) = numpy.round(numpy.linalg.solve(left, right), 4)
    if int(a) == a and int(b) == b:  # and a <= 100 and b <= 100:
        return {'a': int(round(a)), 'b': int(round(b))}

    return None


parser = ArgumentParser(description='Calculate tokens required to win all prices for AoC day 13.')
parser.add_argument('INPUT_FILE', help='Claw machine description file')
args = parser.parse_args()

claw_machines = read_claw_machine_configuration(args.INPUT_FILE)
print('Need {0} tokens to win all possible prizes.'.format(calculate_minimum_tokens_to_win(claw_machines)))
print('Need {0} tokens to win all possible prizes after reviewing the measurements.'.format(
    calculate_minimum_tokens_to_win(claw_machines, True)))
