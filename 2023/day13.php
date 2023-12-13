<?php

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day13.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$initialSummary = $correctedSummary = 0;

foreach (getPatterns($fp) as $pattern) {
    $initialSummary += getLeftReflectedColumns($pattern) ?: (getTopReflectedRows($pattern) * 100);
    $correctedSummary += getLeftReflectedColumns($pattern, 1) ?: (getTopReflectedRows($pattern, 1) * 100);
}

echo 'Initial summary: ' . $initialSummary . PHP_EOL;
echo 'Corrected summary: ' . $correctedSummary . PHP_EOL;
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

        $pattern[] = trim($line);
    }

    yield $pattern;
}

function getLeftReflectedColumns(array $pattern, int $requiredSmudges = 0): int
{
    return getTopReflectedRows(transposePattern($pattern), $requiredSmudges);
}

function getTopReflectedRows(array $pattern, int $requiredSmudges = 0): int
{
    for ($i = array_key_last($pattern) - 1; $i >= 0; $i--) {
        $j = $i;
        $k = $i + 1;
        $currentTotalSmudges = 0;

        while (isset($pattern[$j]) && isset($pattern[$k])) {
            $currentSmudges = levenshtein($pattern[$j--], $pattern[$k++], 10000, 1, 10000);
            $currentTotalSmudges += $currentSmudges;

            if ($requiredSmudges < $currentTotalSmudges) {
                continue 2;
            }
        }

        if ($requiredSmudges === $currentTotalSmudges) {
            return $i + 1;
        }
    }

    return 0;
}

function transposePattern(array $pattern): array
{
    $matrix = array_map('str_split', $pattern);
    $transposedMatrix = [];

    foreach ($matrix[0] as $columnIndex => $value) {
        $transposedMatrix[] = array_column($matrix, $columnIndex);
    }

    return array_map(static fn (array $row): string => implode('', $row), $transposedMatrix);
}
