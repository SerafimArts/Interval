<?php
/**
 * This file is part of Interval package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Serafim\Interval;

use Serafim\Interval\Exception\BadRangeException;

/**
 * Class Range
 */
class Range implements \IteratorAggregate
{
    /**
     * @var int|float
     */
    private $from;

    /**
     * @var int|float
     */
    private $to = \INF;

    /**
     * @var int
     */
    private $step;

    /**
     * @var bool
     */
    private $verified = false;

    /**
     * Range constructor.
     * @param float|int $from
     * @param float|int $to
     */
    public function __construct($from, $to = \INF)
    {
        $step = \is_float($from) || (\is_float($to) && $to !== \INF) ? .1 : 1;

        $this->from($from)->to($to)->step = $from < $to ? $step : ~($step >> 1);
    }

    /**
     * @param int|float $value
     * @return Range
     */
    public function to($value): self
    {
        \assert($value === \INF || \is_float($value) || \is_int($value));

        $this->verified = false;
        $this->to = $value;

        return $this;
    }

    /**
     * @param int|float $value
     * @return Range
     */
    public function from($value): self
    {
        \assert((\is_float($value) || \is_int($value)) && $value !== \INF);

        $this->verified = false;
        $this->from = $value;

        return $this;
    }

    /**
     * @param string|float|int $from
     * @param float|int $to
     * @return Range|iterable|int[]|float[]
     * @throws BadRangeException
     */
    public static function new($from, $to = null): self
    {
        switch (true) {
            case \is_string($from) && $to === null:
                return self::fromString($from);
        }

        return new static($from, $to);
    }

    /**
     * @param string $interval
     * @return Range|iterable|int[]|float[]
     * @throws BadRangeException
     */
    public static function fromString(string $interval): self
    {
        \assert(\strlen($interval) > 3);

        \preg_match('/(\d+)0\.(\d+)/', $interval, $matches);

        if (\count($matches) !== 3) {
            throw new BadRangeException('Invalid interval format "' . $interval . '"');
        }

        return new static((float)$matches[1], (float)$matches[2]);
    }

    /**
     * @param int|float $step
     * @return Range
     */
    public function step($step): self
    {
        \assert((\is_float($step) || \is_int($step)) && $step !== 0 && $step !== \INF);

        $this->verified = false;
        $this->step = $step;

        return $this;
    }

    /**
     * @return \Traversable|float[]|int[]
     * @throws BadRangeException
     */
    public function getIterator(): \Traversable
    {
        if ($this->verified === false) {
            $this->verify();
        }

        yield from $this->isIncreasing()
            ? $this->getIncreasingIterator()
            : $this->getDecreasingIterator();
    }

    /**
     * @throws BadRangeException
     */
    private function verify(): void
    {
        $this->verified = true;

        switch (true) {
            case $this->step === \INF:
                throw new BadRangeException('Interval step should not be an infinity');
            case $this->from === \INF:
                throw new BadRangeException('Interval element "from" should not be an infinity');
            case $this->step === 0:
                throw new BadRangeException('Interval step should not be equal with 0');

            case $this->isIncreasing() && $this->isFinite() && $this->from >= $this->to:
                throw new BadRangeException('Interval element "from" should be less than "to"');

            case $this->isDecreasing() && $this->isFinite() && $this->from <= $this->to:
                throw new BadRangeException('Interval element "from" should be greater than "to"');
        }
    }

    /**
     * @return bool
     */
    public function isIncreasing(): bool
    {
        return $this->step > 0;
    }

    /**
     * @return bool
     */
    public function isFinite(): bool
    {
        return $this->to !== \INF;
    }

    /**
     * @return bool
     */
    public function isDecreasing(): bool
    {
        return $this->step < 0;
    }

    /**
     * @return \Traversable|float[]|int[]
     */
    private function getIncreasingIterator(): \Traversable
    {
        $current = $this->from;

        while ($current <= $this->to) {
            yield $current;

            $current += $this->step;
        }
    }

    /**
     * @return \Traversable|float[]|int[]
     */
    private function getDecreasingIterator(): \Traversable
    {
        $current = $this->from;

        while ($current >= $this->to) {
            yield $current;

            $current += $this->step;
        }
    }

}
