#!/usr/bin/env python3

from argparse import ArgumentParser


def read_garden_map(filename):
    with open(filename) as f:
        area_map = [[{'plant': char} for char in line.strip()] for line in f if line.strip() != '']

    return area_map


def get_regions(garden_map):
    garden_map = garden_map.copy()
    next_region_number = 0
    row_count = len(garden_map)
    col_count = len(garden_map[0])
    regions = {}

    for row_index, row in enumerate(garden_map):
        for plot_index, plot in enumerate(row):
            plot['fenced_sides'] = 4
            potential_region_numbers = set()
            if row_index > 0 and garden_map[row_index - 1][plot_index]['plant'] == plot['plant']:
                # No fence in the North
                plot['fenced_sides'] -= 1
                potential_region_numbers.add(garden_map[row_index - 1][plot_index]['region_number'])
            if row_index < row_count - 1 and garden_map[row_index + 1][plot_index]['plant'] == plot['plant']:
                # No fence in the South
                plot['fenced_sides'] -= 1
            if plot_index > 0 and garden_map[row_index][plot_index - 1]['plant'] == plot['plant']:
                # No fence in the West
                plot['fenced_sides'] -= 1
                potential_region_numbers.add(garden_map[row_index][plot_index - 1]['region_number'])
            if plot_index < col_count - 1 and garden_map[row_index][plot_index + 1]['plant'] == plot['plant']:
                # No fence in the East
                plot['fenced_sides'] -= 1
            if len(potential_region_numbers) == 0:
                plot['region_number'] = next_region_number
                regions[next_region_number] = {'plant': plot['plant'], 'plots': 1, 'fenced_sides': plot['fenced_sides']}
                next_region_number += 1
            else:
                use_region_number = potential_region_numbers.pop()
                reassign_region_numbers = potential_region_numbers
                plot['region_number'] = use_region_number
                regions[use_region_number]['plots'] += 1
                regions[use_region_number]['fenced_sides'] += plot['fenced_sides']
                if len(reassign_region_numbers) != 0:
                    for reassign_region_number in reassign_region_numbers:
                        regions[use_region_number]['plots'] += regions[reassign_region_number]['plots']
                        regions[use_region_number]['fenced_sides'] += regions[reassign_region_number]['fenced_sides']
                        del (regions[reassign_region_number])
                    for reassign_row_index, reassign_row in enumerate(garden_map[:row_index + 1]):
                        for reassign_plot_index, reassign_plot in enumerate(reassign_row):
                            if 'region_number' not in reassign_plot:
                                # No further plots have been processed yet
                                break
                            if reassign_plot['region_number'] in reassign_region_numbers:
                                reassign_plot['region_number'] = use_region_number

    return regions


def calculate_fencing_cost(garden_map):
    fencing_cost = 0
    for region_number, region_data in get_regions(garden_map).items():
        fencing_cost += region_data['plots'] * region_data['fenced_sides']

    return fencing_cost


def print_map(garden_map):
    for row in garden_map:
        row_text = ''
        for plot in row:
            row_text += str(plot['region_number']) + plot['plant'] + str(plot['fenced_sides']) + ' '
        print(row_text)


parser = ArgumentParser(description='Count garden fencing cost for AoC day 12.')
parser.add_argument('INPUT_FILE', help='Garden map file')
args = parser.parse_args()

garden_map = read_garden_map(args.INPUT_FILE)

print('Garden fencing cost is:', calculate_fencing_cost(garden_map))
