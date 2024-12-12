#!/usr/bin/env python3

from argparse import ArgumentParser


def read_garden_map(filename):
    with open(filename) as f:
        area_map = [[{'plant': char} for char in line.strip()] for line in f if line.strip() != '']

    return area_map


def get_regions(garden_map):
    garden_map = garden_map.copy()
    next_region_number = 0
    regions = {}

    for row_index, row in enumerate(garden_map):
        for plot_index, plot in enumerate(row):
            plot['fenced_sides'] = 4
            potential_region_numbers = set()
            for direction in ['north', 'west', 'south', 'east']:
                adjacent_plot = get_adjacent_plot(garden_map, row_index, plot_index, direction)
                if adjacent_plot is not None and adjacent_plot['plant'] == plot['plant']:
                    plot['fenced_sides'] -= 1
                    if 'region_number' in adjacent_plot:
                        potential_region_numbers.add(adjacent_plot['region_number'])

            plot['fence_corners'] = 0
            for dir1 in ('north', 'south'):
                for dir2 in ('west', 'east'):
                    adj1 = get_adjacent_plot(garden_map, row_index, plot_index, dir1)
                    adj2 = get_adjacent_plot(garden_map, row_index, plot_index, dir2)
                    adj3 = get_adjacent_plot(garden_map, row_index, plot_index, dir1 + dir2)
                    if adj1 is not None and adj2 is not None and adj3 is not None \
                            and plot['plant'] == adj1['plant'] \
                            and plot['plant'] == adj2['plant'] \
                            and plot['plant'] != adj3['plant']:
                        # inwards corner
                        plot['fence_corners'] += 1
                    elif (adj1 is None or plot['plant'] != adj1['plant']) and (
                            adj2 is None or plot['plant'] != adj2['plant']):
                        # outwards corner
                        plot['fence_corners'] += 1

            if len(potential_region_numbers) == 0:
                plot['region_number'] = next_region_number
                regions[next_region_number] = {
                    'plant': plot['plant'],
                    'plots': 1,
                    'fenced_sides': plot['fenced_sides'],
                    'fence_corners': plot['fence_corners'],
                }
                next_region_number += 1
            else:
                use_region_number = potential_region_numbers.pop()
                reassign_region_numbers = potential_region_numbers
                plot['region_number'] = use_region_number
                regions[use_region_number]['plots'] += 1
                regions[use_region_number]['fenced_sides'] += plot['fenced_sides']
                regions[use_region_number]['fence_corners'] += plot['fence_corners']

                if len(reassign_region_numbers) != 0:
                    for reassign_region_number in reassign_region_numbers:
                        regions[use_region_number]['plots'] += regions[reassign_region_number]['plots']
                        regions[use_region_number]['fenced_sides'] += regions[reassign_region_number]['fenced_sides']
                        regions[use_region_number]['fence_corners'] += regions[reassign_region_number]['fence_corners']
                        del (regions[reassign_region_number])
                    for reassign_row_index, reassign_row in enumerate(garden_map[:row_index + 1]):
                        for reassign_plot_index, reassign_plot in enumerate(reassign_row):
                            if 'region_number' not in reassign_plot:
                                # No further plots have been processed yet
                                break
                            if reassign_plot['region_number'] in reassign_region_numbers:
                                reassign_plot['region_number'] = use_region_number

    return regions


def get_adjacent_plot(garden_map, row_index, plot_index, direction):
    match direction:
        case 'north':
            row_index -= 1
        case 'south':
            row_index += 1
        case 'west':
            plot_index -= 1
        case 'east':
            plot_index += 1
        case 'northwest':
            row_index -= 1
            plot_index -= 1
        case 'northeast':
            row_index -= 1
            plot_index += 1
        case 'southwest':
            row_index += 1
            plot_index -= 1
        case 'southeast':
            row_index += 1
            plot_index += 1
        case _:
            raise ValueError('Invalid direction')

    return garden_map[row_index][plot_index] if 0 <= row_index < len(garden_map) and 0 <= plot_index < len(
        garden_map[0]) else None


def calculate_fencing_costs(garden_map):
    fencing_cost = discounted_fencing_cost = 0
    for region_number, region_data in get_regions(garden_map).items():
        fencing_cost += region_data['plots'] * region_data['fenced_sides']
        discounted_fencing_cost += region_data['plots'] * region_data['fence_corners']

    return fencing_cost, discounted_fencing_cost


parser = ArgumentParser(description='Count garden fencing cost for AoC day 12.')
parser.add_argument('INPUT_FILE', help='Garden map file')
args = parser.parse_args()

garden_map = read_garden_map(args.INPUT_FILE)

fencing_cost, discounted_fencing_cost = calculate_fencing_costs(garden_map)

print('Original garden fencing cost is:', fencing_cost)
print('Discounted garden fencing cost is:', discounted_fencing_cost)
