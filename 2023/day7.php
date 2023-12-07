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
$printed = [];

usort($handsAndBids, static fn(array $handAndBid1, array $handAndBid2): int => compareHands($handAndBid1[0], $handAndBid2[0]));

$totalWinnings = 0;
foreach ($handsAndBids as $rank => $handAndBid) {
    $totalWinnings += ($rank + 1) * $handAndBid[1];
}

echo 'Total winnings (part 1): ' . $totalWinnings . PHP_EOL;

usort($handsAndBids, static fn(array $handAndBid1, array $handAndBid2): int => compareHands($handAndBid1[0], $handAndBid2[0], true));

$totalWinnings = 0;
foreach ($handsAndBids as $rank => $handAndBid) {
    $totalWinnings += ($rank + 1) * $handAndBid[1];
}


echo 'Total winnings (part 2): ' . $totalWinnings . PHP_EOL;

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
            $rank1 = strpos($jacksAsJokers ? CARD_RANKS_WITH_JOKER : CARD_RANKS_DEFAULT, $cards1[$i]);
            if (false === $rank1) {
                throw new RuntimeException(sprintf('Invalid card: "%s".', $cards1[$i]));
            }

            $rank2 = strpos($jacksAsJokers ? CARD_RANKS_WITH_JOKER : CARD_RANKS_DEFAULT, $cards2[$i]);
            if (false === $rank2) {
                throw new RuntimeException(sprintf('Invalid card: "%s".', $cards2[$i]));
            }

            return $rank1 <=> $rank2;
        }
    }

    return 0;
}

function getHandType(string $hand, bool $jacksAsJokers = false): HandType
{
    if (5 !== strlen($hand)) {
        throw new RuntimeException(sprintf('Invalid hand: "%s".', $hand));
    }

    $valueCounts = array_count_values(str_split($hand));

    $jokers = 0;

    if ($jacksAsJokers && isset($valueCounts['J'])) {
        $jokers = $valueCounts['J'];
        unset ($valueCounts['J']);
    }

    $valueCountCounts = array_count_values($valueCounts);

    if (1 >= count($valueCounts)) {
        // We only have jokers and/or at most one other rank in our hand
        return HandType::FiveOfAKind;
    }

    if (in_array(4 - $jokers, $valueCounts)) {
        return HandType::FourOfAKind;
    }

    // We have at most two jokers beyond this point

    if (in_array(3 - $jokers, $valueCounts)) {
        // For a Full house, we need 3 + 2 or two pairs and a joker. 3 + 1 and two jokers is a Four of a kind.
        if (1 + $jokers === ($valueCountCounts[2] ?? 0)) {
            return HandType::FullHouse;
        }

        return HandType::ThreeOfAKind;
    }

    // We have at most one joker beyond this point

    if (2 === ($valueCountCounts[2] ?? 0)) {
        // Using a joker to build Two pairs doesn't make sense, because Three of a kind is a better option.
        // Thus, not even testing $jokers here.
        return HandType::TwoPair;
    }

    if (1 === $jokers || 1 === ($valueCountCounts[2] ?? 0)) {
        return HandType::OnePair;
    }

    // No jokers, no pairs. Bad luck.
    return HandType::HighCard;
}
