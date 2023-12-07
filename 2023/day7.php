<?php

const CARD_RANKS_DEFAULT = '23456789TJQKA';
const CARD_RANKS_WITH_JOKER = 'J23456789TQKA';

enum HandType: int
{
    case HighCard = 0;
    case OnePair = 1;
    case TwoPair = 2;
    case ThreeOfAKind = 3;
    case FullHouse = 4;
    case FourOfAKind = 5;
    case FiveOfAKind = 6;
}

$fp = fopen(__DIR__ . '/input/day7.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$handsAndBids = [];

while (!in_array($line = fgets($fp), [false, "\n"])) {
    $handsAndBids[] = explode(' ', trim($line));
}

echo 'Total winnings (part 1): ' . getTotalWinnings($handsAndBids) . PHP_EOL;
echo 'Total winnings (part 2): ' . getTotalWinnings($handsAndBids, true) . PHP_EOL;

function getTotalWinnings(array $handsAndBids, bool $jacksAsJokers = false): int
{
    usort($handsAndBids, static fn(array $handAndBid1, array $handAndBid2): int => compareHands($handAndBid1[0], $handAndBid2[0], $jacksAsJokers));

    $totalWinnings = 0;

    foreach ($handsAndBids as $rank => $handAndBid) {
        $totalWinnings += ($rank + 1) * $handAndBid[1];
    }

    return $totalWinnings;
}

function compareHands(string $hand1, string $hand2, bool $jacksAsJokers = false): int
{
    $type1 = getHandType($hand1, $jacksAsJokers);
    $type2 = getHandType($hand2, $jacksAsJokers);

    if ($type1 !== $type2) {
        return $type1->value <=> $type2->value;
    }

    $cards1 = str_split($hand1);
    $cards2 = str_split($hand2);

    for ($i = 0; $i <= 4; $i++) {
        if ($cards1[$i] !== $cards2[$i]) {
            return getCardRank($cards1[$i], $jacksAsJokers) <=> getCardRank($cards2[$i], $jacksAsJokers);
        }
    }

    return 0;
}

function getCardRank(string $card, bool $jacksAsJokers = false): int
{
    $rank = strpos($jacksAsJokers ? CARD_RANKS_WITH_JOKER : CARD_RANKS_DEFAULT, $card);

    if (false === $rank) {
        throw new RuntimeException(sprintf('Invalid card: "%s".', $card));
    }

    return $rank;
}

function getHandType(string $hand, bool $jacksAsJokers = false): HandType
{
    if (5 !== strlen($hand)) {
        throw new RuntimeException(sprintf('Invalid hand: "%s".', $hand));
    }

    $valueCounts = array_count_values(str_split($hand));
    arsort($valueCounts);

    if ($jacksAsJokers && isset($valueCounts['J'])) {
        $jokers = $valueCounts['J'];

        if (5 === $jokers) {
            return HandType::FiveOfAKind;
        }

        unset ($valueCounts['J']);
        $valueCounts[array_key_first($valueCounts)] += $jokers;
    }

    return match (array_shift($valueCounts)) {
        5 => HandType::FiveOfAKind,
        4 => HandType::FourOfAKind,
        3 => match (array_shift($valueCounts)) {
            2 => HandType::FullHouse,
            1 => HandType::ThreeOfAKind,
        },
        2 => match (array_shift($valueCounts)) {
            2 => HandType::TwoPair,
            1 => HandType::OnePair,
        },
        1 => HandType::HighCard,
    };
}
