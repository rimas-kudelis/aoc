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

const DEFAULT_WORKFLOW = 'in';
const ACCEPT = 'A';
const REJECT = 'R';

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
    $ratingSum += $part->extremelyCool + $part->musical + $part->aerodynamic + $part->shiny;
}

echo 'Sum of partRatings: ' . $ratingSum . PHP_EOL;
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
    $workflowName = DEFAULT_WORKFLOW;

    while (!in_array($workflowName, [ACCEPT, REJECT])) {
        $workflowName = $workflows[$workflowName]->run($part);
    }

    return ACCEPT === $workflowName;
}

class Workflow
{
    private const RULE_SEPARATOR = ',';

    public function __construct(
        /** @var Rule[] $rules */
        private readonly array $rules,
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
        public readonly int $extremelyCool,
        public readonly int $musical,
        public readonly int $aerodynamic,
        public readonly int $shiny,
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
        private readonly ?Comparator $comparator,
        private readonly string $nextWorkflow,
    ) {
    }

    public static function createFromString(string $string): self
    {
        $separatorPosition = strpos($string, self::NEXT_WORKFLOW_SEPARATOR);

        if (false === $separatorPosition) {
            return new self(null, $string);
        }

        return new self(
            Comparator::createFromString(substr($string, 0, $separatorPosition)),
            substr($string, $separatorPosition + 1),
        );
    }

    public function apply(Part $part): ?string
    {
        if (null === $this->comparator || $this->comparator->matches($part)) {
            return $this->nextWorkflow;
        }

        return null;
    }
}

class Comparator
{
    public function __construct(
        private readonly Category $category,
        private readonly Operation $compareOperation,
        private readonly int $compareValue,
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
            Category::ExtremelyCool => $part->extremelyCool,
            Category::Musical => $part->musical,
            Category::Aerodynamic => $part->aerodynamic,
            Category::Shiny => $part->shiny,
        };

        return match ($this->compareOperation) {
            Operation::GreaterThan => $partRating > $this->compareValue,
            Operation::LessThan => $partRating < $this->compareValue,
        };
    }
}
