#!/usr/bin/env python3

from argparse import ArgumentParser


def read_input(filename):
    char_matrix = []

    with open(filename) as f:
        while True:
            line = f.readline()
            if not line:
                break

            line = line.strip()
            if line == '':
                continue

            char_matrix.append(list(line.strip()))

    return char_matrix


def word_found_in_dir(char_matrix, word, start_line_index, start_char_index, line_dir, char_dir):
    matrix_line_count = len(char_matrix)
    matrix_char_count = len(char_matrix[0])

    if line_dir == 0 and char_dir == 0:
        return 0 <= start_line_index < matrix_line_count \
            and 0 <= start_char_index < matrix_char_count \
            and word == char_matrix[start_line_index][start_char_index]

    for word_char_index, word_char in enumerate(word):
        current_line_index = start_line_index + word_char_index * line_dir
        if current_line_index < 0 or current_line_index >= matrix_line_count:
            return False

        current_char_index = start_char_index + word_char_index * char_dir
        if current_char_index < 0 or current_char_index >= matrix_char_count:
            return False

        if char_matrix[current_line_index][current_char_index] != word_char:
            return False

    return True


def count_words(char_matrix, word):
    found_word_count = 0
    if len(word) < 2:
        raise ValueError('The word that is being counted must have at least 2 characters.')

    for start_line_index in range(len(char_matrix)):
        for start_char_index in range(len(char_matrix[0])):
            for line_dir in [-1, 0, 1]:
                for char_dir in [-1, 0, 1]:
                    if word_found_in_dir(char_matrix, word, start_line_index, start_char_index, line_dir, char_dir):
                        found_word_count += 1

    # If the word is a palindrome, each occurrence has been counted twice.
    if word == word[::-1]:
        return int(found_word_count / 2)

    return found_word_count


def count_x_words(char_matrix, word):
    word_length = len(word)
    if word_length < 3 or len(word) % 2 != 1:
        raise ValueError('The X-word must have a odd number of characters (3 or more).')

    word_mid_char_index = int((len(word) - 1) / 2)
    word_start_reversed = word[word_mid_char_index::-1]
    word_end = word[word_mid_char_index:]
    found_x_word_count = 0

    for start_line_index in range(len(char_matrix)):
        for start_char_index in range(len(char_matrix[0])):
            found_word_mid_here = 0
            # Track checked directions so we don't count the same word twice if it's a palindrome.
            # This is also an optimization: if the word is not a palindrome and word_start_reversed
            # is found in direction (X, Y), that means that searching for the word_start_revered
            # in direction (-X, -Y) is pointless, because word_end won't match what is in direction (X, Y) anyway.
            checked_dirs = []
            for line_dir in [-1, 1]:
                for char_dir in [-1, 1]:
                    if [line_dir, char_dir] in checked_dirs:
                        continue
                    checked_dirs.append([line_dir, char_dir])
                    if word_found_in_dir(char_matrix, word_start_reversed, start_line_index, start_char_index, line_dir, char_dir):
                        checked_dirs.append([-line_dir, -char_dir])
                        if word_found_in_dir(char_matrix, word_end, start_line_index, start_char_index, -line_dir, -char_dir):
                            found_word_mid_here += 1
            if found_word_mid_here == 2:
                found_x_word_count += 1

    return found_x_word_count


parser = ArgumentParser(description='Solve the XMAS/X-MAS word search puzzle for the small Elf for AoC 2024 day 4.')
parser.add_argument('INPUT_FILE', help='Word search puzzle input')
args = parser.parse_args()

char_matrix = read_input(args.INPUT_FILE)

print('Found XMAS:', count_words(char_matrix, 'XMAS'))
print('Found X-MAS:', count_x_words(char_matrix, 'MAS'))
