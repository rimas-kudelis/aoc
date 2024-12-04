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


parser = ArgumentParser(description='Salvage what\'s possible from program\'s corrupted memory for AoC 2024 day 3.')
parser.add_argument('INPUT_FILE', help='Corrupted memory input file')
args = parser.parse_args()

chars = read_input(args.INPUT_FILE)
print('Found words:', count_words(chars, 'XMAS'))
