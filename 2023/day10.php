<?php

const EAST = 'E';
const WEST = 'W';
const NORTH = 'N';
const SOUTH = 'S';

const NS = '|';
const EW = '-';
const NE = 'L';
const NW = 'J';
const SW = '7';
const SE = 'F';
const START = 'S';

$fp = fopen(__DIR__ . '/input/day10.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$startRow = $startCol = 0;
$map = getMap($fp, $startRow, $startCol);

$path = getPath($map, $startRow, $startCol);

printf('Farthest point is %d steps away.' . PHP_EOL, count($path) / 2);

function getMap($fp, int &$startRow, int &$startCol): array
{
    $map = [];
    while (false !== $line = fgets($fp)) {
        if ("\n" === $line) {
            continue;
        }

        $mapRow = str_split(trim($line));
        if (false !== $startPos = array_search(START, $mapRow, true)) {
            $startRow = count($map);
            $startCol = $startPos;
        }

        $map[] = $mapRow;
    }

    $map[$startRow][$startCol] = getStartingPipe($map, $startRow, $startCol);

    return $map;
}

function getStartingPipe(array $map, int $startRow, int $startCol): string
{
    $north = $east = $south = $west = false;

    if (in_array($map[$startRow - 1][$startCol] ?? '.', [SW, SE, NS], true)) {
        $north = true;
    }

    if (in_array($map[$startRow][$startCol - 1] ?? '.', [NE, SE, EW], true)) {
        $west = true;
    }

    if (in_array($map[$startRow][$startCol + 1] ?? '.', [NW, SW, EW], true)) {
        $east = true;
    }

    if (in_array($map[$startRow + 1][$startCol] ?? '.', [NW, NE, NS], true)) {
        $south = true;
    }

    if ((int)$north + (int)$south + (int)$west + (int)$east > 2) {
        throw new RuntimeException('Too many pipes connecting to starting position!' . ((int)$north + (int)$south + (int)$west + (int)$east));
    }

    return match (true) {
        $north && $east => NE,
        $south && $east => SE,
        $south && $west => SW,
        $north && $west => NW,
        $north && $south => NS,
        $east && $west => EW,
        default => throw new RuntimeException('Too few pipes connecting to starting position!'),
    };
}

function getPath(array $map, int $startRow, int $startCol): array
{
    $path = [];
    $cameFrom = null;
    $currentRow = $startRow;
    $currentCol = $startCol;

    do {
        $currentPipe = $map[$currentRow][$currentCol];
        $path[] = $currentPipe;

        list ($currentRow, $currentCol, $cameFrom) = match ($currentPipe) {
            NE => match ($cameFrom) {
                NORTH => [$currentRow, $currentCol + 1, WEST],
                default => [$currentRow - 1, $currentCol, SOUTH],
            },
            SE => match ($cameFrom) {
                SOUTH => [$currentRow, $currentCol + 1, WEST],
                default => [$currentRow + 1, $currentCol, NORTH],
            },
            NW => match ($cameFrom) {
                NORTH => [$currentRow, $currentCol - 1, EAST],
                default => [$currentRow - 1, $currentCol, SOUTH],
            },
            SW => match ($cameFrom) {
                SOUTH => [$currentRow, $currentCol - 1, EAST],
                default => [$currentRow + 1, $currentCol, NORTH],
            },
            NS => match ($cameFrom) {
                NORTH => [$currentRow + 1, $currentCol, NORTH],
                default => [$currentRow - 1, $currentCol, SOUTH],
            },
            EW => match ($cameFrom) {
                WEST => [$currentRow, $currentCol + 1, WEST],
                default => [$currentRow, $currentCol - 1, EAST],
            },
            default => throw new RuntimeException('Unexpected pipe: ' . $currentPipe),
        };
    } while ($currentRow !== $startRow || $currentCol !== $startCol);

    return $path;
}
