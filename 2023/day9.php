<?php

enum ExtrapolateMode {
    case ExtrapolateNext;
    case ExtrapolatePrevious;
}

$fp = fopen(__DIR__ . '/input/day9.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$nextValueSum = $previousValueSum = 0;

foreach (readSequences($fp) as $sequence) {
    $nextValueSum += extrapolateValue($sequence, ExtrapolateMode::ExtrapolateNext);
    $previousValueSum += extrapolateValue($sequence, ExtrapolateMode::ExtrapolatePrevious);
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

function extrapolateValue(array $sequence, ExtrapolateMode $mode): int
{
    if ([0] === array_keys(array_count_values($sequence))) {
        return 0;
    }

    $differences = getSequenceDifferences($sequence);

    return match($mode) {
        ExtrapolateMode::ExtrapolateNext => extrapolateValue($differences, $mode) + array_pop($sequence),
        ExtrapolateMode::ExtrapolatePrevious => $sequence[0] - extrapolateValue($differences, $mode),
    };
}

function getSequenceDifferences(array $sequence): array
{
    $differences = [];

    for ($i = 1; $i < count($sequence); $i++) {
        $differences[] = $sequence[$i] - $sequence[$i - 1];
    }

    return $differences;
}
