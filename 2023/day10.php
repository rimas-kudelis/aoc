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
const BLANK = '.';
const START = 'S';

$fp = fopen(__DIR__ . '/input/day10.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$map = getMap($fp);

printf('Farthest point is %d steps away.' . PHP_EOL, calculatePathLengthInCleanMap($map) / 2);
printf('There are %d tiles enclosed within the loop.' . PHP_EOL, countEnclosedTiles($map));

function getMap($fp): array
{
    $map = [];
    $startRow = $startCol = null;

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

    return cleanupMap($map, $startRow, $startCol);
}

function getStartingPipe(array $map, int $startRow, int $startCol): string
{
    $north = $east = $south = $west = false;

    if (in_array($map[$startRow - 1][$startCol] ?? BLANK, [SW, SE, NS], true)) {
        $north = true;
    }

    if (in_array($map[$startRow][$startCol - 1] ?? BLANK, [NE, SE, EW], true)) {
        $west = true;
    }

    if (in_array($map[$startRow][$startCol + 1] ?? BLANK, [NW, SW, EW], true)) {
        $east = true;
    }

    if (in_array($map[$startRow + 1][$startCol] ?? BLANK, [NW, NE, NS], true)) {
        $south = true;
    }

    if ((int)$north + (int)$south + (int)$west + (int)$east !== 2) {
        throw new RuntimeException('Unacceptable number of pipes connected to starting position!');
    }

    return match (true) {
        $north && $east => NE,
        $south && $east => SE,
        $south && $west => SW,
        $north && $west => NW,
        $north && $south => NS,
        $east && $west => EW,
    };
}

function cleanupMap(array $map, int $startRow, int $startCol): array
{
    $cleanedMap = array_fill(
        0,
        count($map),
        array_fill(0, count($map[0]), BLANK),
    );

    $cameFrom = match ($map[$startRow][$startCol]) {
        NE, SE, EW => EAST,
        NW, SW => WEST,
        NS => NORTH,
    };

    $currentRow = $startRow;
    $currentCol = $startCol;

    do {
        $currentPipe = $map[$currentRow][$currentCol];
        $cleanedMap[$currentRow][$currentCol] = $currentPipe;

        list ($currentRow, $currentCol, $cameFrom) = match ($currentPipe) {
            NE => match ($cameFrom) {
                NORTH => [$currentRow, $currentCol + 1, WEST],
                EAST => [$currentRow - 1, $currentCol, SOUTH],
            },
            SE => match ($cameFrom) {
                SOUTH => [$currentRow, $currentCol + 1, WEST],
                EAST => [$currentRow + 1, $currentCol, NORTH],
            },
            NW => match ($cameFrom) {
                NORTH => [$currentRow, $currentCol - 1, EAST],
                WEST => [$currentRow - 1, $currentCol, SOUTH],
            },
            SW => match ($cameFrom) {
                SOUTH => [$currentRow, $currentCol - 1, EAST],
                WEST => [$currentRow + 1, $currentCol, NORTH],
            },
            NS => match ($cameFrom) {
                NORTH => [$currentRow + 1, $currentCol, NORTH],
                SOUTH => [$currentRow - 1, $currentCol, SOUTH],
            },
            EW => match ($cameFrom) {
                EAST => [$currentRow, $currentCol - 1, EAST],
                WEST => [$currentRow, $currentCol + 1, WEST],
            },
        };
    } while ($currentRow !== $startRow || $currentCol !== $startCol);

    return $cleanedMap;
}

function calculatePathLengthInCleanMap(array $map): int
{
    $pathLength = 0;

    foreach ($map as $row) {
        $pathLength += count(array_filter($row, static fn(string $tile) => BLANK !== $tile));
    }

    return $pathLength;
}

function countEnclosedTiles(array $map): int
{
    $enclosedTiles = 0;

    foreach ($map as $rowIndex => $row) {
        foreach ($row as $columnIndex => $tile) {
            if (isTileEnclosed($map, $rowIndex, $columnIndex)) {
                $enclosedTiles++;
            }
        }
    }

    return $enclosedTiles;
}

function isTileEnclosed(array $map, int $row, int $col): bool
{
    if (BLANK !== $map[$row][$col]) {
        return false;
    }

    // For a tile to be enclosed within a loop, the loop must have an uneven number of North-South passages to the East
    // or West of it, or an uneven number of East-West passages to the North or South. These four indicators are
    // mutually dependent on a clean map, so it's safe to check just one direction. I'll check for NS passages in East.
    $easternTileCounts = array_count_values(array_slice($map[$row], $col + 1));

    // This is not necessarily the correct amount of passages (pairs of NE and NW may be connected with each other
    // directly or via EW tiles, thus not forming a North-South passage), but it can only be off by an even number,
    // so it works fine for our purpose. The remaining NW and NE tiles will be matched by SE or SW tiles respectively,
    // thus forming a North-South passage.
    $passages = ($easternTileCounts[NS] ?? 0) + ($easternTileCounts[NW] ?? 0) + ($easternTileCounts[NE] ?? 0);

    return 1 === $passages % 2;
}
