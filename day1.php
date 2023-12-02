<?php

$fp = fopen(__DIR__ . '/input/day1.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('no file');
}

$calibrationValues = '';
$sum = 0;

while (($line = fgets($fp)) !== false) {
    $replaced = '';

    foreach(str_split($line) as $char) {
        $replaced .= $char;

        $replaced = str_replace(
            ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'],
            ['1ne', '2wo', '3hree', '4our', '5ive', '6ix', '7even', '8ight', '9ine'],
            $replaced,
        );
    }

    $digits = preg_replace('/[^0-9]/', '', $replaced);

    $number = $digits[0] . $digits[strlen($digits) - 1];
    $calibrationValues .= $number . PHP_EOL;
    $sum += (int) $number;
}

print('CALIBRATION_VALUES:' . PHP_EOL . $calibrationValues . PHP_EOL . PHP_EOL . 'SUM:' . PHP_EOL . $sum . PHP_EOL);