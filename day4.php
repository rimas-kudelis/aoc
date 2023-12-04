<?php

$fp = fopen(__DIR__ . '/input/day4.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('no file');
}

/** @var Card[] $cards */
$cards = [];
$cardPoints = [];

while (false !== ($line = fgets($fp))) {
    if ('' === $line) {
        continue;
    }

    $card = parseCard($line);

    $cards[] = $card;
    $cardPoints[] = $card->points;
}

echo 'Total points: ' . array_sum($cardPoints) . PHP_EOL;

$cardAmounts = array_fill(0, count($cards), 1);
foreach ($cards as $cardIndex => $card) {
    $thisCardAmount = $cardAmounts[$cardIndex];

    for ($i = 1; $i <= $card->matches; $i++) {
        if (isset($cardAmounts[$cardIndex + $i])) {
            $cardAmounts[$cardIndex + $i] += $thisCardAmount;
        }
    }
}

var_dump($cardAmounts);

echo 'Total cards: ' . array_sum($cardAmounts) . PHP_EOL;

function parseCard(string $line): ?Card
{
    $matches = [];
    preg_match('/^Card\s+[0-9]+:\s+(([0-9]+\s+)+)\|((\s+[0-9]+)+)$/', $line, $matches);

    if (5 !== count($matches)) {
        throw new RuntimeException('Unexpected line: ' . $line);
    }

    $winningNumbers = castToInts(preg_split('/\s+/', trim($matches[1])));
    $gotNumbers = castToInts(preg_split('/\s+/', trim($matches[3])));

    $matches = array_intersect($winningNumbers, $gotNumbers);
    $foundMatches = count($matches);
    $points = (0 === $foundMatches) ? 0 : 2 ** ($foundMatches - 1);

    return new Card($winningNumbers, $gotNumbers, $foundMatches, $points);
}

function castToInts(array $strings): array
{
    return array_map(static fn(string $string) => (int)$string, $strings);
}

class Card
{
    public function __construct(
        public readonly array $winningNumbers,
        public readonly array $hadNumbers,
        public readonly int   $matches,
        public readonly int   $points,
    )
    {
    }
}