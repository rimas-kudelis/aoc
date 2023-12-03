<?php

$fp = fopen(__DIR__ . '/input/day3.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('no file');
}

$lineNumber = 0;
$lines = [];

while (!in_array(($line = fgets($fp)), [false, ''])) {
    $lines[] = $line;
}

fclose($fp);

$symbols = [];

foreach ($lines as $lineIndex => $line) {
    $symbols[$lineIndex] = [];

    foreach (str_split($line) as $charIndex => $char) {
        if (1 === preg_match('/[^0-9\.\n]/', $char)) {
            $symbols[$lineIndex][$charIndex] = $char;
        }
    }
}

$foundNumbers = $foundNumbersWithDuplicates = [];
foreach($lines as $lineIndex => $line) {
    $numbersWithOffsets = [];
    preg_match_all('/[0-9]+/', $line, $numbersWithOffsets, PREG_OFFSET_CAPTURE);

    if (empty($numbersWithOffsets) || empty($numbersWithOffsets[0])) {
        continue;
    }

    $foundNumbers[$lineIndex] = [];
    foreach ($numbersWithOffsets[0] as $numbersWithOffset) {
        $foundNumbers[$lineIndex][$numbersWithOffset[1]] = $numbersWithOffset[0];
    }
}

$foundPartNumbers = [];
foreach ($foundNumbers as $lineIndex => $foundNumbersInLines) {
    foreach ($foundNumbersInLines as $offset => $number) {
        $numberLen = strlen($number);
        for ($symbolLineIndex = $lineIndex - 1; $symbolLineIndex <= $lineIndex + 1; $symbolLineIndex++) {
            for ($symbolCharIndex = $offset - 1; $symbolCharIndex <= $offset + $numberLen; $symbolCharIndex++) {
                if (isset($symbols[$symbolLineIndex][$symbolCharIndex])) {
                    $foundPartNumbers[] = $number;
                    break 2;
                }
            }
        }
    }
}

printf('Total sum of part numbers: %d' . PHP_EOL, array_sum($foundPartNumbers));

$gearRatios = [];
foreach ($symbols as $lineIndex => $lineSymbols) {
    foreach ($lineSymbols as $charIndex => $char) {
        if ($char !== '*') {
            continue;
        }

        $adjacentNumbers = [];
        for ($testLineIndex = $lineIndex - 1; $testLineIndex <= $lineIndex + 1; $testLineIndex++) {
            $requiredNumberLength = 3;
            for ($testCharIndex = $charIndex - 3; $testCharIndex <= $charIndex + 1; $testCharIndex++) {
                if (isset($foundNumbers[$testLineIndex][$testCharIndex])
                    && $requiredNumberLength <= strlen($foundNumbers[$testLineIndex][$testCharIndex])
                ) {
                    $adjacentNumbers[] = $foundNumbers[$testLineIndex][$testCharIndex];
                }

                --$requiredNumberLength;
            }
        }

        if (2 === count($adjacentNumbers)) {
            $gearRatios[] = array_product($adjacentNumbers);
        }
    }
}

printf('Sum of gear ratios: %d' . PHP_EOL, array_sum($gearRatios));