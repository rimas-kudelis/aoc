<?php

const STATE_NONE = 'none';
const STATE_SEED_TO_SOIL = 'sts';
const STATE_SOIL_TO_FERTILIZER = 'stf';
const STATE_FERTILIZER_TO_WATER = 'ftw';
const STATE_WATER_TO_LIGHT = 'wtl';
const STATE_LIGHT_TO_TEMPERATURE = 'ltt';
const STATE_TEMPERATURE_TO_HUMIDITY = 'tth';
const STATE_HUMIDITY_TO_LOCATION = 'htl';

$fp = fopen(__DIR__ . '/input/day5.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$seedNumbers = [];
$seedToSoilMapper = new NumberMapper();
$soilToFertilizerMapper = new NumberMapper();
$fertilizerToWaterMapper = new NumberMapper();
$waterToLightMapper = new NumberMapper();
$lightToTemperatureMapper = new NumberMapper();
$temperatureToHumidityMapper = new NumberMapper();
$humidityToLocationMapper = new NumberMapper();

$state = STATE_NONE;
while (false !== ($line = fgets($fp))) {
    $line = trim($line);

    if ('' === $line) {
        continue;
    }

    if (str_starts_with($line, 'seeds: ')) {
        $seedNumbers = parseSeedNumbers($line);

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

    switch ($state) {
        case STATE_SEED_TO_SOIL:
            applyRangeLineToMapper($line, $seedToSoilMapper);
            break;
        case STATE_SOIL_TO_FERTILIZER:
            applyRangeLineToMapper($line, $soilToFertilizerMapper);
            break;
        case STATE_FERTILIZER_TO_WATER:
            applyRangeLineToMapper($line, $fertilizerToWaterMapper);
            break;
        case STATE_WATER_TO_LIGHT:
            applyRangeLineToMapper($line, $waterToLightMapper);
            break;
        case STATE_LIGHT_TO_TEMPERATURE:
            applyRangeLineToMapper($line, $lightToTemperatureMapper);
            break;
        case STATE_TEMPERATURE_TO_HUMIDITY:
            applyRangeLineToMapper($line, $temperatureToHumidityMapper);
            break;
        case STATE_HUMIDITY_TO_LOCATION:
            applyRangeLineToMapper($line, $humidityToLocationMapper);
            break;
        default:
            throw new RuntimeException('Unexpected state: ' . $state);
    }
}

$seedToLocationMapper = $seedToSoilMapper
    ->combine($soilToFertilizerMapper)
    ->combine($fertilizerToWaterMapper)
    ->combine($waterToLightMapper)
    ->combine($lightToTemperatureMapper)
    ->combine($temperatureToHumidityMapper)
    ->combine($humidityToLocationMapper);

$lowestLocationNumber = PHP_INT_MAX;
$checked = 0;

foreach ($seedNumbers as $seedNumber) {
    $lowestLocationNumber = min($lowestLocationNumber, $seedToLocationMapper->map($seedNumber));
    $checked++;
}

echo 'Lowest location number (part 1): ' . $lowestLocationNumber . PHP_EOL;
echo 'Numbers checked: ' . $checked . PHP_EOL;

$lowestLocationNumber = PHP_INT_MAX;
$checked = 0;

foreach (getSeedRanges($seedNumbers) as $seedNumber => $seedNumberRangeLength) {
    $lowestLocationNumber = min($lowestLocationNumber, $seedToLocationMapper->map($seedNumber));
    $checked++;
    $maxSeedNumberInRange = $seedNumber + $seedNumberRangeLength - 1;

    foreach ($seedToLocationMapper->getRangeStartNumbers() as $mapRangeStartNumber) {
        if ($mapRangeStartNumber > $seedNumber && $mapRangeStartNumber <= $maxSeedNumberInRange) {
            $lowestLocationNumber = min($lowestLocationNumber, $seedToLocationMapper->map($mapRangeStartNumber));
            $checked++;
        }
    }
}

echo 'Lowest location number (part 2): ' . $lowestLocationNumber . PHP_EOL;
echo 'Numbers checked: ' . $checked . PHP_EOL;

function parseSeedNumbers(string $line): array
{
    $seeds = preg_split('/\s+/', trim(substr($line, 6)));
    return castToInts($seeds);
}

function applyRangeLineToMapper(string $line, NumberMapper $mapper): void
{
    list($targetNumber, $sourceNumber, $rangeLength) = castToInts(explode(' ', trim($line)));

    $mapper->addRange($sourceNumber, $targetNumber, $rangeLength);
}

/**
 * Converts an original seed number array from part 1 of the exercise to the
 * [seed number range start] → [range length] array used for part 2.
 *
 * @param int[] $seeds
 * @return array<int, int>
 */
function getSeedRanges(array $seeds): array
{
    $seeds = array_values($seeds);
    $ranges = [];

    for ($key = 1; $key < count ($seeds); $key += 2) {
        $ranges[$seeds[$key - 1]] = $seeds[$key];
    }

    return $ranges;
}

/**
 * @param string[] $strings
 * @return int[]
 */
function castToInts(array $strings): array
{
    return array_map(static fn(string $string) => (int)$string, $strings);
}

class NumberMapper
{
    /** @var array<int, int> */
    private array $offsetMap = [0 => 0];

    /** @var array<int, int> */
    private array $reverseOffsetMap = [0 => 0];

    /**
     * Adds a range to the mapper config. $rangeLength = 0 (default) causes only the start of the range to be
     * configured, meaning this range is either open-ended, or is ended by another explicitly configured range.
     * Note: range conflicts (intersections) are not prevented.
     */
    public function addRange(int $sourceNumber, int $targetNumber, int $rangeLength = 0): void
    {
        $this->addRangeToMap($this->offsetMap, $sourceNumber, $targetNumber, $rangeLength);
        $this->addRangeToMap($this->reverseOffsetMap, $targetNumber, $sourceNumber, $rangeLength);
    }

    public function map(int $sourceNumber): int
    {
        return $this->doMap($sourceNumber, $this->offsetMap);
    }

    /** @return int[] */
    public function getRangeStartNumbers(): array
    {
        return array_keys($this->offsetMap);
    }

    public function combine(self $nextMap): self
    {
        $rangeStartNumbers = $this->getRangeStartNumbers();
        $nextMapRangeStartNumbers = $nextMap->getRangeStartNumbers();

        foreach ($nextMapRangeStartNumbers as $nextMapRangeStartNumber) {
            $rangeStartNumbers[] = $this->reverseMap($nextMapRangeStartNumber);
        }

        $rangeStartNumbers = array_unique($rangeStartNumbers);
        sort($rangeStartNumbers);

        $combinedMap = new self();
        foreach ($rangeStartNumbers as $rangeStartNumber) {
            $combinedMap->addRange($rangeStartNumber, $nextMap->map($this->map($rangeStartNumber)));
        }

        return $combinedMap;
    }

    private function reverseMap(int $targetNumber): int
    {
        return $this->doMap($targetNumber, $this->reverseOffsetMap);
    }

    /**
     * Add a [range start number] → [number offset] mapping to the given offset map.
     * If $rangeLength > 0 and there is no subsequent range configured right after the end of this one,
     * this method also configures that subsequent range with the offset of 0.
     */
    private function addRangeToMap(array &$map, int $sourceNumber, int $targetNumber, int $rangeLength): void
    {
        if (0 > $rangeLength) {
            throw new InvalidArgumentException('$rangeLength must be a non-negative number, got ' . $rangeLength);
        }

        $map[$sourceNumber] = $targetNumber - $sourceNumber;
        $nextNumber = $sourceNumber + $rangeLength;

        if (!isset($map[$nextNumber])) {
            $map[$nextNumber] = 0;
        }

        ksort($map);
    }

    private function doMap(int $number, array $map): int
    {
        $lastOffset = 0;

        foreach ($map as $rangeStart => $offset) {
            if ($rangeStart > $number) {
                break;
            }

            $lastOffset = $offset;
        }

        return $number + $lastOffset;
    }
}
