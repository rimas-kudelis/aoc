<?php

const LEFT = 'L';
const RIGHT = 'R';
const TOP = 'T';
const BOTTOM = 'B';

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day17.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$initialLightBeamMapCache = [];
$map = readMap($fp);
$calculator = new HeatLossCalculator();

echo 'Minimum heat loss calculated: ' . $calculator->calculateMinimumHeatLoss($map) . PHP_EOL;
echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function readMap($fp): array
{
    $map = [];

    while (false !== $line = fgets($fp)) {
        $trimmed = trim($line);
        if ('' !== $trimmed) {
            $map[] = array_map('intval', str_split($trimmed));
        }
    }

    return $map;
}

class HeatLossCalculator
{
    private const MAX_STRAIGHT_PATH_LENGTH = 3;

    private const ENTER_COST = 'e';
    private const VISITED = 'v';
    private const TOTAL_COST_BEST = 't1';
    private const TOTAL_COST_NEXT = 't2';
    private const SOURCE_BEST = 's1';
    private const SOURCE_NEXT = 's2';
    private const PATH_LENGTH_BEST = 'p1';
    private const PATH_LENGTH_NEXT = 'p2';

    private const LEFT = -1;
    private const RIGHT = 1;
    private const TOP = -2;
    private const BOTTOM = 2;

