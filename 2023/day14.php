<?php

const FREE = '.';
const ROUND = 'O';
const CUBE = '#';

const SPIN_CYCLES = 1000000000;

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day14.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$platform = scanPlatform($fp);
$tilted = tiltNorth($platform);

echo 'Total load on North support beams after first tilt: ' . calculateLoadOnNorthBeams($tilted) . PHP_EOL . PHP_EOL;

$spinHistory = [];

// This code assumes a loop will be found eventually. If not, it will run out of memory soon enough.
for ($cycle = 0; $cycle < SPIN_CYCLES; $cycle++) {
    $positionInSpinHistory = array_search($tilted, $spinHistory, true);

    if (false !== $positionInSpinHistory) {
        $spinLoopSize = count($spinHistory) - $positionInSpinHistory;
        $remainingCycles = (SPIN_CYCLES - $cycle) % $spinLoopSize;
        $tilted = $spinHistory[$positionInSpinHistory + $remainingCycles];

        echo 'Spin loop detected at cycle: ' . $cycle . PHP_EOL;
        echo 'Pre-spin platform layout found in history at index: ' . $positionInSpinHistory . PHP_EOL;
        echo 'Total platform layouts in history: ' . count($spinHistory) . PHP_EOL;
        echo 'Loop size: ' . $spinLoopSize . PHP_EOL;
        echo 'Extra cycles to add: ' . $remainingCycles . PHP_EOL;

        break;
    }

    $spinHistory[] = $tilted;
    $tilted = runSpinCycle($tilted);
}

echo 'Total load on North support beams after ' . SPIN_CYCLES . ' spin cycles: ' . calculateLoadOnNorthBeams($tilted) . PHP_EOL;
echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function scanPlatform($fp): array
{
    $rocks = [];

    while (false !== $row = fgets($fp)) {
        $row = trim($row);

        if ("" === $row) {
            continue;
        }

        $rocks[] = str_split($row);
    }

    return $rocks;
}

function tiltNorth(array $platform): array
{
    return transposeMatrix(tiltWest(transposeMatrix($platform)));
}

function tiltSouth(array $platform): array
{
    return array_reverse(tiltNorth(array_reverse($platform)));
}

function tiltWest(array $platform): array
{
    foreach ($platform as $rowIndex => $row) {
        $lastOccupiedSpace = -1;

        foreach ($row as $rockIndex => $rock) {
            if (CUBE === $rock) {
                $lastOccupiedSpace = $rockIndex;
                continue;
            }
            if (ROUND === $rock) {
                if ($rockIndex !== ++$lastOccupiedSpace) {
                    $platform[$rowIndex][$rockIndex] = FREE;
                    $platform[$rowIndex][$lastOccupiedSpace] = ROUND;
                }
            }
        }
    }

    return $platform;
}

function tiltEast(array $platform): array
{
    return array_map('array_reverse', tiltWest(array_map('array_reverse', $platform)));
}

function runSpinCycle(array $platform): array
{
    return tiltEast(tiltSouth(tiltWest(tiltNorth($platform))));
}

function calculateLoadOnNorthBeams(array $platform): int
{
    $load = 0;
    $rowMultiplier = count($platform);

    foreach ($platform as $row) {
        $counts = array_count_values($row);
        $load += ($counts[ROUND] ?? 0) * $rowMultiplier--;
    }

    return $load;
}

function transposeMatrix(array $matrix): array
{
    # https://stackoverflow.com/questions/797251/transposing-multidimensional-arrays-in-php
    return array_map(null, ...$matrix);
}
