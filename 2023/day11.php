<?php

const BLANK = '.';
const GALAXY = '#';

$fp = fopen(__DIR__ . '/input/day11.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$map = expandMap(getMap($fp));

//foreach($map as $row) {
//    echo implode('', $row) . PHP_EOL;
//}

$galaxies = getGalaxies($map);

//foreach ($galaxies as $galaxy) {
//    echo implode (', ', $galaxy) . PHP_EOL;
//}

$sumOfDistances = 0;

foreach ($galaxies as $firstGalaxyId => $galaxy) {
    for ($nextGalaxyId = $firstGalaxyId + 1; $nextGalaxyId < count($galaxies); $nextGalaxyId++) {
        $sumOfDistances += getDistanceBetweenGalaxies($galaxy, $galaxies[$nextGalaxyId]);
    }
}

echo 'Sum of distances between galaxies: ' . $sumOfDistances . PHP_EOL;

function getMap($fp): array
{
    $map = [];

    while (false !== $line = fgets($fp)) {
        if ("\n" === $line) {
            continue;
        }

        $mapRow = str_split(trim($line));

        $map[] = $mapRow;
    }

    return $map;
}

function expandMap(array $map): array
{
    $expanded = [];
    $columnsInMap = count($map[0]);
    $expandColumns = array_fill(0, $columnsInMap, true);

    foreach ($map as $row) {
        $expanded[] = $row;

        if ([BLANK => $columnsInMap] === array_count_values($row)) {
            $expanded[] = $row;
            continue;
        }

        foreach ($row as $columnIndex => $marker) {
            if (GALAXY === $marker) {
                $expandColumns[$columnIndex] = false;
            }
        }
    }

    foreach ($expanded as $rowIndex => $row) {
        $expandedRow = [];

        foreach ($row as $columnIndex => $marker) {
            $expandedRow[] = $marker;
            if ($expandColumns[$columnIndex]) {
                $expandedRow[] = $marker;
            }
        }

        $expanded[$rowIndex] = $expandedRow;
    }

    return $expanded;
}

function getGalaxies(array $map): array
{
    $galaxies = [];

    foreach ($map as $rowIndex => $row) {
        foreach ($row as $columnIndex => $marker) {
            if (GALAXY === $marker) {
                $galaxies[] = [$rowIndex, $columnIndex];
            }
        }
    }

    return $galaxies;
}

function getDistanceBetweenGalaxies(array $galaxy1, array $galaxy2): int
{
    return abs($galaxy1[0] - $galaxy2[0]) + abs($galaxy1[1] - $galaxy2[1]);
}