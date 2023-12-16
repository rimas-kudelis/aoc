<?php

const ENERGIZED = 'e';
const TOP = 't';
const RIGHT = 'r';
const BOTTOM = 'b';
const LEFT = 'l';

const BLANK = '.';
const SPLIT_V = '|';
const SPLIT_H = '-';
const MIRROR_45 = '/';
const MIRROR_135 = '\\';

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day16.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$initialLightBeamMapCache = [];
$contraption = readContraption($fp);

echo 'Energized tiles: ' . calculateEnergizedTiles(energizeContraption($contraption)) . PHP_EOL;
echo 'Best energized tiles: ' . calculateEnergizedTilesInBestScenario($contraption) . PHP_EOL;
echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function readContraption($fp): array
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

function energizeContraption(
    array $contraption,
    int $startRow = 0,
    int $startColumn = 0,
    string $startDirection = LEFT,
): array {
    $lightBeamMap = getInitialLightBeamMap(count($contraption), count($contraption[0]));

    applyBeam($contraption, $lightBeamMap, $startRow, $startColumn, $startDirection);

    return $lightBeamMap;
}

function applyBeam(array $contraption, array &$lightBeamMap, int $row, int $column, string $direction): void
{
    if (!isset($lightBeamMap[$row][$column])) {
        // Out of bounds
        return;
    }

    if ($lightBeamMap[$row][$column][$direction]) {
        // Been there, done that
        return;
    }

    $lightBeamMap[$row][$column][$direction] = true;
    $lightBeamMap[$row][$column][ENERGIZED] = true;

    switch ($direction) {
        case LEFT:
            switch ($contraption[$row][$column]) {
                case BLANK:
                case SPLIT_H:
                    applyBeam($contraption, $lightBeamMap, $row, $column + 1, LEFT);
                    break 2;
                case SPLIT_V:
                    applyBeam($contraption, $lightBeamMap, $row - 1, $column, BOTTOM);
                    applyBeam($contraption, $lightBeamMap, $row + 1, $column, TOP);
                    break 2;
                case MIRROR_45:
                    applyBeam($contraption, $lightBeamMap, $row - 1, $column, BOTTOM);
                    break 2;
                case MIRROR_135:
                    applyBeam($contraption, $lightBeamMap, $row + 1, $column, TOP);
                    break 2;
                default:
                    throw new RuntimeException('Unexpected tile in contraption!');
            }
        case RIGHT:
            switch ($contraption[$row][$column]) {
                case BLANK:
                case SPLIT_H:
                    applyBeam($contraption, $lightBeamMap, $row, $column - 1, RIGHT);
                    break 2;
                case SPLIT_V:
                    applyBeam($contraption, $lightBeamMap, $row - 1, $column, BOTTOM);
                    applyBeam($contraption, $lightBeamMap, $row + 1, $column, TOP);
                    break 2;
                case MIRROR_45:
                    applyBeam($contraption, $lightBeamMap, $row + 1, $column, TOP);
                    break 2;
                case MIRROR_135:
                    applyBeam($contraption, $lightBeamMap, $row - 1, $column, BOTTOM);
                    break 2;
                default:
                    throw new RuntimeException('Unexpected tile in contraption!');
            }
        case TOP:
            switch ($contraption[$row][$column]) {
                case BLANK:
                case SPLIT_V:
                    applyBeam($contraption, $lightBeamMap, $row + 1, $column, TOP);
                    break 2;
                case SPLIT_H:
                    applyBeam($contraption, $lightBeamMap, $row, $column - 1, RIGHT);
                    applyBeam($contraption, $lightBeamMap, $row, $column + 1, LEFT);
                    break 2;
                case MIRROR_45:
                    applyBeam($contraption, $lightBeamMap, $row, $column - 1, RIGHT);
                    break 2;
                case MIRROR_135:
                    applyBeam($contraption, $lightBeamMap, $row, $column + 1, LEFT);
                    break 2;
                default:
                    throw new RuntimeException('Unexpected tile in contraption!');
            }
        case BOTTOM:
            switch ($contraption[$row][$column]) {
                case BLANK:
                case SPLIT_V:
                    applyBeam($contraption, $lightBeamMap, $row - 1, $column, BOTTOM);
                    break 2;
                case SPLIT_H:
                    applyBeam($contraption, $lightBeamMap, $row, $column - 1, RIGHT);
                    applyBeam($contraption, $lightBeamMap, $row, $column + 1, LEFT);
                    break 2;
                case MIRROR_45:
                    applyBeam($contraption, $lightBeamMap, $row, $column + 1, LEFT);
                    break 2;
                case MIRROR_135:
                    applyBeam($contraption, $lightBeamMap, $row, $column - 1, RIGHT);
                    break 2;
                default:
                    throw new RuntimeException('Unexpected tile in contraption!');
            }
        default:
            throw new RuntimeException('Unexpected light beam direction!');
    }
}

function calculateEnergizedTiles(array $lightBeamMap): int
{
    return array_sum(
        array_map(
            static fn(array $row): int => count(array_filter($row, static fn(array $tile): bool => $tile[ENERGIZED])),
            $lightBeamMap,
        ),
    );
}

function getInitialLightBeamMap(int $rows, int $columns): array
{
    global $initialLightBeamMapCache;

    if (!isset($initialLightBeamMapCache[$rows][$columns])) {
        $initialLightBeamMapCache[$rows][$columns] = array_fill(
            0,
            $rows,
            array_fill(
                0,
                $columns,
                [ENERGIZED => false, TOP => false, RIGHT => false, BOTTOM => false, LEFT => false],
            ),
        );
    }

    return $initialLightBeamMapCache[$rows][$columns];
}

function calculateEnergizedTilesInBestScenario(array $contraption): int
{
    $bestEnergizedTiles = 0;
    $lastRow = count($contraption) - 1;
    $lastColumn = count($contraption[0]) - 1;

    for ($column = 0; $column <= $lastColumn; ++$column) {
        $bestEnergizedTiles = max(
            $bestEnergizedTiles,
            calculateEnergizedTiles(energizeContraption($contraption, 0, $column, TOP)),
        );
        $bestEnergizedTiles = max(
            $bestEnergizedTiles,
            calculateEnergizedTiles(energizeContraption($contraption, $lastRow, $column, BOTTOM)),
        );
    }

    for ($row = 0; $row <= $lastRow; $row++) {
        $bestEnergizedTiles = max(
            $bestEnergizedTiles,
            calculateEnergizedTiles(energizeContraption($contraption, $row, 0, LEFT)),
        );
        $bestEnergizedTiles = max(
            $bestEnergizedTiles,
            calculateEnergizedTiles(energizeContraption($contraption, $row, $lastColumn, RIGHT)),
        );
    }

    return $bestEnergizedTiles;
}
