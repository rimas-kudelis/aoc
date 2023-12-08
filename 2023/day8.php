<?php

const NODE_START = 'AAA';
const NODE_END = 'ZZZ';

const LEFT = 'L';
const RIGHT = 'R';

$fp = fopen(__DIR__ . '/input/day8.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$path = readPath($fp);
$nodes = readNodes($fp);

echo 'Steps required (part 1): ' . countSteps($path, $nodes) . ' .' . PHP_EOL;

function readPath($fp): array
{
    $path = fgets($fp);

    if (in_array($path, [false, "\n"])) {
        throw new RuntimeException(sprintf('Invalid path: "%s".%s', $path, PHP_EOL));
    }

    return str_split(trim($path));
}

function readNodes($fp): array
{
    $nodes = [];

    while (false !== $line = fgets($fp)) {
        if ("\n" === $line) {
            continue;
        }

        $matches = [];
        preg_match('/^([A-Z]+) = \(([A-Z]+), ([A-Z]+)\)$/', $line, $matches);
        if (4 !== count($matches)) {
            throw new RuntimeException(sprintf('Unexpected line: "%s".%s', $line, PHP_EOL));
        }

        array_shift($matches);

        list($node, $left, $right) = $matches;
        $nodes[$node] = [$left, $right];
    }

    return $nodes;
}

function countSteps(array $path, array $nodes): int
{
    $currentNode = NODE_START;
    $steps = 0;

    while (true) {
        foreach ($path as $instruction) {
            $currentNode = $instruction === LEFT ? $nodes[$currentNode][0] : $nodes[$currentNode][1];
            ++$steps;
            if (NODE_END === $currentNode) {
                return $steps;
            }
        }
    }
}