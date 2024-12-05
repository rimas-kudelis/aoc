#!/usr/bin/env python3

from argparse import ArgumentParser


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


def count_correctly_ordered_updates(manual_updates, page_ordering_rules):
    middle_page_number_sum = 0;

    for update in manual_updates:
        order_is_correct = True
        for rule in page_ordering_rules:
            if rule[0] not in update or rule[1] not in update:
                continue
            if update.index(rule[0]) > update.index(rule[1]):
                order_is_correct = False
                break
        if order_is_correct:
            middle_page_index = int((len(update) - 1)  / 2)
            middle_page_number_sum += update[middle_page_index]

    return middle_page_number_sum


parser = ArgumentParser(description='Sort out the Sleigh launch safety manual updates for AoC day 5.')
parser.add_argument('INPUT_FILE', help='Update data')
args = parser.parse_args()

page_ordering_rules, manual_updates = read_input(args.INPUT_FILE)

print('Sum of correctly ordered update middle page numbers:', count_correctly_ordered_updates(manual_updates, page_ordering_rules))
