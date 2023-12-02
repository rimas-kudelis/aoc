<?php

const MAX_RED = 12;
const MAX_GREEN = 13;
const MAX_BLUE = 14;

$fp = fopen(__DIR__ . '/input/day2.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('no input file');
}

$validIds = $invalidIds = $powers = [];

while (($line = fgets($fp)) !== false) {
    if ('' === $line) {
        continue;
    }

    $matches = [];
    preg_match('/Game ([0-9]+): (.*)/', $line, $matches);

    if (3 !== count($matches)) {
        throw new RuntimeException('Unexpected $matches');
    }

    list($line, $id, $subsets) = $matches;

    if (areSubsetsValid($subsets)) {
        $validIds[] = (int) $id;
    } else {
        $invalidIds[] = (int) $id;
    }

    $powers[] = getPower($subsets);
}

var_dump('VALID IDS:', $validIds, 'INVALID IDS:', $invalidIds, 'VALID SUM:', array_sum($validIds), 'POWER_SUM:', array_sum($powers));

function areSubsetsValid(string $strSubsets): bool
{
    $subsets = explode(';', $strSubsets);
    foreach ($subsets as $subset) {
        if (
            !isSubsetValid('red', $subset, MAX_RED)
            || !isSubsetValid('green', $subset, MAX_GREEN)
            || !isSubsetValid('blue', $subset, MAX_BLUE)
        ) {
            return false;
        }
    }

    return true;
}
function isSubsetValid(string $color, string $subset, int $max): bool
{
    $matches = [];
    preg_match("/([0-9]+) $color/i", $subset, $matches);

    if (2 === count($matches) && ((int) $matches[1] > $max)) {
        return false;
    }

    return true;
}

function getPower(string $subsets): int
{
    $red = getMinColor($subsets, 'red');
    $green = getMinColor($subsets, 'green');
    $blue = getMinColor($subsets, 'blue');

    return $red * $green * $blue;
}

function getMinColor(string $subsets, string $color): int
{
    $matches = [];
    preg_match_all("/([0-9]+) $color/i", $subsets, $matches);
    if (2 !== count($matches)) {
        return 0;
    }

    return max($matches[1]);
}

