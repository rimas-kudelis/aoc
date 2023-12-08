<?php

const NODE_START = 'AAA';
const NODE_END = 'ZZZ';

const CHAR_STARTING_NODE = 'A';
const CHAR_ENDING_NODE = 'Z';

const LEFT = 'L';

$fp = fopen(__DIR__ . '/input/day8.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$stepCounterCache = [];

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
            $currentNode = getNextNode($nodes[$currentNode], $instruction);
            ++$steps;

            if (NODE_END === $currentNode) {
                return $steps;
            }
        }
    }
}

function countGhostSteps(array $instructions, array $nodes): int
{
    $currentNodes = array_filter(
        array_keys($nodes),
        static fn(string $node) => str_ends_with($node, CHAR_STARTING_NODE),
    );

    $endingNodesToSteps = [];
    foreach ($currentNodes as $currentNode) {
        $endingNodeAndSteps = getEndingNodeAndSteps($instructions, $nodes, $currentNode);
        $endingNodesToSteps[$endingNodeAndSteps[0]] = $endingNodeAndSteps[1];
    }

    $instructionsLength = count($instructions);

    if ([] === array_filter($endingNodesToSteps, static fn($steps) => $steps % $instructionsLength !== 0)) {
        return leastCommonMultipleInArray($endingNodesToSteps);
    }

    $currentMaxSteps = max($endingNodesToSteps);

    while (true) {
        $maxStepsUpdated = false;

        foreach ($endingNodesToSteps as $endingNode => &$steps) {
            while ($steps < $currentMaxSteps) {
                $steps += getStepsToNextEndingNode($instructions, $nodes, $endingNode, $steps);
                $maxStepsUpdated = true;
            }

            $currentMaxSteps = $steps;
        }

        if (!$maxStepsUpdated) {
            return $currentMaxSteps;
        }
    }
}

function getNextNode(array $node, string $instruction): string
{
    return $instruction === LEFT ? $node[0] : $node[1];
}

function getEndingNodeAndSteps(array $instructions, array $nodes, string $node, int $skipSteps = 0): array
{
    $steps = 0;

    while (true) {
        foreach ($instructions as $instruction) {
            if (0 < $skipSteps--) {
                continue;
            }

            $steps++;
            $node = getNextNode($nodes[$node], $instruction);

            if (str_ends_with($node, CHAR_ENDING_NODE)) {
                return [$node, $steps];
            }
        }
    }
}

function getStepsToNextEndingNode(array $instructions, array $nodes, string $node, int $skipSteps = 0): int
{
    global $stepCounterCache;

    $skipSteps = $skipSteps % count($instructions);
    if (!isset($stepCounterCache[$node])) {
        $stepCounterCache[$node] = [];
    }

    if (!isset($stepCounterCache[$node][$skipSteps])) {
        list ($sameNode, $steps) = getEndingNodeAndSteps($instructions, $nodes, $node, $skipSteps);
        $stepCounterCache[$node][$skipSteps] = $steps;
    }

    return $stepCounterCache[$node][$skipSteps];
}

function leastCommonMultipleInArray(array $numbers): int
{
    $lcm = array_shift($numbers);

    while (null !== ($number = array_shift($numbers))) {
        $lcm = leastCommonMultiple($lcm, $number);
    }

    return $lcm;
}

function leastCommonMultiple(int $a, int $b): int
{
    return $a * $b / greatestCommonDivisor($a, $b);
}

function greatestCommonDivisor(int $a, int $b): int
{
    if ($a > $b) {
        list($a, $b) = [$b, $a];
    }

    if (0 === $a) {
        return $b;
    }

    return greatestCommonDivisor($a, $b % $a);
}
