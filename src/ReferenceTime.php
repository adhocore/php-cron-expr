<?php

declare(strict_types=1);

/*
 * This file is part of the PHP-CRON-EXPR package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Cron;

/**
 * @method int minute()
 * @method int hour()
 * @method int monthDay()
 * @method int month()
 * @method int weekDay()  0 based day of week.
 * @method int year()
 * @method int day()
 * @method int weekDay1() 1 based day of week.
 * @method int numDays()  Number of days in the month.
 */
class ReferenceTime
{
    // The cron parts. (Donot change it)
    const MINUTE   = 0;
    const HOUR     = 1;
    const MONTHDAY = 2;
    const MONTH    = 3;
    const WEEKDAY  = 4;
    const YEAR     = 5;

    // Meta data parts.
    const DAY      = 6;
    const WEEKDAY1 = 7;
    const NUMDAYS  = 8;

    /** @var array The data */
    protected $values = [];

    /** @var array The Magic methods */
    protected $methods = [];

    public function __construct($time)
    {
        $timestamp = $this->normalizeTime($time);

        $this->values  = $this->parse($timestamp);
        $this->methods = (new \ReflectionClass($this))->getConstants();
    }

    public function __call(string $method, array $args): int
    {
        $method = \preg_replace('/^GET/', '', \strtoupper($method));
        if (isset($this->methods[$method])) {
            return $this->values[$this->methods[$method]];
        }

        // @codeCoverageIgnoreStart
        throw new \BadMethodCallException("Method '$method' doesnot exist in ReferenceTime.");
        // @codeCoverageIgnoreEnd
    }

    public function get(int $segment): int
    {
        return $this->values[$segment];
    }

    public function isAt($value, int $segment): bool
    {
        return $this->values[$segment] == $value;
    }

    protected function normalizeTime($time): int
    {
        if (empty($time)) {
            $time = \time();
        } elseif (\is_string($time)) {
            $time = \strtotime($time);
        } elseif ($time instanceof \DateTime) {
            $time = $time->getTimestamp();
        }

        return $time;
    }

    protected function parse(int $timestamp): array
    {
        $parts = \date('i G j n w Y d N t', $timestamp);
        $parts = \explode(' ', $parts);
        $parts = \array_map('intval', $parts);

        return $parts;
    }
}
