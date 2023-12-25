<?php

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day23.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$initialLightBeamMapCache = [];
$map = readMap($fp);
$calculator = new HikeCalculator();

echo 'Longest trail (part 1): ' . ($calculator->findLongestTrail($map, false) ?? 'not found!') . PHP_EOL;
echo 'Longest trail (part 2): ' . ($calculator->findLongestTrail($map, true) ?? 'not found!') . PHP_EOL;

echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function readMap($fp): array
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

class HikeCalculator
{
    private const PATH = '.';
    private const FOREST = '#';
    private const SLOPE_N = '^';
    private const SLOPE_S = 'v';
    private const SLOPE_E = '>';
    private const SLOPE_W = '<';

    public function findLongestTrail(array $map, bool $allowClimbingSlopes = false): ?int
    {
        [$startX, $startY] = $this->findStartTile($map);
        [$endX, $endY] = $this->findEndTile($map);

        return $this->findLongestTrailFromAToB($map, [], $startX, $startY, $endX, $endY, $allowClimbingSlopes);
    }

    private function findLongestTrailFromAToB(
        array $map,
        array $visited,
        int $startX,
        int $startY,
        int $endX,
        int $endY,
        bool $allowClimbingSlopes,
    ): ?int {
        if (!isset($map[$startY][$startX])) {
            return null;
        }

        if (isset($visited[$startY][$startX])) {
            return null;
        }

        if ($startX === $endX && $startY === $endY) {
            return 0;
        }

        $tile = $map[$startY][$startX];

        if (self::FOREST === $tile) {
            return null;
        }

        $visited[$startY][$startX] = true;

        $moves = [
            'north' => [$startX, $startY - 1],
            'east' => [$startX + 1, $startY],
            'south' => [$startX, $startY + 1],
            'west' => [$startX - 1, $startY],
        ];

        if (!$allowClimbingSlopes) {
            $moves = match ($tile) {
                self::SLOPE_N => [$moves['north']],
                self::SLOPE_S => [$moves['south']],
                self::SLOPE_E => [$moves['east']],
                self::SLOPE_W => [$moves['west']],
                default => $moves,
            };
        }

        $longestTrail = null;

        foreach ($moves as [$nextX, $nextY]) {
            $nextLongestTrail = $this->findLongestTrailFromAToB(
                $map,
                $visited,
                $nextX,
                $nextY,
                $endX,
                $endY,
                $allowClimbingSlopes,
            );

            if (null !== $nextLongestTrail) {
                $longestTrail = max($longestTrail, 1 + $nextLongestTrail);
            }
        }

        return $longestTrail;
    }

    private function findStartTile(array $map): array
    {
        foreach ($map[0] as $x => $mark) {
            if (self::PATH === $mark) {
                return [$x, 0];
            }
        }

        throw new RuntimeException('Could not find starting tile!');
    }

    private function findEndTile(array $map): array
    {
        $y = array_key_last($map);

        foreach ($map[$y] as $x => $tile) {
            if (self::PATH === $tile) {
                return [$x, $y];
            }
        }

        throw new RuntimeException('Could not find ending tile!');
    }
}
