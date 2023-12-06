<?php

$fp = fopen(__DIR__ . '/input/day6.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$times = preg_split('/\s+/', getLine($fp));
$distances = preg_split('/\s+/', getLine($fp));
array_shift($times);
array_shift($distances);

$totalSolutions = 1;

foreach ($times as $race => $time) {
    $solutions = solve($time, $distances[$race]);
    $totalSolutions *= $solutions;
}

printf('Total solutions (part 1): %d .' . PHP_EOL, $totalSolutions);

$time = (int)implode('', $times);
$distance = (int)implode('', $distances);

printf('Total solutions (part 2): %d .' . PHP_EOL, solve($time, $distance));

function solve(int $time, int $distance): int
{
    $disc = $time ** 2 - 4 * $distance;
    $x1 = ($time - sqrt($disc)) / 2;
    $x2 = ($time + sqrt($disc)) / 2;
    $solutions = smallerInt($x2) - biggerInt($x1) + 1;

    printf('Race: %f – %f; %d solutions.' . PHP_EOL, $x1, $x2, $solutions);

    return $solutions;
}

function getLine($fp): string
{
    if (false === ($line = fgets($fp))) {
        throw new RuntimeException('Cannot read line!');
    }
    $line = trim($line);

    if ('' === $line) {
        throw new RuntimeException('Line is blank!');
    }

    return trim($line);
}

function smallerInt(float $number): int
{
    $floor = floor($number);

    return ($floor !== $number) ? (int)$floor : (int)floor($number - 1);
}

function biggerInt(float $number): int
{
    $ceil = ceil($number);

    return ($ceil !== $number) ? (int)$ceil : (int)ceil($number + 1);
}