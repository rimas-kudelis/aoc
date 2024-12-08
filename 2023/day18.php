<?php

const LEFT = 'L';
const RIGHT = 'R';
const UP = 'U';
const DOWN = 'D';
const DUG = 'X';

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day18.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$instructions = getInstructions($fp);
$map = digLagoon($instructions);

//printMap($map);

printf('Lagoon size is %d cubic meters (part 1).' . PHP_EOL, calculateLagoonSize($map));

$instructions = fixInstructions($instructions);
$map = digLagoon($instructions);

printf('Lagoon size is %d cubic meters (part 2).' . PHP_EOL, calculateLagoonSize($map));

echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function getInstructions($fp): array
{
    $instructions = [];

    while (false !== $line = fgets($fp)) {
        if ("\n" === $line) {
            continue;
        }

        $instruction = explode(' ', trim($line));
        $instruction[2] = substr($instruction[2], 1, 7);

        $instructions[] = $instruction;
    }

    return $instructions;
}

function digLagoon(array $instructions): array
{
    $trenchMap = $map = digTrench($instructions);

    printMap($trenchMap);

    $map = digLagoonInterior($trenchMap);

    printMap($map);

    return $map;
}

function digLagoonInterior(array $trenchMap): array
{
    $map = $trenchMap;

    foreach ($map as $rowIndex => $row) {
        $enclosed = $trenchEncountered = $trenchWentUp = $trenchWentDown = false;

        foreach ($row as $colIndex => $tile) {
            if (true === $tile[DUG]) {
                $trenchEncountered = true;

                if ($trenchMap[$rowIndex - 1][$colIndex][DUG] ?? false) {
                    $trenchWentUp = !$trenchWentUp;
                }

                if ($trenchMap[$rowIndex + 1][$colIndex][DUG] ?? false) {
                    $trenchWentDown = !$trenchWentDown;
                }

                continue;
            }

            if ($trenchEncountered && $trenchWentUp && $trenchWentDown) {
                $enclosed = !$enclosed;
            }

            $trenchEncountered = $trenchWentUp = $trenchWentDown = false;

            if ($enclosed) {
                $map[$rowIndex][$colIndex][DUG] = true;
            }
        }
    }

    return $map;
}

function digTrench(array $instructions): array
{
    $rowIndex = $colIndex = 0;
    $rowLines = $colLines = [0 => 0, 1 => 1];

    // Find all X and Y values (breakpoints) where lagoon corners occur. Each edge may fold in either direction, so
    // it will be considered as two distinct breakpoints (x and x+1) just in case.
    foreach ($instructions as list($direction, $distance, $color)) {
        switch ($direction) {
            case DOWN:
                $rowIndex += $distance;
                break;
            case UP:
                $rowIndex -= $distance;
                break;
            case RIGHT:
                $colIndex += $distance;
                break;
            case LEFT:
                $colIndex -= $distance;
                break;
            default:
                throw new RuntimeException('Invalid direction: ' . $direction . '!');
        }

        $rowLines[$rowIndex] = $rowIndex;
        $rowLines[$rowIndex + 1] = $rowIndex + 1;
        $colLines[$colIndex] = $colIndex;
        $colLines[$colIndex + 1] = $colIndex + 1;
    }

    ksort($rowLines);
    ksort($colLines);

    $startRow = array_shift($rowLines);
    $startCol = array_shift($colLines);

    $fieldMap = [];
    $lastRow = $startRow;

    foreach ($rowLines as $rowLine) {
        $fieldMap[$lastRow] = [];
        $lastCol = $startCol;

        foreach ($colLines as $colLine) {
            $fieldMap[$lastRow][$lastCol] = [DOWN => $rowLine - $lastRow, RIGHT => $colLine - $lastCol, DUG => false];
            $lastCol = $colLine;
        }

        $lastRow = $rowLine;
    }

    $fieldMap[0][0][DUG] = true;

    $lastRow = $lastCol = 0;
    foreach ($instructions as list($direction, $distance, $color)) {
        switch ($direction) {
            case DOWN:
                $minRow = $lastRow + 1;
                $maxRow = $lastRow = $lastRow + $distance;
                $minCol = $maxCol = $lastCol;

                break;
            case UP:
                $maxRow = $lastRow - 1;
                $minRow = $lastRow = $lastRow - $distance;
                $minCol = $maxCol = $lastCol;

                break;
            case RIGHT:
                $minRow = $maxRow = $lastRow;
                $minCol = $lastCol + 1;
                $maxCol = $lastCol = $lastCol + $distance;

                break;
            case LEFT:
                $minRow = $maxRow = $lastRow;
                $maxCol = $lastCol - 1;
                $minCol = $lastCol = $lastCol - $distance;

                break;
            default:
                throw new RuntimeException('Invalid direction: "' . $direction . '"!');
        }

        foreach ($fieldMap as $rowIndex => $row) {
            if ($minRow > $rowIndex || $maxRow < $rowIndex) {
                continue;
            }

            foreach ($row as $colIndex => $data) {
                if ($minCol > $colIndex || $maxCol < $colIndex) {
                    continue;
                }

                $fieldMap[$rowIndex][$colIndex][DUG] = true;
            }
        }
    }

    // Return arrays with incremental indices starting at 0 (a list of lists).
    return array_values(array_map('array_values', $fieldMap));
}

function calculateLagoonSize(array $lagoon): int
{
    return array_sum(
        array_map(
            'array_sum',
            array_map(
                static fn(array $row): array => array_map(
                    static fn(array $tile) => $tile[DUG] ? $tile[DOWN] * $tile[RIGHT] : 0,
                    $row
                ),
                $lagoon,
            ),
        ),
    );
}

function fixInstructions(array $instructions): array
{
    return array_map(
        static fn(array $instruction): array => [
            [RIGHT, DOWN, LEFT, UP][substr($instruction[2], 6)],
            intval(substr($instruction[2], 1, 5), 16),
            '',
        ],
        $instructions,
    );
}

function debugMap(array $map): void
{
    return;
    foreach ($map as $x => $row) {
        echo $x . ': ';
        foreach ($row as $y => $colData) {
            printf('[%d %d×%d, %s] ', $y, $colData[DOWN], $colData[RIGHT], $colData[DUG] ? '#' : '.');
        }

        echo PHP_EOL;
    }

    echo PHP_EOL;
}

function printMap(array $map): void
{
    for ($row = 0; $row < count($map); $row += 4) {
        for ($column = 0; $column < count($map[$row]); $column += 2) {
            $codePoint = 0x2800;

            foreach ([
                         ($map[$row][$column][DUG] ?? false) ? 1 : 0,
                         ($map[$row + 1][$column][DUG] ?? false) ? 1 : 0,
                         ($map[$row + 2][$column][DUG] ?? false) ? 1 : 0,
                         ($map[$row][$column + 1][DUG] ?? false) ? 1 : 0,
                         ($map[$row + 1][$column + 1][DUG] ?? false) ? 1 : 0,
                         ($map[$row + 2][$column + 1][DUG] ?? false) ? 1 : 0,
                         ($map[$row + 3][$column][DUG] ?? false) ? 1 : 0,
                         ($map[$row + 3][$column + 1][DUG] ?? false) ? 1 : 0,
                     ] as $index => $dot) {
                if (1 === $dot) {
                    $codePoint += (2 ** $index);
                }
            }

            echo mb_chr($codePoint, 'UTF-8');
        }

        echo PHP_EOL;
    }

    echo PHP_EOL;
}
