<?php

const MULTIPLIER = 17;
const DIVISOR = 256;

const REMOVE = '-';
const REPLACE = '=';

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day15.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$instructions = readInstructions($fp);
$hashes = array_map('getHash', $instructions);
$boxes = installLenses(array_fill(0, 256, []), $instructions);

echo 'Sum of instruction hashes: ' . array_sum($hashes) . PHP_EOL;
echo 'Focusing power of the setup: ' . calculateTotalFocusingPower($boxes) . PHP_EOL;
echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function readInstructions($fp): array
{
    while (false !== $line = fgets($fp)) {
        $trimmed = trim($line);
        if ('' !== $trimmed) {
            return explode(',', $trimmed);
        }
    }

    return [];
}

function getHash(string $string): int
{
    $currentHash = 0;

    foreach (str_split($string) as $character) {
        $currentHash = ($currentHash + ord($character)) * MULTIPLIER % DIVISOR;
    }

    return $currentHash;
}

function installLenses(array $boxes, array $instructions): array
{
    foreach ($instructions as $instruction) {
        if (str_contains($instruction, REMOVE)) {
            removeLens($boxes, substr($instruction, 0, -1));
        } else {
            list($lensLabel, $focalLength) = explode(REPLACE, $instruction);
            replaceLens($boxes, $lensLabel, (int)$focalLength);
        }
    }

    return $boxes;
}

function removeLens(array &$boxes, string $lensLabel): void
{
    $box = &$boxes[getHash($lensLabel)];

    foreach ($box as $lensPosition => $lensInBox) {
        if ($lensLabel === $lensInBox[0]) {
            unset($box[$lensPosition]);
            $box = array_values($box);

            return;
        }
    }
}

function replaceLens(array &$boxes, string $lensLabel, int $focalLength): void
{
    $box = &$boxes[getHash($lensLabel)];

    foreach ($box as &$lensInBox) {
        if ($lensLabel === $lensInBox[0]) {
            $lensInBox[1] = $focalLength;

            return;
        }
    }

    $box[] = [$lensLabel, $focalLength];
}

function calculateTotalFocusingPower(array $boxes): int
{
    $totalPower = 0;

    foreach ($boxes as $boxNumber => $lenses) {
        foreach ($lenses as $slotIndex => $lens) {
            $totalPower += ($boxNumber + 1) * ($slotIndex + 1) * $lens[1];
        }
    }

    return $totalPower;
}
