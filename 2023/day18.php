<?php

const LEFT = 'L';
const RIGHT = 'R';
const UP = 'U';
const DOWN = 'D';

$fp = fopen(__DIR__ . '/input/day18.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$instructions = getInstructions($fp);
$map = digLagoon($instructions);

printMap($map);

printf('Lagoon size is %d cubic meters.' . PHP_EOL, calculateLagoonSize($map));

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
    $trenchMap = $fieldMap = digTrench($instructions);

    foreach ($fieldMap as $rowIndex => $row) {
        $dig = false;

        foreach ($row as $columnIndex => $tile) {
            if (1 === $tile) {
                if (1 === ($trenchMap[$rowIndex - 1][$columnIndex] ?? 0)) {
                    $dig = !$dig;
                }

                continue;
            }

            if ($dig) {
                $fieldMap[$rowIndex][$columnIndex] = 1;
            }
        }
    }

    return $fieldMap;
}

function digTrench(array $instructions): array
{
    $x = $y = $minX = $minY = $maxX = $maxY = 0;

    // Figure out the outer dimensions of the rectangle that the lagoon fits in, and the digging start point
    foreach ($instructions as list($direction, $distance, $color)) {
        switch ($direction) {
            case DOWN:
                $x += $distance;
                $maxX = max($maxX, $x);
                break;
            case UP:
                $x -= $distance;
                $minX = min($minX, $x);
                break;
            case RIGHT:
                $y += $distance;
                $maxY = max($maxY, $y);
                break;
            case LEFT:
                $y -= $distance;
                $minY = min($minY, $y);
                break;
            default:
                throw new RuntimeException('Invalid direction: ' . $direction . '!');
        }
    }

    $fieldMap = array_fill(0, $maxX - $minX +1, array_fill(0, $maxY - $minY + 1, 0));
    $x = -$minX;
    $y = -$minY;
    $fieldMap[$x][$y] = 1;

    foreach ($instructions as list($direction, $distance, $color)) {
        switch ($direction) {
            case DOWN:
                for ($i = 0; $i < $distance; ++$i) {
                    $fieldMap[++$x][$y] = 1;
                }

                break;
            case UP:
                for ($i = 0; $i < $distance; ++$i) {
                    $fieldMap[--$x][$y] = 1;
                }

                break;
            case RIGHT:
                for ($i = 0; $i < $distance; ++$i) {
                    $fieldMap[$x][++$y] = 1;
                }

                break;
            case LEFT:
                for ($i = 0; $i < $distance; ++$i) {
                    $fieldMap[$x][--$y] = 1;
                }

                break;
        }
    }

    return $fieldMap;
}

function calculateLagoonSize(array $lagoon): int
{
    return array_sum(array_map('array_sum', $lagoon));
}

function printMap(array $map): void
{
    for ($row = 0; $row < count($map); $row += 4) {
        for ($column = 0; $column < count($map[$row]); $column += 2) {
            $codePoint = 0x2800;

            foreach([
                $map[$row][$column] ?? '0',
                $map[$row + 1][$column] ?? '0',
                $map[$row + 2][$column] ?? '0',
                $map[$row][$column + 1] ?? '0',
                $map[$row + 1][$column + 1] ?? '0',
                $map[$row + 2][$column + 1] ?? '0',
                $map[$row + 3][$column] ?? '0',
                $map[$row + 3][$column + 1] ?? '0',
            ] as $index => $dot) {
                if (1 === $dot) {
                    $codePoint += (2**$index);
                }
            }

            echo mb_chr($codePoint, 'UTF-8');
        }

        echo PHP_EOL;
    }
}
