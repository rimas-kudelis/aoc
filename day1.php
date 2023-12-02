<?php

$fp = fopen(__DIR__ . '/input/day1.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('no file');
}

$calibrationValues = '';
$sumWrong = $sumRight = 0;

while (($line = fgets($fp)) !== false) {
    $digits = preg_replace('/[^0-9]/', '', $line);

    $number = $digits[0] . $digits[strlen($digits) - 1];
    $sumWrong += (int)$number;

    $matches = [];
    preg_match_all(
        '/1|2|3|4|5|6|7|8|9|on(?=e)|tw(?=o)|thre(?=e)|four|fiv(?=e)|six|seve(?=n)|eigh(?=t)|nin(?=e)/',
        $line,
        $matches,
    );

    $digits = array_map('mapDigit', $matches[0]);
    $number = reset($digits) * 10 + end($digits);

    $calibrationValues .= $number . PHP_EOL;
    $sumRight += (int)$number;
}

print('CALIBRATION_VALUES:' . PHP_EOL . $calibrationValues . PHP_EOL . 'WRONG SUM:' . PHP_EOL . $sumWrong . PHP_EOL . 'SUM:' . PHP_EOL . $sumRight . PHP_EOL);

function mapDigit(string $val): int
{
    return match ($val) {
        'on' => 1,
        'tw' => 2,
        'thre' => 3,
        'four' => 4,
        'fiv' => 5,
        'six' => 6,
        'seve' => 7,
        'eigh' => 8,
        'nin' => 9,
        default => (int)$val,
    };
}