<?php

namespace Ahc\Cron;

/**
 * Cron Expression Parser.
 *
 * This class checks if a cron segment satisfies given time.
 *
 * @author Jitendra Adhikari <jiten.adhikary@gmail.com>
 */
class SegmentChecker
{
    public static function isDue($segment, $pos, $time)
    {
        $isDue   = true;
        $offsets = explode(',', trim($segment));

        foreach ($offsets as $offset) {
            if (null === $isDue = static::isOffsetDue($offset, $pos, $time)) {
                throw new \UnexpectedValueException(
                    sprintf('Invalid offset value %s for segment #%d', $offset, $pos)
                );
            }

            if ($isDue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a given offset at a position is due with respect to given time.
     *
     * @param string $offset
     * @param int    $pos
     * @param array  $time
     *
     * @return bool|null
     */
    protected static function isOffsetDue($offset, $pos, $time)
    {
        if (strpos($offset, '/') !== false) {
            return static::inStep($time[$pos], $offset);
        }

        if (strpos($offset, '-') !== false) {
            return static::inRange($time[$pos], $offset);
        }

        if (is_numeric($offset)) {
            return $time[$pos] == $offset;
        }

        return static::inModifier($offset, $pos, $time);
    }

    protected static function inModifier($offset, $pos, $time)
    {
        $isModifier = strpbrk($offset, 'LCW#');

        if ($pos === 2 && $isModifier) {
            return static::checkMonthDay($offset, $time);
        }

        if ($pos === 4 && $isModifier) {
            return static::checkWeekDay($offset, $time);
        }
    }

    protected static function inRange($value, $offset)
    {
        $parts = explode('-', $offset);

        return $parts[0] <= $value && $value <= $parts[1];
    }

    protected static function inStep($value, $offset)
    {
        if (strpos($offset, '*/') !== false || strpos($offset, '0/') !== false) {
            $parts = explode('/', $offset, 2);

            return $value % $parts[1] === 0;
        }

        $parts    = explode('/', $offset, 2);
        $subparts = explode('-', $parts[0], 2) + [1 => $value];

        return ($subparts[0] <= $value && $value <= $subparts[1] && $parts[1])
            ? in_array($value, range($subparts[0], $subparts[1], $parts[1]))
            : false;
    }

    /**
     * Check if modifiers [L C W #] are satisfied.
     *
     * @internal
     *
     * @param string $value
     * @param int    $time
     *
     * @return bool|null
     */
    protected static function checkMonthDay($value, $time)
    {
        $month = $time[8] < 10 ? '0' . $time[8] : $time[8];

        if ($value == 'L') {
            return $time[2] == $time[6];
        }

        if ($pos = strpos($value, 'W')) {
            $value = substr($value, 0, $pos);

            return static::isClosestWeekDay($value, $month, $time);
        }
    }

    protected static function isClosestWeekDay($value, $month, $time)
    {
        foreach ([0, -1, 1, -2, 2] as $i) {
            $incr = $value + $i;
            if ($incr > 0 && $incr <= $time[6]) {
                if ($incr < 10) {
                    $incr = '0' . $incr;
                }

                $parts = explode(' ', date('N m j', strtotime("$time[5]-$month-$incr")));
                if ($parts[0] < 6 && $parts[1] == $month) {
                    return $time[2] == $parts[2];
                }
            }
        }
    }

    /**
     * Check if modifiers [L C W #] are satisfied.
     *
     * @internal
     *
     * @param string $value
     * @param int    $time
     *
     * @return bool|null
     */
    protected static function checkWeekDay($value, $time)
    {
        $month = $time[8] < 10 ? '0' . $time[8] : $time[8];

        if (strpos($value, 'L')) {
            return static::lastWeekDay($value, $month, $time);
        }

        if (strpos($value, '#')) {
            $value = explode('#', str_replace('0#', '7#', $value));

            if ($value[0] < 0 || $value[0] > 7 || $value[1] < 1 || $value[1] > 5 || $time[9] != $value[0]) {
                return false;
            }

            return intval($time[7] / 7) == $value[1] - 1;
        }
    }

    protected static function lastWeekDay($value, $month, $time)
    {
        $value = explode('L', str_replace('7L', '0L', $value));
        $decr  = $time[6];

        for ($i = 0; $i < 7; $i++) {
            $decr -= $i;
            if (date('w', strtotime("$time[5]-$month-$decr")) == $value[0]) {
                return $time[2] == $decr;
            }
        }

        return false;
    }
}
