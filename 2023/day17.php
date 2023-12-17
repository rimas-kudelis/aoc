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
    $contraption = [];

    while (false !== $line = fgets($fp)) {
        $trimmed = trim($line);
        if ('' !== $trimmed) {
            $contraption[] = str_split($trimmed);
        }
    }

    return $contraption;
}

class HeatLossCalculator
{
    private const MAX_STRAIGHT_PATH_LENGTH = 3;

    public function calculateMinimumHeatLoss(
        array $map,
        int $startRow = 0,
        int $startColumn = 0,
        ?int $endRow = null,
        ?int $endColumn = null,
    ): int {
        if (null === $endRow) {
            $endRow = array_key_last($map);
        }

        if (null === $endColumn) {
            $endColumn = array_key_last($map[0]);
        }

        $baseHeatLoss = $this->calculateBaseHeatLoss($map, $startRow, $startColumn, $endRow, $endColumn);

        echo 'Base heat loss: ' . $baseHeatLoss . PHP_EOL;

        $visitedBlocks = $this->generateInitialVisitedBlocks($map);

        return $this->doCalculateMinimumHeatLoss(
            $map,
            $startRow,
            $startColumn,
            $endRow,
            $endColumn,
            null,
            0,
            $visitedBlocks,
            $baseHeatLoss,
        );
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
    ): int {
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

            $maxHeatLoss = $map[$row][$column] + $this->doCalculateMinimumHeatLoss (
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
}
