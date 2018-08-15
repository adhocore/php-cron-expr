<?php

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
    public function inRange($value, $offset)
    {
        $parts = \explode('-', $offset);

        return $parts[0] <= $value && $value <= $parts[1];
    }

    public function inStep($value, $offset)
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

        return $this->inStepRange($value, $subparts[0], $subparts[1], $parts[1]);
    }

    public function inStepRange($value, $start, $end, $step)
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
     * @return bool|null
     */
    public function isValidMonthDay($value, $time)
    {
        if ($value == 'L') {
            return $time[2] == $time[6];
        }

        if ($pos = \strpos($value, 'W')) {
            $value = \substr($value, 0, $pos);
            $month = \str_pad($time[8], 2, '0', \STR_PAD_LEFT);

            return $this->isClosestWeekDay($value, $month, $time);
        }
    }

    protected function isClosestWeekDay($value, $month, $time)
    {
        foreach ([0, -1, 1, -2, 2] as $i) {
            $incr = $value + $i;
            if ($incr < 1 || $incr > $time[6]) {
                continue;
            }

            $incr  = \str_pad($incr, 2, '0', \STR_PAD_LEFT);
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
     * @return bool|null
     */
    public function isValidWeekDay($value, $time)
    {
        $month = \str_pad($time[8], 2, '0', \STR_PAD_LEFT);

        if (\strpos($value, 'L')) {
            return $this->isLastWeekDay($value, $month, $time);
        }

        if (!\strpos($value, '#')) {
            return null;
        }

        list($day, $nth) = \explode('#', \str_replace('0#', '7#', $value));

        if (!$this->isNthWeekDay($day, $nth) || $time[9] != $day) {
            return false;
        }

        return \intval($time[7] / 7) == $nth - 1;
    }

    protected function isLastWeekDay($value, $month, $time)
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

    protected function isNthWeekDay($day, $nth)
    {
        return !($day < 0 || $day > 7 || $nth < 1 || $nth > 5);
    }
}
