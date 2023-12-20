<?php

const TIMES = 1000;

enum Pulse
{
    case Low;
    case High;
}

enum FlipState
{
    case On;
    case Off;
}

$start = microtime(true);

$fp = fopen(__DIR__ . '/input/day20.txt', 'r');

if (false === $fp) {
    throw new RuntimeException('Could not open input file!');
}

$controlPanel = setUpEquipment($fp);
$pushCount = 0;
$pulseCounts = [];

do {
    ++$pushCount;
    $controlPanel->pushButton();
    $cyclePulseCounts = ['l' => $controlPanel->getLowPulseCounter(), 'h' => $controlPanel->getHighPulseCounter()];
    $pulseCounts[] = $cyclePulseCounts;
    //printf('Step %d: %d low, %d high.' . PHP_EOL, $pushCount, $cyclePulseCounts['l'], $cyclePulseCounts['h']);
} while (!$controlPanel->isSystemInDefaultState() && TIMES > $pushCount);

$fullCycles = $controlPanel->isSystemInDefaultState() ? (int)(TIMES / $pushCount) : 0;
$extraPushes = $controlPanel->isSystemInDefaultState() ? TIMES % $pushCount : $pushCount;

$lowPulseCounter = $controlPanel->getLowPulseCounter() * $fullCycles + ($extraPushes > 0 ? $pulseCounts[$extraPushes - 1]['l'] : 0);
$highPulseCounter = $controlPanel->getHighPulseCounter() * $fullCycles + ($extraPushes > 0 ? $pulseCounts[$extraPushes - 1]['h'] : 0);

printf(
    'Pulse counts after %d button pushes (%d full cycles and %d extra pushes): %d low, %d high. Result: %d .' . PHP_EOL,
    $pushCount,
    $fullCycles,
    $extraPushes,
    $lowPulseCounter,
    $highPulseCounter,
    $lowPulseCounter * $highPulseCounter,
);

echo 'Calculation took ' . microtime(true) - $start . ' seconds.' . PHP_EOL;

function setUpEquipment($fp): ControlPanel
{
    $queue = new PulseQueue();

    $modules = [];

    while (false !== $line = fgets($fp)) {
        $line = trim($line);

        if ('' === $line) {
            continue;
        }

        list ($moduleName, $destinationNames) = explode(' -> ', $line);
        $destinationNames = explode(', ', $destinationNames);

        if ('broadcaster' === $moduleName) {
            $modules[$moduleName] = [
                new Broadcaster('broadcaster', $queue),
                $destinationNames,
            ];

            continue;
        }

        if ('%' === $moduleName[0]) {
            $moduleName = substr($moduleName, 1);
            $modules[$moduleName] = [
                new FlipFlop($moduleName, $queue),
                $destinationNames,
            ];

            continue;
        }

        if ('&' === $moduleName[0]) {
            $moduleName = substr($moduleName, 1);
            $modules[$moduleName] = [
                new Conjunction($moduleName, $queue),
                $destinationNames,
            ];

            continue;
        }

        throw new RuntimeException(sprintf('Unexpected module name "%d"!', $moduleName));
    }

    $controlPanel = new ControlPanel('controlPanel', $queue);

    /**
     * @var Module $module
     * @var string[] $destinationNames
     */
    foreach ($modules as list($module, $destinationNames)) {
        foreach ($destinationNames as $destinationName) {
            if (!isset($modules[$destinationName])) {
                // Create dummy modules for all previously registered but not explicitly created module names
                $modules[$destinationName] = [new Module($destinationName, $queue), []];
            }

            /** @var Module $destination */
            $destination = $modules[$destinationName][0];
            $module->addDestination($destination);
        }
    }

    foreach ($modules as list($module, $destinationNames)) {
        $controlPanel->registerModule($module);
    }

    $controlPanel->addDestination($modules['broadcaster'][0]);

    return $controlPanel;
}

class PulseQueue
{
    private bool $processing = false;