    public function calculateMinimumHeatLoss(
        array $map,
        int $startRow = 0,
        int $startColumn = 0,
        ?int $endRow = null,
        ?int $endColumn = null,
    ): int
    {
        if (null === $endRow) {
            $endRow = array_key_last($map);
        }

        if (null === $endColumn) {
            $endColumn = array_key_last($map[0]);
        }

        $workingMap = $this->buildWorkingMap($map);
        $workingMap[$startRow][$startColumn][self::TOTAL_COST_BEST] = $workingMap[$startRow][$startColumn][self::TOTAL_COST_NEXT] = 0;

        do {
            list($currentRow, $currentColumn) = $this->findCheapestUnvisitedBlock($workingMap);

            if (null === $currentRow || null === $currentColumn) {
                throw new RuntimeException('Cannot visit any more blocks!');
            }

            $currentBlock = $workingMap[$currentRow][$currentColumn];

            $possibleMoves = [
                [$currentRow - 1, $currentColumn, self::BOTTOM],
                [$currentRow, $currentColumn + 1, self::LEFT],
                [$currentRow + 1, $currentColumn, self::TOP],
                [$currentRow, $currentColumn - 1, self::RIGHT],
            ];

            foreach ($possibleMoves as list ($nextRow, $nextColumn, $willComeFrom)) {
                // Out of map coordinates
                if (!isset($workingMap[$nextRow][$nextColumn])) {
                    continue;
                }

                // Already visited this block, or coordinates are out of map
//                if ($workingMap[$nextRow][$nextColumn][self::VISITED]) {
//                    continue;
//                }
//                echo 'Checking ' . ($workingMap[$nextRow][$nextColumn][self::VISITED] ? '' : 'un') . 'visited block...' . PHP_EOL;

                $lengthsAndCosts = [];
                if ($willComeFrom !== -$currentBlock[self::SOURCE_BEST]) {
                    $lengthsAndCosts[] = [
                        // Best
                        $willComeFrom === $currentBlock[self::SOURCE_BEST] ? $currentBlock[self::PATH_LENGTH_BEST] + 1 : 1,
                        $currentBlock[self::TOTAL_COST_BEST],
                    ];
                }

                if ($willComeFrom !== -$currentBlock[self::SOURCE_NEXT]) {
                    $lengthsAndCosts[] = [
                        // Next best
                        $willComeFrom === $currentBlock[self::SOURCE_NEXT] ? $currentBlock[self::PATH_LENGTH_NEXT] + 1 : 1,
                        $currentBlock[self::TOTAL_COST_NEXT],
                    ];
                }

                $lengthsAndCosts = array_unique($lengthsAndCosts);

//                if ($lengthsAndCosts[0] === $lengthsAndCosts[1]) {
//                    unset($lengthsAndCosts[1]);
//                }
//print_r($lengthsAndCosts);
                $nextBlock = &$workingMap[$nextRow][$nextColumn];
                foreach ($lengthsAndCosts as list($nextStraightPathLength, $cost)) {
                    // Can't go more than three blocks straight
                    if ($nextStraightPathLength > self::MAX_STRAIGHT_PATH_LENGTH) {
                        continue;
                    }

                    $nextCostFromHere = $cost + $nextBlock[self::ENTER_COST];

                    // Both stored costs already smaller or equal to the one being processed
                    if ($nextBlock[self::TOTAL_COST_NEXT] <= $nextCostFromHere) {
                        continue;
                    }

                    // Already have this path stored
                    if ($nextBlock[self::TOTAL_COST_BEST] === $nextCostFromHere
                        && $nextBlock[self::SOURCE_BEST] === $willComeFrom
                        && $nextBlock[self::PATH_LENGTH_BEST] === $nextStraightPathLength
                    ) {
                        continue;
                    }

                    $nextBlock[self::TOTAL_COST_NEXT] = $nextCostFromHere;
                    $nextBlock[self::SOURCE_NEXT] = $willComeFrom;
                    $nextBlock[self::PATH_LENGTH_NEXT] = $nextStraightPathLength;

                    if ($nextBlock[self::TOTAL_COST_BEST] > $nextBlock[self::TOTAL_COST_NEXT]) {
//                        echo 'Swapping ' . $nextBlock[self::TOTAL_COST_BEST] . ' and ' . $nextBlock[self::TOTAL_COST_NEXT] . '...' . PHP_EOL;
                        list(
                            $nextBlock[self::TOTAL_COST_BEST],
                            $nextBlock[self::SOURCE_BEST],
                            $nextBlock[self::PATH_LENGTH_BEST],
                            $nextBlock[self::TOTAL_COST_NEXT],
                            $nextBlock[self::SOURCE_NEXT],
                            $nextBlock[self::PATH_LENGTH_NEXT],
                        ) = [
                            $nextBlock[self::TOTAL_COST_NEXT],
                            $nextBlock[self::SOURCE_NEXT],
                            $nextBlock[self::PATH_LENGTH_NEXT],
                            $nextBlock[self::TOTAL_COST_BEST],
                            $nextBlock[self::SOURCE_BEST],
                            $nextBlock[self::PATH_LENGTH_BEST],
                        ];
                    }

                    $nextBlock[self::VISITED] = false;
                }

//                // Can't go more than three blocks straight
//                if ($bestStraightPathLength > self::MAX_STRAIGHT_PATH_LENGTH) {
//                    continue;
//                }
//
//                $nextCostFromHere = $currentBlock[self::TOTAL_COST_BEST] + $nextBlock[self::ENTER_COST];
//
//                if ($nextBlock[self::TOTAL_COST_BEST] < $nextCostFromHere) {
//                    continue;
//                }
//
//                if ($nextBlock[self::TOTAL_COST_BEST] === $nextCostFromHere) {
//                    $nextBlock[self::SOURCES_BEST][$willComeFrom] = $bestStraightPathLength;
//
//                    continue;
//                }
//
//                $nextBlock[self::TOTAL_COST_BEST] = $nextCostFromHere;
//                $nextBlock[self::VISITED] = false;
//                $nextBlock[self::SOURCES_BEST] = [$willComeFrom => $bestStraightPathLength];
//
//                if ($maxHeatLoss <= $map[$nextRow][$nextColumn]) {
//                    continue;
//                }
//
//                if ($endRow === $nextRow && $endColumn === $nextColumn) {
//                    return min($maxHeatLoss, $map[$endRow][$endColumn]);
//                }
//
//                if ($maxHeatLoss <= abs($nextRow - $endRow) + abs($nextColumn - $endColumn) - 2) {
//                    continue;
//                }
//
//                $maxHeatLoss = $map[$nextRow][$nextColumn] + $this->doCalculateMinimumHeatLoss (
//                        $map,
//                        $nextRow,
//                        $nextColumn,
//                        $endRow,
//                        $endColumn,
//                        $willComeFrom,
//                        $nextStraightPathLength,
//                        $visitedBlocks,
//                        $maxHeatLoss - $map[$nextRow][$nextColumn],
//                    );
            }

            $workingMap[$currentRow][$currentColumn][self::VISITED] = true;

        } while (!$workingMap[$endRow][$endColumn][self::VISITED]);

//        $baseHeatLoss = $this->calculateBaseHeatLoss($map, $startRow, $startColumn, $endRow, $endColumn);
//
//        echo 'Base heat loss: ' . $baseHeatLoss . PHP_EOL;

//        $visitedBlocks = $this->generateInitialVisitedBlocks($map);
//
//        return $this->doCalculateMinimumHeatLoss(
//            $map,
//            $startRow,
//            $startColumn,
//            $endRow,
//            $endColumn,
//            null,
//            0,
//            $visitedBlocks,
//            $baseHeatLoss,
//        );

        $this->printMap($workingMap);

        return $workingMap[$endRow][$endColumn][self::TOTAL_COST_BEST];
    }

