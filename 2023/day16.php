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

$contraption = readContraption($fp);
$lightBeamMap = energizeContraption($contraption);

echo 'Energized tiles: ' . calculateEnergizedTiles($lightBeamMap) . PHP_EOL;
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

function energizeContraption(array $contraption): array
{
    $lightBeamMap = array_fill(
        0,
        count($contraption),
        array_fill(
            0,
            count($contraption[0]),
            [ENERGIZED => false, TOP => false, RIGHT => false, BOTTOM => false, LEFT => false],
        ),
    );

    applyBeam($contraption, $lightBeamMap, 0, 0, LEFT);

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
