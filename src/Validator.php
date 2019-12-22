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
 * Cron segment validator.
 *
 * This class checks if a cron segment is valid.
 *
 * @author Jitendra Adhikari <jiten.adhikary@gmail.com>
 */
class Validator
{
    /**
     * Check if the value is in range of given offset.
     *
     * @param int    $value
     * @param string $offset
     *
     * @return bool
     */
    public function inRange(int $value, string $offset): bool
    {
        $parts = \explode('-', $offset);

        return $parts[0] <= $value && $value <= $parts[1];
    }

    /**
     * Check if the value is in step of given offset.
     *
     * @param int    $value
     * @param string $offset
     *
     * @return bool
     */
    public function inStep(int $value, string $offset): bool
    {
        $parts = \explode('/', $offset, 2);

        if (empty($parts[1])) {
            return false;
        }

        if (\strpos($offset, '*/') !== false || \strpos($offset, '0/') !== false) {
            return $value % $parts[1] === 0;
        }

        $parts    = \explode('/', $offset, 2);
        $subparts = \explode('-', $parts[0], 2) + [1 => $value];

        return $this->inStepRange((int) $value, (int) $subparts[0], (int) $subparts[1], (int) $parts[1]);
    }

    /**
     * Check if the value falls between start and end when advanved by step.
     *
     * @param int $value
     * @param int $start
     * @param int $end
     * @param int $step
     *
     * @return bool
     */
    public function inStepRange(int $value, int $start, int $end, int $step): bool
    {
        if (($start + $step) > $end) {
            return false;
        }

        if ($start <= $value && $value <= $end) {
            return \in_array($value, \range($start, $end, $step));
        }

        return false;
    }

    /**
     * Check if month modifiers [L C W #] are satisfied.
     *
     * @internal
     *
     * @param string $value
     * @param array  $time
     *
     * @return bool
     */
    public function isValidMonthDay(string $value, array $time): bool
    {
        if ($value == 'L') {
            return $time[2] == $time[6];
        }

        if ($pos = \strpos($value, 'W')) {
            $value = \substr($value, 0, $pos);
            $month = $this->zeroPad($time[8]);

            return $this->isClosestWeekDay((int) $value, $month, $time);
        }

        $this->unexpectedValue(2, $value);
    }

    protected function isClosestWeekDay(int $value, string $month, array $time): bool
    {
        foreach ([0, -1, 1, -2, 2] as $i) {
            $incr = $value + $i;
            if ($incr < 1 || $incr > $time[6]) {
                continue;
            }

            $incr  = $this->zeroPad($incr);
            $parts = \explode(' ', \date('N m j', \strtotime("{$time[5]}-$month-$incr")));
            if ($parts[0] < 6 && $parts[1] == $month) {
                return $time[2] == $parts[2];
            }
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Check if week modifiers [L C W #] are satisfied.
     *
     * @internal
     *
     * @param string $value
     * @param array  $time
     *
     * @return bool
     */
    public function isValidWeekDay(string $value, array $time): bool
    {
        $month = $this->zeroPad($time[8]);

        if (\strpos($value, 'L')) {
            return $this->isLastWeekDay($value, $month, $time);
        }

        if (!\strpos($value, '#')) {
            $this->unexpectedValue(4, $value);
        }

        list($day, $nth) = \explode('#', \str_replace('0#', '7#', $value));

        if (!$this->isNthWeekDay((int) $day, (int) $nth) || $time[9] != $day) {
            return false;
        }

        return \intval($time[7] / 7) == $nth - 1;
    }

    /**
     * @param int    $pos
     * @param string $value
     *
     * @throws \UnexpectedValueException
     */
    public function unexpectedValue(int $pos, string $value)
    {
        throw new \UnexpectedValueException(
            \sprintf('Invalid offset value at segment #%d: %s', $pos, $value)
        );
    }

    protected function isLastWeekDay(string $value, string $month, array $time): bool
    {
        $value = \explode('L', \str_replace('7L', '0L', $value));
        $decr  = $time[6];

        for ($i = 0; $i < 7; $i++) {
            $decr -= $i;
            if (\date('w', \strtotime("{$time[5]}-$month-$decr")) == $value[0]) {
                return $time[2] == $decr;
            }
        }

        return false;
    }

    protected function isNthWeekDay(int $day, int $nth): bool
    {
        return !($day < 0 || $day > 7 || $nth < 1 || $nth > 5);
    }

    protected function zeroPad($value): string
    {
        return \str_pad((string) $value, 2, '0', \STR_PAD_LEFT);
    }
}