    private function buildWorkingMap(array $map): array
    {
        $workingMap = [];

        foreach ($map as $rowIndex => $row) {
            $workingMap[$rowIndex] = [];

            foreach ($row as $colIndex => $enterCost) {
                $workingMap[$rowIndex][$colIndex] = [
                    self::ENTER_COST => $enterCost,
                    self::VISITED => false,
                    self::TOTAL_COST_BEST => PHP_INT_MAX,
                    self::SOURCE_BEST => null,
                    self::PATH_LENGTH_BEST => null,
                    self::TOTAL_COST_NEXT => PHP_INT_MAX,
                    self::SOURCE_NEXT => null,
                    self::PATH_LENGTH_NEXT => null,
                ];
            }
        }

        return $workingMap;
    }

    /** Returns an array with row index and column index, or two nulls if no unvisited blocks remain */
    private function findCheapestUnvisitedBlock(array $map): array
    {
        $result = [null, null];
        $price = PHP_INT_MAX;

        foreach ($map as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if ($col[self::TOTAL_COST_BEST] < $price && !$col[self::VISITED]) {
                    $result = [$rowIndex, $colIndex];
                    $price = $col[self::TOTAL_COST_BEST];
                }
            }
        }

        return $result;
    }

    private function calculateBaseHeatLoss(array $map, int $startRow, int $startColumn, int $endRow, int $endColumn): int
    {
        $horizontalDistance = abs($startColumn - $endColumn);
        $verticalDistance = abs($startRow - $startColumn);

        if (0 === $horizontalDistance && 0 === $verticalDistance) {
            return 0;
        }

        $moveHorizontally = max(1, min(self::MAX_STRAIGHT_PATH_LENGTH, $horizontalDistance));
        $moveVertically = max(1, min(self::MAX_STRAIGHT_PATH_LENGTH, $verticalDistance));

        $row = $startRow;
        $column = $startColumn;
        $heatLoss = 0;

        for ($step = 0; $step < $moveHorizontally; ++$step) {
            $startColumn < $endColumn ? $column++ : $column--;
            $heatLoss += $map[$row][$column];
        }

        if ($row === $endRow && $column === $endColumn) {
            return $heatLoss;
        }

        for ($step = 0; $step < $moveVertically; ++$step) {
            $startRow < $endRow ? $row++ : $row--;
            $heatLoss += $map[$row][$column];
        }

        return $heatLoss + $this->calculateBaseHeatLoss($map, $row, $column, $endRow, $endColumn);
    }

    private function doCalculateMinimumHeatLoss(
        array $map,
        int $startRow,
        int $startCol,
        int $endRow,
        int $endColumn,
        ?string $cameFrom,
        int $straightPathLength,
        array $visitedBlocks,
        int $maxHeatLoss,
    ): int
    {
        $possibleMoves = [
            [$startRow - 1, $startCol, BOTTOM],
            [$startRow, $startCol + 1, LEFT],
            [$startRow + 1, $startCol, TOP],
            [$startRow, $startCol - 1, RIGHT],
        ];

        $visitedBlocks[$startRow][$startCol] = true;

        foreach ($possibleMoves as list ($row, $column, $wouldComeFrom)) {
            if (true === ($visitedBlocks[$row][$column] ?? true)) {
                continue;
            }

            $nextStraightPathLength = $wouldComeFrom === $cameFrom ? $straightPathLength + 1 : 1;

            if ($nextStraightPathLength > self::MAX_STRAIGHT_PATH_LENGTH) {
                continue;
            }

            if ($maxHeatLoss <= $map[$row][$column]) {
                continue;
            }

            if ($endRow === $row && $endColumn === $column) {
                return min($maxHeatLoss, $map[$endRow][$endColumn]);
            }

            if ($maxHeatLoss <= abs($row - $endRow) + abs($column - $endColumn) - 2) {
                continue;
            }

            $maxHeatLoss = $map[$row][$column] + $this->doCalculateMinimumHeatLoss(
                    $map,
                    $row,
                    $column,
                    $endRow,
                    $endColumn,
                    $wouldComeFrom,
                    $nextStraightPathLength,
                    $visitedBlocks,
                    $maxHeatLoss - $map[$row][$column],
                );
        }

        return $maxHeatLoss;
    }

    private function generateInitialVisitedBlocks(array $map): array
    {
        return array_fill(0, count($map), array_fill(0, count($map[0]), false));
    }

    private function printMap(array $map): void
    {
        foreach ($map as $row) {
            echo implode(
                '',
                array_map(
                    static fn (array $blocks): string => str_pad((string) $blocks[self::TOTAL_COST_BEST], 3, pad_type: STR_PAD_LEFT) . '|' . str_pad((string) $blocks[self::TOTAL_COST_NEXT], 4),
                    $row,
                ),
            ) . PHP_EOL;
        }
    }
}
