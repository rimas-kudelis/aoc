<?php

$fp = fopen(__DIR__ . '/input/day9.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$nextValueSum = $previousValueSum = 0;

foreach (readSequences($fp) as $sequence) {
    $nextValueSum += extrapolateNextValue($sequence);
    $previousValueSum += extrapolatePrecedingValue($sequence);
}

echo 'Sum of next values: ' . $nextValueSum . PHP_EOL;
echo 'Sum of previous values: ' . $previousValueSum . PHP_EOL;

function readSequences($fp): iterable
{
    while (false !== $line = fgets($fp)) {
        if ("\n" === $line) {
            continue;
        }

        yield explode(' ', trim($line));
    }
}

function extrapolateNextValue(array $sequence): int
{
    if ([0] === array_keys(array_count_values($sequence))) {
        return 0;
    }

    $differences = getSequenceDifferences($sequence);

    return extrapolateNextValue($differences) + array_pop($sequence);
}

function extrapolatePrecedingValue(array $sequence): int
{
    if ([0] === array_keys(array_count_values($sequence))) {
        return 0;
    }

    $differences = getSequenceDifferences($sequence);

    return $sequence[0] - extrapolatePrecedingValue($differences);
}

function getSequenceDifferences(array $sequence): array
{
    $differences = [];

    for ($i = 1; $i < count($sequence); $i++) {
        $differences[] = $sequence[$i] - $sequence[$i - 1];
    }

    return $differences;
}
