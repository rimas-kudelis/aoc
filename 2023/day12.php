<?php

const WORKING = '.';
const DAMAGED = '#';
const UNKNOWN = '?';

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day12.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$totalPossibleArrangements = 0;

foreach (getRecords($fp) as $record) {
    $validArrangements = countValidArrangements(...$record);
    $totalPossibleArrangements += $validArrangements;
}

echo 'Total possible arrangements: ' . $totalPossibleArrangements . PHP_EOL;
echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

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
    $currentDamagedSpringCounts = getCurrentDamagedSpringCounts($map);

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

function getCurrentDamagedSpringCounts(string $map): string
{
    return implode(
        ',',
        array_map(
            static fn(string $string): int => strlen($string),
            array_filter(
                preg_split('/[^' . DAMAGED . ']+/', $map),
                static fn(string $string): bool => '' !== $string,
            ),
        ),
    );
}
