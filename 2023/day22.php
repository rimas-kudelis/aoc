<?php

const STEPS_PART_1 = 64;
//const STEPS_PART_2 = 26501365;
//const STEPS_PART_2 = 10;

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day21.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$initialLightBeamMapCache = [];
$map = readBrickSnapshot($fp);
$calculator = new StepCalculator();

echo 'Total visitable plots (part 1): ' . $calculator->countPossibilities($map, STEPS_PART_1) . PHP_EOL;
//echo 'Total visitable plots (part 2): ' . $calculator->countPossibilities($map, STEPS_PART_2) . PHP_EOL;

echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function readBrickSnapshot($fp): array
{
    $map = [];

    while (false !== $line = fgets($fp)) {
        $trimmed = trim($line);
        if ('' !== $trimmed) {
            $map[] = str_split($trimmed);
        }
    }

    return $map;
}

class StepCalculator
{
    private const CURRENT_COST = 'C';
    private const VISITED = 'V';
    private const TERRAIN = 'S';

    private const ROCK = '#';
    private const PLOT = '.';
    private const START = 'S';

    public function countPossibilities(array $map, int $stepsToTake): int
    {
        $workingMap = $this->buildWorkingMap($map);

        echo 'CP START ' . microtime(true) . PHP_EOL;

        while (true) {
            list($currentRow, $currentColumn) = $this->findClosestUnvisitedPlot($workingMap, $stepsToTake);

            if (null === $currentRow || null === $currentColumn) {
                break;
            }

            $currentBlock = $workingMap[$currentRow][$currentColumn];

            $possibleMoves = [
                [$currentRow - 1, $currentColumn],
                [$currentRow, $currentColumn + 1],
                [$currentRow + 1, $currentColumn],
                [$currentRow, $currentColumn - 1],
            ];

            foreach ($possibleMoves as list ($nextRow, $nextColumn)) {
                // Coordinates out of map or block already visited
                if (!isset($workingMap[$nextRow][$nextColumn]) || $workingMap[$nextRow][$nextColumn][self::VISITED]) {
                    continue;
                }

                $workingMap[$nextRow][$nextColumn][self::CURRENT_COST] = min(
                    $workingMap[$nextRow][$nextColumn][self::CURRENT_COST],
                    $currentBlock[self::CURRENT_COST] + 1,
                );
            }

            $workingMap[$currentRow][$currentColumn][self::VISITED] = true;
        }
        echo 'CP END ' . microtime(true) . PHP_EOL;

//        $this->printMap($workingMap);

        $stepCounts = [];
        foreach ($workingMap as $row) {
            foreach (array_count_values(array_map(static fn(array $plot): int => $plot[self::CURRENT_COST], $row)) as $steps => $count) {
                $stepCounts[$steps] = ($stepCounts[$steps] ?? 0) + $count;
            }
        }

        $possibilities = 0;

        foreach ($stepCounts as $currentSteps => $plots) {
            if ($currentSteps <= $stepsToTake && $currentSteps % 2 === $stepsToTake % 2) {
                $possibilities += $plots;
            }
        }

        return $possibilities;
    }

    private function buildWorkingMap(array $map): array
    {
        $workingMap = [];

        foreach ($map as $rowIndex => $row) {
            $workingMap[$rowIndex] = [];

            foreach ($row as $colIndex => $terrain) {
                $workingMap[$rowIndex][$colIndex] = [
                    self::CURRENT_COST => PHP_INT_MAX,
                    self::VISITED => self::ROCK === $terrain,
                    self::TERRAIN => $terrain,
                ];

                if (self::START === $terrain) {
                    $workingMap[$rowIndex][$colIndex][self::CURRENT_COST] = 0;
                    $workingMap[$rowIndex][$colIndex][self::TERRAIN] = self::PLOT;
                }
            }
        }

        return $workingMap;
    }

    /** Returns an array with row index and column index, or two nulls if no unvisited plots remain */
    private function findClosestUnvisitedPlot(array $map, int $maxSteps = PHP_INT_MAX): array
    {
        $result = [null, null];
        $steps = $maxSteps;

        foreach ($map as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if (!$col[self::VISITED] && $col[self::CURRENT_COST] < $steps) {
                    $result = [$rowIndex, $colIndex];
                    $steps = $col[self::CURRENT_COST];
                }
            }
        }

        return $result;
    }

    private function printMap(array $map): void
    {
        foreach ($map as $row) {
            echo implode(
                    '',
                    array_map(
                        static fn(array $blocks): string => str_pad(
                            $blocks[self::CURRENT_COST] > 999 ? '.' : (string)$blocks[self::CURRENT_COST],
                            4,
                        ),
                        $row,
                    ),
                ) . PHP_EOL;
        }
    }
}


class Brick
{
    public function __construct(
        private int $x1,
        private int $y1,
        private int $z1,
        private int $x2,
        private int $y2,
        private int $z2,
    ) {
    }

    public function fall(int $newZ1): void
    {
        $this->z2 = $this->z2 - $this->z1 + $newZ1;
        $this->z1 = $newZ1;
    }
}