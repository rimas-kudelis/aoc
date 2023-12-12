<?php

const WORKING = '.';
const DAMAGED = '#';
const UNKNOWN = '?';

$fp = fopen(__DIR__ . '/input/day12.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$totalPossibleArrangments = 0;

foreach (getRecords($fp) as $record) {
    $validArrangements = countValidArrangements(...$record);
    $totalPossibleArrangments += $validArrangements;
}

echo 'Total possible arrangements: ' . $totalPossibleArrangments . PHP_EOL;

function getRecords($fp): iterable
{
    while (false !== $line = fgets($fp)) {
        if ("\n" === $line) {
            continue;
        }
        $line = trim($line);

        if (!preg_match('/^[#.?]+\s([0-9]+,?)+$/', $line)) {
            throw new RuntimeException('Unexpected line: ' . $line);
        }

        yield explode(' ', trim($line));
    }
}

function countValidArrangements(string $map, string $damagedSpringCounts): int
{
    $currentDamagedSpringCounts = getDamagedCounts($map);

    if ($damagedSpringCounts === $currentDamagedSpringCounts) {
        return 1;
    }

    $firstUnknownPosition = strpos($map, UNKNOWN);
    if (false === $firstUnknownPosition) {
        return 0;
    }

    return countValidArrangements(substr_replace($map, WORKING, $firstUnknownPosition, 1), $damagedSpringCounts)
        + countValidArrangements(substr_replace($map, DAMAGED, $firstUnknownPosition, 1), $damagedSpringCounts);
}

function getDamagedCounts(string $map): string
{
    $damagedCounts = [];
    $currentDamagedCount = 0;

    foreach (str_split($map) as $spring) {
        if (DAMAGED === $spring) {
            $currentDamagedCount++;

            continue;
        }

        $damagedCounts[] = $currentDamagedCount;
        $currentDamagedCount = 0;
    }

    $damagedCounts[] = $currentDamagedCount;

    return implode(',', array_filter($damagedCounts, static fn(int $count): bool => 0 !== $count));
}
