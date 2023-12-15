<?php

const MULTIPLIER = 17;
const DIVISOR = 256;

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day15.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$hashes = [];
$currentHash = 0;

while (false !== ($character = fgetc($fp))) {
    if ("\n" === $character) {
        continue;
    }

    if (',' === $character) {
        $hashes[] = $currentHash;
        $currentHash = 0;
        continue;
    }

    $currentHash = ($currentHash + ord($character)) * MULTIPLIER % DIVISOR;
}

$hashes[] = $currentHash;

echo 'Hashes: ' . implode(',', $hashes) . PHP_EOL;
echo 'Sum: ' . array_sum($hashes) . PHP_EOL;
