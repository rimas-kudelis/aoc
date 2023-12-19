<?php

enum Category: string
{
    case ExtremelyCool = 'x';
    case Musical = 'm';
    case Aerodynamic = 'a';
    case Shiny = 's';
}

enum Operation: string
{
    case LessThan = '<';
    case GreaterThan = '>';
}

const DEFAULT_WORKFLOW_NAME = 'in';
const ACCEPT = 'A';
const REJECT = 'R';

const RATING_MIN = 1;
const RATING_MAX = 4000;

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day19.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$workflows = readWorkflows($fp);

$acceptedParts = [];

foreach (readParts($fp) as $part) {
    if (canAccept($part, $workflows)) {
        $acceptedParts[] = $part;
    }
}

$ratingSum = 0;
foreach ($acceptedParts as $part) {
    $ratingSum += $part->extremelyCoolRating + $part->musicalRating + $part->aerodynamicRating + $part->shinyRating;
}

echo 'Sum of part ratings: ' . $ratingSum . PHP_EOL;
echo 'Possible distinct combinations: ' . calculateAcceptableCombos($workflows) . PHP_EOL;
echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function readWorkflows($fp): array
{
    $workflows = [];

    while (false !== $line = fgets($fp)) {
        $line = trim($line);

        if ('' === $line) {
            break;
        }

        list($name, $workflowString) = explode('{', substr($line, 0, -1));

        $workflows[$name] = Workflow::createFromString($workflowString);
    }

    return $workflows;
}

function readParts($fp): iterable
{
    while (false !== $line = fgets($fp)) {
        $line = trim($line);

        if ('' === $line) {
            continue;
        }

        yield (Part::createFromString($line));
    }
}

/** @param Workflow[] $workflows */
function canAccept(Part $part, array $workflows): bool
{
    $workflowName = DEFAULT_WORKFLOW_NAME;

    while (!in_array($workflowName, [ACCEPT, REJECT])) {
        $workflowName = $workflows[$workflowName]->run($part);
    }

    return ACCEPT === $workflowName;
}

/**
 * @param Workflow[] $workflows
 * @param array<string, Interval> $allowedRatingIntervals
 */
function calculateAcceptableCombos(
    array $workflows,
    array $allowedRatingIntervals = [],
    string $workflowName = DEFAULT_WORKFLOW_NAME,
): int {
    if ([] === $allowedRatingIntervals) {
        foreach (Category::cases() as $category) {
            $allowedRatingIntervals[$category->value] = new Interval(RATING_MIN, RATING_MAX);
        }
    }

    $nextAllowedRatingIntervals = $currentAllowedRatingIntervals = $allowedRatingIntervals;
    $acceptableCombos = 0;

    foreach ($workflows[$workflowName]->rules as $rule) {
        $currentAllowedRatingIntervals = $nextAllowedRatingIntervals;

        if (in_array(null, $currentAllowedRatingIntervals)) {
            continue;
        }

        if (null !== $rule->matcher) {
            $category = $rule->matcher->category->value;
            list($currentAllowedRatingIntervals[$category], $nextAllowedRatingIntervals[$category]) =
                $currentAllowedRatingIntervals[$category]->splitByMatcher($rule->matcher);
        }

        if (in_array(null, $currentAllowedRatingIntervals)) {
            continue;
        }

        if (REJECT === $rule->nextWorkflow) {
            continue;
        }

        if (ACCEPT === $rule->nextWorkflow) {
            $acceptableCombos += array_product(
                array_map(
                    static fn(Interval $interval): int => $interval->max - $interval->min + 1,
                    $currentAllowedRatingIntervals,
                ),
            );

            continue;
        }

        $acceptableCombos += calculateAcceptableCombos($workflows, $currentAllowedRatingIntervals, $rule->nextWorkflow);
    }

    return $acceptableCombos;
}

class Workflow
{
    private const RULE_SEPARATOR = ',';

    public function __construct(
        /** @var Rule[] $rules */
        public readonly array $rules,
    ) {
    }

    public static function createFromString(string $string): self
    {
        $rules = [];

        foreach (explode(self::RULE_SEPARATOR, $string) as $ruleString) {
            $rules[] = Rule::createFromString($ruleString);
        }

        return new self($rules);
    }

    public function run(Part $part): string
    {
        foreach ($this->rules as $rule) {
            $nextWorkflow = $rule->apply($part);

            if (null !== $nextWorkflow) {
                return $nextWorkflow;
            }
        }

        throw new RuntimeException('No matching rule found in workflow!');
    }
}

class Part
{
    public function __construct(
        public readonly int $extremelyCoolRating,
        public readonly int $musicalRating,
        public readonly int $aerodynamicRating,
        public readonly int $shinyRating,
    ) {
    }

    public static function createFromString(string $string): self
    {
        $ratings = explode(',', $string);

        return new self(
            (int)substr($ratings[0], 3),
            (int)substr($ratings[1], 2),
            (int)substr($ratings[2], 2),
            (int)substr($ratings[3], 2, -1),
        );
    }
}

class Rule
{
    private const NEXT_WORKFLOW_SEPARATOR = ':';

    public function __construct(
        public readonly ?Matcher $matcher,
        public readonly string $nextWorkflow,
    ) {
    }

    public static function createFromString(string $string): self
    {
        $separatorPosition = strpos($string, self::NEXT_WORKFLOW_SEPARATOR);

        if (false === $separatorPosition) {
            return new self(null, $string);
        }

        return new self(
            Matcher::createFromString(substr($string, 0, $separatorPosition)),
            substr($string, $separatorPosition + 1),
        );
    }

    public function apply(Part $part): ?string
    {
        if (null === $this->matcher || $this->matcher->matches($part)) {
            return $this->nextWorkflow;
        }

        return null;
    }
}

class Matcher
{
    public function __construct(
        public readonly Category $category,
        public readonly Operation $compareOperation,
        public readonly int $compareValue,
    ) {
    }

    public static function createFromString(string $string): self
    {
        return new self(
            Category::from($string[0]),
            Operation::from($string[1]),
            (int)substr($string, 2),
        );
    }

    public function matches(Part $part): bool
    {
        $partRating = match ($this->category) {
            Category::ExtremelyCool => $part->extremelyCoolRating,
            Category::Musical => $part->musicalRating,
            Category::Aerodynamic => $part->aerodynamicRating,
            Category::Shiny => $part->shinyRating,
        };

        return match ($this->compareOperation) {
            Operation::GreaterThan => $partRating > $this->compareValue,
            Operation::LessThan => $partRating < $this->compareValue,
        };
    }
}

class Interval
{
    public function __construct(
        public readonly int $min,
        public readonly int $max,
    ) {
    }

    public function splitByMatcher(?Matcher $matcher): array
    {
        if (null === $matcher) {
            return [$this, null];
        }

        return match ($matcher->compareOperation) {
            Operation::GreaterThan => match (true) {
                $matcher->compareValue < $this->min => [$this, null],
                $matcher->compareValue > $this->max => [null, $this],
                default => [new self($matcher->compareValue + 1, $this->max), new self($this->min, $matcher->compareValue)],
            },
            Operation::LessThan => match (true) {
                $matcher->compareValue > $this->max => [$this, null],
                $matcher->compareValue < $this->min => [null, $this],
                default => [new self($this->min, $matcher->compareValue - 1), new self($matcher->compareValue, $this->max)],
            }
        };
    }
}
