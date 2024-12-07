#!/usr/bin/env python3

from argparse import ArgumentParser
import re


def read_input(filename):
    with open(filename) as f:
        instructions = []
        for line in f:
            instruction = re.split(r'[^0-9]+', line.strip())
            instructions.append((int(instruction.pop(0)), [int(operand) for operand in instruction]))

    return instructions


def is_instruction_potentially_valid(test_value, operands):
    # No zeroes in my input
    # try:
    #     if zero_index := operands.index(0):
    #         if is_instruction_potentially_valid(test_value, operands[zero_index + 1:]):
    #             return True
    #         operands.pop(zero_index)
    #         if is_instruction_potentially_valid(test_value, operands.copy()):
    #             return True
    #         return False
    # except ValueError:
    #     pass

    # No negative numbers in my input
    if test_value == sum(operands): return True

    if len(operands) == 2: return test_value == operands[0] * operands[1]

    current_operand = operands.pop()
    if is_instruction_potentially_valid(test_value - current_operand, operands.copy()): return True

    if test_value % current_operand == 0 and is_instruction_potentially_valid(test_value // current_operand, operands.copy()): return True

    return False


def get_total_calibration_result(instructions):
    total_calibration_result = 0

    for test_value, operands in instructions:
        if is_instruction_potentially_valid(test_value, operands):
            total_calibration_result += test_value

    return total_calibration_result


parser = ArgumentParser(description='Find possibly correct calibration instructions for AoC day 7.')
parser.add_argument('INPUT_FILE', help='Calibration instructions file')
args = parser.parse_args()

instructions = read_input(args.INPUT_FILE)
print('Total calibration result:', get_total_calibration_result(instructions))
