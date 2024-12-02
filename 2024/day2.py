#!/usr/bin/env python3

from argparse import ArgumentParser
from enum import Enum


class ReportSafety(Enum):
    SAFE = 0
    SAFE_WHEN_DAMPENED = 1
    UNSAFE = 2


class ReportDynamic(Enum):
    INCREASING = 0
    DECREASING = 1


parser = ArgumentParser(description='Analyze unusual data from reports for AoC 2024 day 2.')
parser.add_argument('INPUT_FILE', help='Reports input file')
args = parser.parse_args()


def get_dynamic(level1, level2):
    return ReportDynamic.INCREASING if level1 < level2 else ReportDynamic.DECREASING


def safe_difference(level1, level2):
    return 1 <= abs(level1 - level2) <= 3


def simulate_problem_dampener(report, try_remove_indexes):
    for remove_index in try_remove_indexes:
        report_copy = report.copy()
        report_copy.pop(remove_index)
        if analyze_report(report_copy, False) == ReportSafety.SAFE:
            return ReportSafety.SAFE_WHEN_DAMPENED
    return ReportSafety.UNSAFE


def analyze_report(report, enable_problem_dampener=True):
    dynamic = None
    for index, level in enumerate(report):
        if index == 0:
            continue

        previous_level = report[index - 1]

        # Any two adjacent levels differ by at least one and at most three.
        if not safe_difference(previous_level, level):
            if not enable_problem_dampener:
                return ReportSafety.UNSAFE

            # If this is the beginning of the report, try removing the previous element as well,
            # because report level dynamic is not yet known for sure.
            try_remove_indexes = [index - 1, index] if index <= 2 else [index]
            return simulate_problem_dampener(report, try_remove_indexes)

        if index == 1:
            dynamic = get_dynamic(previous_level, level)
            continue

        # Any two adjacent levels differ by at least one and at most three.
        if dynamic != get_dynamic(previous_level, level):
            if not enable_problem_dampener:
                return ReportSafety.UNSAFE

            # Removing either the previous, or the current level may fix broken report level dynamic.
            # If we're at index 2, also try removing level #0, since it may be the bad one as well.
            try_remove_indexes = [0, 1, 2] if index == 2 else [index - 1, index]
            return simulate_problem_dampener(report, try_remove_indexes)
    return ReportSafety.SAFE


reports = []
with open(args.INPUT_FILE) as f:
    while True:
        line = f.readline()
        if not line:
            break

        line = line.strip()
        if line == '':
            continue

        reports.append([int(report) for report in line.split(' ')])

safe_reports = []
dampened_safe_reports = []
unsafe_reports = []
for report in reports:
    report_safety = analyze_report(report)

    if report_safety == ReportSafety.SAFE:
        safe_reports.append(report)
    elif report_safety == ReportSafety.SAFE_WHEN_DAMPENED:
        dampened_safe_reports.append(report)
    else:
        unsafe_reports.append(report)

print('Safe reports:', len(safe_reports))
print('Safe reports with problem dampener on:', len(dampened_safe_reports) + len(safe_reports))
print('Unsafe reports:', len(unsafe_reports))
