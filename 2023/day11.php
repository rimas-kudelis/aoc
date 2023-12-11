<?php

const BLANK = '.';
const GALAXY = '#';

$fp = fopen(__DIR__ . '/input/day11.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$map = getMap($fp);

//foreach($map as $row) {
//    echo implode('', $row) . PHP_EOL;
//}

$galaxies = getGalaxies($map);

//foreach ($galaxies as $galaxy) {
//    echo implode (', ', $galaxy) . PHP_EOL;
//}

$rowsToExpand = getRegionsToExpand(count($map), array_column($galaxies, 0));
$columnsToExpand = getRegionsToExpand(count($map), array_column($galaxies, 1));

//echo 'Rows to expand: ' . implode(', ', $rowsToExpand) . PHP_EOL;
//echo 'Columns to expand: ' . implode(', ', $columnsToExpand) . PHP_EOL;

echo 'Sum of distances between galaxies (part 1): ' . getSumOfDistancesBetweenGalaxies($galaxies, $rowsToExpand, $columnsToExpand, 2) . PHP_EOL;
echo 'Sum of distances between galaxies (part 2): ' . getSumOfDistancesBetweenGalaxies($galaxies, $rowsToExpand, $columnsToExpand, 1000000) . PHP_EOL;

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


function getRegionsToExpand(int $numRegions, array $galaxyRegions): array
{
    $expandRegions = array_fill(0, $numRegions, true);

    foreach ($galaxyRegions as $galaxyRegion) {
        $expandRegions[$galaxyRegion] = false;
    }

    $regionsToExpand = [];

    foreach ($expandRegions as $regionIndex => $expandRow) {
        if ($expandRow) {
            $regionsToExpand[] = $regionIndex;
        }
    }

    return $regionsToExpand;
}

function adjustGalaxies(array $galaxies, array $rowsToExpand, array $columnsToExpand, int $expandFactor): array
{

}

function getSumOfDistancesBetweenGalaxies(
    array $galaxies,
    array $rowsToExpand,
    array $columnsToExpand,
    int $expandFactor,
): int {
    $sumOfDistances = 0;

    foreach ($galaxies as $galaxy1Id => $galaxy1) {
        for ($galaxy2Id = $galaxy1Id + 1; $galaxy2Id < count($galaxies); $galaxy2Id++) {
            $sumOfDistances += getDistanceBetweenGalaxies(
                $galaxy1,
                $galaxies[$galaxy2Id],
                $rowsToExpand,
                $columnsToExpand,
                $expandFactor,
            );
        }
    }

    return $sumOfDistances;
}

function getDistanceBetweenGalaxies(
    array $galaxy1,
    array $galaxy2,
    array $rowsToExpand,
    array $columnsToExpand,
    int $expandFactor,
): int {
    $distance = 0;

    for ($row = min($galaxy1[0], $galaxy2[0]); $row < max($galaxy1[0], $galaxy2[0]); $row++) {
        $distance += in_array($row, $rowsToExpand, true) ? $expandFactor : 1;
    }

    for ($column = min($galaxy1[1], $galaxy2[1]); $column < max($galaxy1[1], $galaxy2[1]); $column++) {
        $distance += in_array($column, $columnsToExpand, true) ? $expandFactor : 1;
    }

    return $distance;
}
