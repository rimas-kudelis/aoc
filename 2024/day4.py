#!/usr/bin/env python3

from argparse import ArgumentParser


def read_input(filename):
    chars = []

    with open(filename) as f:
        while True:
            line = f.readline()
            if not line:
                break

            line = line.strip()
            if line == '':
                continue

            chars.append(list(line.strip()))

    return chars


def count_words(chars, word):
    word_chars = list(word)
    found_word_count = 0
    search_dirs = [(line, char) for line in [-1, 0, 1] for char in [-1, 0, 1] if line != 0 or char != 0]
    line_count = len(chars)
    char_count = len(chars[0])

    for start_line_index, line_chars in enumerate(chars):
        for start_char_index, char in enumerate(line_chars):
            if char == word_chars[0]:
                for search_dir in search_dirs:
                    found = 1
                    for word_char_index, word_char in enumerate(word_chars):
                        if word_char_index == 0:
                            continue
                        current_line_index = start_line_index + word_char_index * search_dir[0]
                        if current_line_index < 0 or current_line_index >= line_count:
                            found = 0
                            break
                        current_char_index = start_char_index + word_char_index * search_dir[1]
                        if current_char_index < 0 or current_char_index >= char_count:
                            found = 0
                            break
                        if chars[current_line_index][current_char_index] != word_char:
                            found = 0
                            break
                    found_word_count += found
    return found_word_count


def count_x_words(chars, word):
    if len(word) % 2 != 1:
        raise ValueError('The X-word must have an odd number of characters.')

    word_chars = list(word)

    word_mid_char_index = int((len(word_chars) - 1) / 2)
    word_mid_char = word_chars[word_mid_char_index]
    word_start_chars = list(reversed(word_chars[:word_mid_char_index]))
    word_end_chars = word_chars[word_mid_char_index + 1:]

    found_x_count = 0
    search_dirs = [(line, char) for line in [-1, 1] for char in [-1, 1]]
    line_count = len(chars)
    char_count = len(chars[0])

    for start_line_index, line_chars in enumerate(chars):
        for start_char_index, char in enumerate(line_chars):
            if char == word_mid_char:
                found_for_this_mid_char = 0
                for search_dir in search_dirs:
                    found = 1
                    for word_start_char_index, word_char in enumerate(word_start_chars):
                        current_line_index = start_line_index + search_dir[0] + word_start_char_index * search_dir[0]
                        if current_line_index < 0 or current_line_index >= line_count:
                            found = 0
                            break
                        current_char_index = start_char_index + search_dir[1] + word_start_char_index * search_dir[1]
                        if current_char_index < 0 or current_char_index >= char_count:
                            found = 0
                            break
                        if chars[current_line_index][current_char_index] != word_char:
                            found = 0
                            break
                    if found == 1:
                        for word_end_char_index, word_char in enumerate(word_end_chars):
                            current_line_index = start_line_index - search_dir[0] - word_end_char_index * search_dir[0]
                            if current_line_index < 0 or current_line_index >= line_count:
                                found = 0
                                break
                            current_char_index = start_char_index - search_dir[1] - word_end_char_index * search_dir[1]
                            if current_char_index < 0 or current_char_index >= char_count:
                                found = 0
                                break
                            if chars[current_line_index][current_char_index] != word_char:
                                found = 0
                                break
                    found_for_this_mid_char += found
                if found_for_this_mid_char == 2:
                    found_x_count += 1
    return found_x_count


parser = ArgumentParser(description='Solve the XMAS/X-MAS word search puzzle for the small Elf for AoC 2024 day 4.')
parser.add_argument('INPUT_FILE', help='Word search puzzle input')
args = parser.parse_args()

chars = read_input(args.INPUT_FILE)
print('Found XMAS:', count_words(chars, 'XMAS'))
print('Found X-MAS:', count_x_words(chars, 'MAS'))
