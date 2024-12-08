<?php

const WORKING = '.';
const DAMAGED = '#';
const UNKNOWN = '?';
const SEQUENCE_SEPARATOR = ',';

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day12.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$totalPossibleFoldedArrangements = $totalPossibleUnfoldedArrangements = 0;

// Caches
$similarArrangementCountsByMaxBlockSize = [];
$samplePatterns = [];

foreach (getRecords($fp) as $i => list($springRow, $damagedSpringCounts)) {
    echo $i . '. ' . $springRow . ' : ' , $damagedSpringCounts . '... ';
    $totalPossibleFoldedArrangements += countPossibleArrangements($springRow, $damagedSpringCounts);
    $totalPossibleUnfoldedArrangements += countPossibleArrangements(
        implode(UNKNOWN, array_fill(0, 5, $springRow)),
        implode(SEQUENCE_SEPARATOR, array_fill(0, 5, $damagedSpringCounts)),
    );
    echo 'Done. ' . $totalPossibleUnfoldedArrangements . PHP_EOL;
}

echo 'Total possible folded arrangements: ' . $totalPossibleFoldedArrangements . PHP_EOL;
echo 'Total possible unfolded arrangements: ' . $totalPossibleUnfoldedArrangements . PHP_EOL;
echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function getRecords($fp): iterable
{
    while (false !== $line = fgets($fp)) {
        if ("\n" === $line) {
            continue;
        }
        $line = trim($line);

        if (!preg_match('/^[#.?]+\s([0-9]+,?)+$/', $line)) {
            throw new RuntimeException('Unexpected line: ' . $line);
        }

        yield explode(' ', trim($line));
    }
}

function countPossibleArrangements(string $map, string $expectedDamagedSpringCounts): int
{
    $currentDamagedSpringCounts = getKnownDamagedSpringGroups($map);

    // All springs marked with status unknown are damaged
    if ($expectedDamagedSpringCounts === $currentDamagedSpringCounts) {
        return 1;
    }

    $firstUnknownPosition = strpos($map, UNKNOWN);

    // No springs with unknown status, and the arrangement is not as expected. This is a dead end.
    if (false === $firstUnknownPosition) {
        return 0;
    }

    $largestExpectedDamagedSpringGroupSize = getLargestDamagedSpringGroupSize($expectedDamagedSpringCounts);

    // Too many damaged springs in a row.
    if ($largestExpectedDamagedSpringGroupSize < getLargestDamagedSpringGroupSize($currentDamagedSpringCounts)) {
        return 0;
    }

//    $expectedDamagedSpringGroupCount = countDamagedSpringGroups($expectedDamagedSpringCounts);
//
//    // Too many damaged spring groups.
//    if ($expectedDamagedSpringGroupCount < countDamagedSpringGroups($currentDamagedSpringCounts)) {
//        return 0;
//    }

    // Known exact initial damaged spring counts don't match what's expected.
    if (!str_starts_with($expectedDamagedSpringCounts, getFirstExactDamagedSpringGroups($map, $firstUnknownPosition))) {
        return 0;
    }


    // A spring with an unknown status is next to a damaged one. Can't use string matching just yet.
    if (DAMAGED === $map[$firstUnknownPosition - 1]) {
        return countPossibleArrangements(substr_replace($map, WORKING, $firstUnknownPosition, 1), $expectedDamagedSpringCounts)
            + countPossibleArrangements(substr_replace($map, DAMAGED, $firstUnknownPosition, 1), $expectedDamagedSpringCounts);
    }

    $lastUnknownPositionInFirstGroup = getLastUnknownPositionInGroup($map, $firstUnknownPosition);

    // A spring with an unknown status precedes a damaged one. Can't use string matching just yet.
    if ($lastUnknownPositionInFirstGroup !== strlen ($map) - 1 && DAMAGED === $map[$lastUnknownPositionInFirstGroup + 1]) {
        return countPossibleArrangements(substr_replace($map, WORKING, $lastUnknownPositionInFirstGroup, 1), $expectedDamagedSpringCounts)
            + countPossibleArrangements(substr_replace($map, DAMAGED, $lastUnknownPositionInFirstGroup, 1), $expectedDamagedSpringCounts);
    }

    // The first damaged spring group is surrounded by working springs.
    // Only test one sample arrangement for each arrangement pattern.
    $validArrangements = 0;
    $unknownBlockLength = $lastUnknownPositionInFirstGroup - $firstUnknownPosition + 1;
    foreach (getSimilarArrangementCounts($unknownBlockLength, $largestExpectedDamagedSpringGroupSize, countDamagedSpringGroups($expectedDamagedSpringCounts)) as $arrangement => $multiplier) {
        $modifiedMap = substr_replace($map, getSamplePatternForArrangement($arrangement), $firstUnknownPosition, $unknownBlockLength);
        $valid = countPossibleArrangements($modifiedMap, $expectedDamagedSpringCounts);

        $validArrangements += $multiplier * $valid;
    }

    return $validArrangements;
}

function getKnownDamagedSpringGroups(string $map): string
{
    return implode(
        SEQUENCE_SEPARATOR,
        array_map(
            static fn(string $string): int => strlen($string),
            array_filter(
                preg_split('/[^' . DAMAGED . ']+/', $map),
                static fn(string $string): bool => '' !== $string,
            ),
        ),
    );
}

