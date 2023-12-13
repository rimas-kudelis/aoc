<?php

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day13.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

echo microtime(true) . PHP_EOL;

$summary = 0;

foreach (getPatterns($fp) as $pattern) {
    $summary += getLeftReflectedColumns($pattern) ?: (getTopReflectedRows($pattern) * 100);
}

echo 'Result: ' . $summary . PHP_EOL;

echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function getPatterns($fp): iterable
{
    $pattern = [];

    while (false !== $line = fgets($fp)) {
        if ("\n" === $line) {
            yield $pattern;
            $pattern = [];

            continue;
        }

        $pattern[] = str_split(trim($line));
    }

    yield $pattern;
}

function getLeftReflectedColumns(array $pattern): int
{
    return getTopReflectedRows(transpose($pattern));
}

function getTopReflectedRows(array $pattern): int
{
    for ($i = array_key_last($pattern); $i >= 1; $i--) {
        if ($pattern[$i] === $pattern[$i - 1]) {
            $j = $i + 1;
            $k = $i - 2;

            while (isset($pattern[$j]) && isset($pattern[$k])) {
                if ($pattern[$j++] !== $pattern[$k--]) {
                    continue 2;
                }
            }

            return $i;
        }
    }

    return 0;
}

function transpose(array $matrix): array
{
    $result = [];

    foreach ($matrix[0] as $columnIndex => $value) {
        $result[] = array_column($matrix, $columnIndex);
    }

    return $result;
}
