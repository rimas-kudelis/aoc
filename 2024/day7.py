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


def is_equation_potentially_valid(test_value, operands, try_concatenate):
    # Note: no zeroes or negative numbers are expected in input
    if test_value == sum(operands): return True

    if len(operands) == 2:
        return test_value == operands[0] * operands[1] or str(test_value) == str(operands[0]) + str(operands[1])

    current_operand = operands.pop()
    if is_equation_potentially_valid(test_value - current_operand, operands.copy(), try_concatenate):
        return True

    if test_value % current_operand == 0 and is_equation_potentially_valid(test_value // current_operand, operands.copy(), try_concatenate):
        return True

    if try_concatenate:
        test_value, current_operand = str(test_value), str(current_operand)
        if test_value.endswith(current_operand) and is_equation_potentially_valid(int(test_value.removesuffix(current_operand)), operands.copy(), try_concatenate):
            return True

    return False


def get_total_calibration_result(instructions, try_concatenate):
    total_calibration_result = 0

    for test_value, operands in instructions:
        if is_equation_potentially_valid(test_value, operands.copy(), try_concatenate):
            total_calibration_result += test_value

    return total_calibration_result


parser = ArgumentParser(description='Find possibly correct calibration equations for AoC day 7.')
parser.add_argument('INPUT_FILE', help='Calibration equations file')
args = parser.parse_args()

instructions = read_input(args.INPUT_FILE)
print('Total calibration result without concatenation:', get_total_calibration_result(instructions, False))
print('Total calibration result with concatenation:', get_total_calibration_result(instructions, True))
