#!/usr/bin/env python3

from argparse import ArgumentParser
import functools


def read_input(filename):
    page_ordering_rules = []
    manual_updates = []

    with open(filename) as f:
        while True:
            line = f.readline()
            if not line:
                break

            line = line.strip()
            if line == '':
                continue

            if line.find('|') != -1:
                page_ordering_rules.append(list(map(int, line.split('|'))))
            else:
                manual_updates.append(list(map(int, line.split(','))))

    return page_ordering_rules, manual_updates


def get_middle_page_number_sums(manual_updates, page_ordering_rules):
    correctly_ordered_middle_page_number_sum = incorrectly_ordered_middle_page_number_sum = 0;

    for update in manual_updates:
        fixed_update = reorder_pages(update, page_ordering_rules)
        middle_page_index = int((len(update) - 1) / 2)

        if fixed_update == update:
            correctly_ordered_middle_page_number_sum += update[middle_page_index]
        else:
            incorrectly_ordered_middle_page_number_sum += fixed_update[middle_page_index]

    return correctly_ordered_middle_page_number_sum, incorrectly_ordered_middle_page_number_sum


def reorder_pages(manual_update, page_ordering_rules):
    def compare(page1, page2):
        try:
            rule = next(rule for rule in page_ordering_rules if page1 in rule and page2 in rule)
            return rule.index(page1) - rule.index(page2)
        except StopIteration:
            return 0

    return sorted(manual_update, key=functools.cmp_to_key(compare))


parser = ArgumentParser(description='Sort out the Sleigh launch safety manual updates for AoC day 5.')
parser.add_argument('INPUT_FILE', help='Update data')
args = parser.parse_args()

page_ordering_rules, manual_updates = read_input(args.INPUT_FILE)
correct_sum, incorrect_sum = get_middle_page_number_sums(manual_updates, page_ordering_rules)

print('Sum of correctly ordered update middle page numbers:', correct_sum)
print('Sum of incorrectly ordered update middle page numbers:', incorrect_sum)
