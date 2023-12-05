<?php

const STATE_NONE = 'none';
const STATE_SEED_TO_SOIL = 'sts';
const STATE_SOIL_TO_FERTILIZER = 'stf';
const STATE_FERTILIZER_TO_WATER = 'ftw';
const STATE_WATER_TO_LIGHT = 'wtl';
const STATE_LIGHT_TO_TEMPERATURE = 'ltt';
const STATE_TEMPERATURE_TO_HUMIDITY = 'tth';
const STATE_HUMIDITY_TO_LOCATION = 'htl';

const MAP_DATA_OFFSET = 'offset';
const MAP_DATA_LENGTH = 'length';

$fp = fopen(__DIR__ . '/input/day5.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$seeds = [];
$seedToSoilMapData = $soilToFertilizerMapData = $fertilizerToWaterMapData = $waterToLightMapData = $lightToTemperatureMapData = $temperatureToHumidityMapData = $humidityToLocationMapData = [];

$state = STATE_NONE;
while (false !== ($line = fgets($fp))) {
    $line = trim($line);

    if ('' === $line) {
        continue;
    }

    if (str_starts_with($line, 'seeds: ')) {
        $seeds = parseSeeds($line);
        continue;
    }

    if ('seed-to-soil map:' === $line) {
        $state = STATE_SEED_TO_SOIL;
        continue;
    }

    if ('soil-to-fertilizer map:' === $line) {
        $state = STATE_SOIL_TO_FERTILIZER;
        continue;
    }

    if ('fertilizer-to-water map:' === $line) {
        $state = STATE_FERTILIZER_TO_WATER;
        continue;
    }

    if ('water-to-light map:' === $line) {
        $state = STATE_WATER_TO_LIGHT;
        continue;
    }

    if ('light-to-temperature map:' === $line) {
        $state = STATE_LIGHT_TO_TEMPERATURE;
        continue;
    }

    if ('temperature-to-humidity map:' === $line) {
        $state = STATE_TEMPERATURE_TO_HUMIDITY;
        continue;
    }

    if ('humidity-to-location map:' === $line) {
        $state = STATE_HUMIDITY_TO_LOCATION;
        continue;
    }

    if (!preg_match('/^[0-9]+\s+[0-9]+\s+[0-9]+$/', $line)) {
        throw new RuntimeException('Unexpected line: ' . $line);
    }

    switch($state) {
        case STATE_SEED_TO_SOIL:
            addToMapData($seedToSoilMapData, $line);
            break;
        case STATE_SOIL_TO_FERTILIZER:
            addToMapData($soilToFertilizerMapData, $line);
            break;
        case STATE_FERTILIZER_TO_WATER:
            addToMapData($fertilizerToWaterMapData, $line);
            break;
        case STATE_WATER_TO_LIGHT:
            addToMapData($waterToLightMapData, $line);
            break;
        case STATE_LIGHT_TO_TEMPERATURE:
            addToMapData($lightToTemperatureMapData, $line);
            break;
        case STATE_TEMPERATURE_TO_HUMIDITY:
            addToMapData($temperatureToHumidityMapData, $line);
            break;
        case STATE_HUMIDITY_TO_LOCATION:
            addToMapData($humidityToLocationMapData, $line);
            break;
        default:
            throw new RuntimeException('Unexpected state: ' . $state);
    }
}

$seedToSoilMap = getOffsetMap($seedToSoilMapData);
$soilToFertilizerMap = getOffsetMap($soilToFertilizerMapData);
$fertilizerToWaterMap = getOffsetMap($fertilizerToWaterMapData);
$waterToLightMap = getOffsetMap($waterToLightMapData);
$lightToTemperatureMap = getOffsetMap($lightToTemperatureMapData);
$temperatureToHumidityMap = getOffsetMap($temperatureToHumidityMapData);
$humidityToLocationMap = getOffsetMap($humidityToLocationMapData);

$lowestLocationNumber = PHP_INT_MAX;
foreach ($seeds as $seedNumber) {
    $locationNumber = mapNumber(
        mapNumber(
            mapNumber(
                mapNumber(
                    mapNumber(
                        mapNumber(
                            mapNumber($seedNumber, $seedToSoilMap),
                            $soilToFertilizerMap,
                        ),
                        $fertilizerToWaterMap,
                    ),
                    $waterToLightMap,
                ),
                $lightToTemperatureMap,
            ),
            $temperatureToHumidityMap,
        ),
        $humidityToLocationMap,
    );

    $lowestLocationNumber = min($locationNumber, $lowestLocationNumber);
}

var_dump($seeds, $seedToSoilMap, $soilToFertilizerMap, $fertilizerToWaterMap, $waterToLightMap, $lightToTemperatureMap, $temperatureToHumidityMap, $humidityToLocationMap);

echo 'Lowest location number: ' . $lowestLocationNumber . PHP_EOL;

function parseSeeds(string $line): array
{
    $seeds = preg_split('/\s+/', trim(substr($line, 6)));
    return castToInts($seeds);
}

function addToMapData(array &$map, string $line): void
{
    list($destination, $source, $length) = explode(' ', trim($line));
    $map[$source] = [MAP_DATA_OFFSET => (int)$destination - (int)$source, MAP_DATA_LENGTH => (int)$length];
}

function getOffsetMap(array $mapData): array
{
    $lastMappedNumber = -1;
    ksort($mapData);
    $normalizedMap = [];

    foreach ($mapData as $sourceNumber => $currentMapData) {
        if ($sourceNumber > $lastMappedNumber) {
            $normalizedMap[$lastMappedNumber + 1] = 0;
        }

        $normalizedMap[$sourceNumber] = $currentMapData[MAP_DATA_OFFSET];
        $lastMappedNumber = $sourceNumber + $currentMapData[MAP_DATA_LENGTH] - 1;
    }

    $normalizedMap[$lastMappedNumber + 1] = 0;

    return $normalizedMap;
}

function mapNumber(int $number, array $offsetMap): int
{
    $lastOffset = 0;

    foreach ($offsetMap as $offsetMapIndex => $offset) {
        if ($number < $offsetMapIndex) {
            break;
        }

        $lastOffset = $offset;
    }

    return $number + $lastOffset;
}

function castToInts(array $strings): array
{
    return array_map(static fn(string $string) => (int) $string, $strings);
}