function getFirstExactDamagedSpringGroups(string $map, int $firstUnknownSpringPosition): string
{
    $lastWorkingSpringPosition = strrpos(substr($map, 0, $firstUnknownSpringPosition), WORKING);

    return getKnownDamagedSpringGroups(substr($map, 0, $lastWorkingSpringPosition));
}

function getDamagedSpringCountPattern(string $map): ?string
{
    $mapComponents = explode(UNKNOWN, $map);
    $damagedSpringCountsInComponents = [];

    foreach ($mapComponents as $index => $mapComponent) {
        if ('' === $mapComponent) {
            continue;
        }

        $counts = explode(SEQUENCE_SEPARATOR, getKnownDamagedSpringGroups($mapComponent));
        if (DAMAGED === $mapComponent[0] && isset($mapComponents[$index - 1])) {
            array_shift($counts);
        }

        if (DAMAGED === substr($mapComponent, -1) && isset($mapComponents[$index + 1])) {
            array_pop($counts);
        }

        $damagedSpringCountsInComponents[] = implode(SEQUENCE_SEPARATOR, $counts);
    }

    $damagedSpringCountsInComponents = array_filter(
        $damagedSpringCountsInComponents,
        static fn(string $string): bool => '' !== $string,
    );

    if ([] === $damagedSpringCountsInComponents) {
        return null;
    }

    return '/' . implode(',([0-9]+,)*', $damagedSpringCountsInComponents) . '/';
}

function getSimilarArrangementCounts(int $blockSize, int $maxGroupSize, int $maxGroupCount): array
{
    global $similarArrangementCountsByMaxBlockSize;

    $maxGroupSize = min($blockSize, $maxGroupSize);

    if (!isset($similarArrangementCountsByMaxBlockSize[$blockSize])
        || $similarArrangementCountsByMaxBlockSize[$blockSize]['maxGroupSize'] < $maxGroupSize
        || $similarArrangementCountsByMaxBlockSize[$blockSize]['maxGroupCount'] < $maxGroupCount
    ) {
        for ($firstGroupSize = 1; $firstGroupSize <= $maxGroupSize; $firstGroupSize++) {
            // There are 5 (5-1+1) ways to place a group of size 1 in a block of size 5, or
            //           4 (5-2+1) ways to place a group of size 2 in a block of size 5, and so on.
            $arrangements[$firstGroupSize] = $blockSize - $firstGroupSize + 1;

            $remainingSize = $blockSize - $firstGroupSize;
            while (1 < $remainingSize && 1 < $maxGroupCount) {
                foreach (getSimilarArrangementCounts(--$remainingSize, $maxGroupSize, $maxGroupCount - 1) as $key => $count) {
                    // Special case for no damaged springs in a subgroup. This is already accounted for above.
                    if ('' === $key) {
                        continue;
                    }

                    $key = $firstGroupSize . SEQUENCE_SEPARATOR . $key;
                    $arrangements[$key] = ($arrangements[$key] ?? 0) + $count;
                }
            }
        }

        // There's only one possible spring arrangement with no damaged springs regardless of the group size.
        $arrangements[''] = 1;
        $similarArrangementCountsByMaxBlockSize[$blockSize] = [
            'maxGroupSize' => $maxGroupSize,
            'maxGroupCount' => $maxGroupCount,
            'arrangements' => $arrangements,
        ];
    }

    return $similarArrangementCountsByMaxBlockSize[$blockSize]['arrangements'];
}

function getSamplePatternForArrangement(string $arrangement): string
{
    global $samplePatterns;

    if ('' === $arrangement) {
        return '.';
    }

    if (!isset($samplePatterns[$arrangement])) {
        $blockSizes = explode(SEQUENCE_SEPARATOR, $arrangement);

        $samplePatterns[$arrangement] = implode(WORKING, array_map(static fn(int $blockSize): string => str_repeat(DAMAGED, $blockSize), $blockSizes));
    }

    return $samplePatterns[$arrangement];
}

function getPossibleArrangements(string $row): void
{
    $markerGroups = groupSpringMarkers($row);

    var_dump($markerGroups);
}

function groupSpringMarkers(string $row): array
{
    $markerGroups = [];
    $groupMarker = $row[0];
    $count = 0;

    foreach (str_split($row) as $marker) {
        if ($groupMarker === $marker) {
            $count++;
            continue;
        }

        $markerGroups[] = [$groupMarker, $count];
        $groupMarker = $marker;
        $count = 1;
    }

    $markerGroups[] = [$groupMarker, $count];

    return $markerGroups;
}

function getLastUnknownPositionInGroup(string $map, int $offset): int
{
    for (; $offset < strlen($map) - 1; $offset++) {
        if (UNKNOWN !== $map[$offset + 1]) {
            return $offset;
        }
    }

    return $offset;
}

function getLargestDamagedSpringGroupSize(string $damagedSpringCounts): int
{
    return max(array_map('intval', explode(',', $damagedSpringCounts)));
}

function countDamagedSpringGroups(string $damagedSpringCounts): int
{
    return count(explode(',', $damagedSpringCounts));
}