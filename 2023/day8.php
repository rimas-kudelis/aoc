<?php

const NODE_START = 'AAA';
const NODE_END = 'ZZZ';

const GHOST_NODE_START = 'A';
const GHOST_NODE_END = 'Z';

const LEFT = 'L';

$fp = fopen(__DIR__ . '/input/day8.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$instructions = readInstructions($fp);
$nodes = readNodes($fp);

echo 'Steps required (part 1): ' . countSteps($instructions, $nodes) . ' .' . PHP_EOL;
echo 'Steps required (part 2): ' . countGhostSteps($instructions, $nodes) . ' .' . PHP_EOL;

function readInstructions($fp): array
{
    $instructions = fgets($fp);

    if (in_array($instructions, [false, "\n"])) {
        throw new RuntimeException(sprintf('Invalid instructions: "%s".%s', $instructions, PHP_EOL));
    }

    return str_split(trim($instructions));
}

function readNodes($fp): array
{
    $nodes = [];

    while (false !== $line = fgets($fp)) {
        if ("\n" === $line) {
            continue;
        }

        $matches = [];
        preg_match('/^([A-Z0-9]+) = \(([A-Z0-9]+), ([A-Z0-9]+)\)$/', $line, $matches);
        if (4 !== count($matches)) {
            throw new RuntimeException(sprintf('Unexpected line: "%s".%s', $line, PHP_EOL));
        }

        array_shift($matches);

        list($node, $left, $right) = $matches;
        $nodes[$node] = [$left, $right];
    }

    return $nodes;
}

function countSteps(array $instructions, array $nodes): int
{
    $steps = 0;
    $currentNode = NODE_START;

    while (true) {
        foreach ($instructions as $instruction) {
            $currentNode = $instruction === LEFT ? $nodes[$currentNode][0] : $nodes[$currentNode][1];
            ++$steps;

            if (NODE_END === $currentNode) {
                return $steps;
            }
        }
    }
}

function countGhostSteps(array $instructions, array $nodes): int
{
    $steps = 0;
    $currentNodes = array_filter(
        array_keys($nodes),
        static fn(string $node) => str_ends_with($node, GHOST_NODE_START),
    );

    while (true) {
        foreach ($instructions as $instruction) {
            $allNodesFinal = true;

            ++$steps;

            foreach ($currentNodes as &$currentNode) {
                $currentNode = $instruction === LEFT ? $nodes[$currentNode][0] : $nodes[$currentNode][1];

                $allNodesFinal = $allNodesFinal && str_ends_with($currentNode, GHOST_NODE_END);
            }

            if ($allNodesFinal) {
                return $steps;
            }

            if (0 === $steps % 10000000) {
                echo 'P2: ' . $steps . ' steps.' . PHP_EOL;
            }
        }
    }
}
