<?php

const FREE = '.';
const ROUND = 'O';
const CUBE = '#';

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day14.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$platform = scanPlatform($fp);
$tilted = tiltNorth($platform);

echo 'Total load on the North support beams: ' . calculateLoad($tilted) . PHP_EOL;
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
    $tilted = [];

    foreach ($platform as $rowIndex => $row) {
        $tilted[$rowIndex] = [];

        foreach ($row as $rockIndex => $rock) {
            if (FREE === $rock || CUBE === $rock) {
                $tilted[$rowIndex][$rockIndex] = $rock;

                continue;
            }

            $tilted[$rowIndex][$rockIndex] = FREE;

            for ($nextRowIndex = $rowIndex - 1; $nextRowIndex >= -1; $nextRowIndex--) {
                if (-1 === $nextRowIndex || FREE !== $tilted[$nextRowIndex][$rockIndex]) {
                    $tilted[$nextRowIndex + 1][$rockIndex] = $rock;

                    break;
                }
            }
        }
    }

    return $tilted;
}

function calculateLoad(array $platform): int
{

    foreach ($platform as $row) {
        echo implode('', $row) . PHP_EOL;
    }

    $load = 0;
    $rowMultiplier = count($platform);

    foreach ($platform as $row) {
        $counts = array_count_values($row);
        $load += ($counts[ROUND] ?? 0) * $rowMultiplier--;
    }

    return $load;
}