    /** @var array<int, array{0: Module|null, 1: Module, 2: Pulse}> */
    private array $queue = [];

    private int $lowPulseCounter = 0;

    private int $highPulseCounter = 0;

    public function add(?Module $source, Module $destination, Pulse $pulse): void
    {
        $this->queue[] = [$source, $destination, $pulse];
    }

    public function process(): void
    {
        if ($this->processing) {
            throw new RuntimeException('The queue is already being processed!');
        }

        $this->processing = true;

        while (null !== ($pulseInfo = array_shift($this->queue))) {
            $pulseInfo[2] === Pulse::High ? ++$this->highPulseCounter : ++$this->lowPulseCounter;
            $pulseInfo[1]->receive($pulseInfo[0], $pulseInfo[2]);
        }

        $this->processing = false;
    }

    public function getLowPulseCounter(): int
    {
        return $this->lowPulseCounter;
    }

    public function getHighPulseCounter(): int
    {
        return $this->highPulseCounter;
    }
}

class Module
{
    protected array $destinations = [];

    public function __construct(
        public readonly string $name,
        protected readonly PulseQueue $pulseQueue,
    ) {
    }

    public function addDestination(Module $destination): void
    {
        $this->destinations[] = $destination;

        if ($destination instanceof Conjunction) {
            $destination->addSource($this);
        }
    }

    public function isInDefaultState(): bool
    {
        return true;
    }

    public function receive(?Module $source, Pulse $pulse): void
    {
    }

    protected function send(Pulse $pulse): void
    {
        foreach ($this->destinations as $destination) {
            $this->pulseQueue->add($this, $destination, $pulse);
        }
    }
}

class Broadcaster extends Module
{
    public function receive(?Module $source, Pulse $pulse): void
    {
        $this->send($pulse);
    }
}

class FlipFlop extends Module
{
    private const STATE_DEFAULT = FlipState::Off;

    private FlipState $state = self::STATE_DEFAULT;

    public function isInDefaultState(): bool
    {
        return self::STATE_DEFAULT === $this->state;
    }

    public function receive(?Module $source, Pulse $pulse): void
    {
        if (Pulse::High === $pulse) {
            return;
        }

        if (FlipState::Off === $this->state) {
            $this->state = FlipState::On;
            $pulse = Pulse::High;
        } else {
            $this->state = FlipState::Off;
            $pulse = Pulse::Low;
        }

        $this->send($pulse);
    }
}

class Conjunction extends Module
{
    private array $lastPulses = [];

    public function addSource(Module $source): void
    {
        $this->lastPulses[$source->name] = Pulse::Low;
    }

    public function isInDefaultState(): bool
    {
        return !in_array(Pulse::High, $this->lastPulses, true);
    }

    public function receive(?Module $source, Pulse $pulse): void
    {
        if (null === $source) {
            throw new RuntimeException('Pulse source not supplied!');
        }

        if (!array_key_exists($source->name, $this->lastPulses)) {
            throw new RuntimeException('Pulse source not registered!');
        }

        $this->lastPulses[$source->name] = $pulse;

        foreach ($this->lastPulses as $lastPulse) {
            if (Pulse::Low === $lastPulse) {
                $this->send(Pulse::High);

                return;
            }
        }

        $this->send(Pulse::Low);
    }
}

class ControlPanel extends Module
{
    /** @var Module[] */
    private array $modules;

    public function pushButton(): void
    {
        $this->send(Pulse::Low);
        $this->pulseQueue->process();
    }

    public function registerModule(Module $module): void
    {
        $this->modules[$module->name] = $module;
    }

    public function getLowPulseCounter(): int
    {
        return $this->pulseQueue->getLowPulseCounter();
    }

    public function getHighPulseCounter(): int
    {
        return $this->pulseQueue->getHighPulseCounter();
    }

    public function isSystemInDefaultState(): bool
    {
        return (bool)array_product(
            array_map(
                static fn(Module $module): int => (int)$module->isInDefaultState(),
                $this->modules,
            ),
        );
    }
}
