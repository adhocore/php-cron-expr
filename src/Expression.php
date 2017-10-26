<?php

namespace Ahc\Cron;

/**
 * Cron Expression Parser.
 *
 * This class checks if a cron expression is due to run on given timestamp (or default now).
 * Acknowledgement: The initial idea came from {@link http://stackoverflow.com/a/5727346}.
 *
 * @author Jitendra Adhikari <jiten.adhikary@gmail.com>
 */
class Expression
{
    protected static $expressions = [
        '@yearly'    => '0 0 1 1 *',
        '@annually'  => '0 0 1 1 *',
        '@monthly'   => '0 0 1 * *',
        '@weekly'    => '0 0 * * 0',
        '@daily'     => '0 0 * * *',
        '@hourly'    => '0 * * * *',
        '@always'    => '* * * * *',
        '@5minutes'  => '*/5 * * * *',
        '@10minutes' => '*/10 * * * *',
        '@15minutes' => '*/15 * * * *',
        '@30minutes' => '0,30 * * * *',
    ];

    protected static $literals = [
        'sun' => 0,
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6,
        'jan' => 1,
        'feb' => 2,
        'mar' => 3,
        'apr' => 4,
        'may' => 5,
        'jun' => 6,
        'jul' => 7,
        'aug' => 8,
        'sep' => 9,
        'oct' => 10,
        'nov' => 11,
        'dec' => 12,
    ];

    /**
     * Parse cron expression to decide if it can be run on given time (or default now).
     *
     * @param string $expr The cron expression.
     * @param int    $time The timestamp to validate the cron expr against. Defaults to now.
     *
     * @return bool
     */
    public static function isDue($expr, $time = null)
    {
        list($expr, $time) = static::process($expr, $time);

        foreach ($expr as $pos => $value) {
            if ($value === '*' || $value === '?') {
                continue;
            }

            $isDue = true;
            $value = explode(',', trim($value));

            foreach ($value as $offset) {
                $isDue = static::isOffsetDue($offset, $pos, $time);

                if (null === $isDue) {
                    throw new \UnexpectedValueException(
                        sprintf('Invalid offset value %s for segment #%d', $offset, $pos)
                    );
                }

                if ($isDue) {
                    break;
                }
            }

            if (!$isDue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Process and prepare input.
     *
     * @param string $expr
     * @param mixed  $time
     *
     * @return array
     */
    protected static function process($expr, $time)
    {
        if (isset(static::$expressions[$expr])) {
            $expr = static::$expressions[$expr];
        }

        $expr = str_ireplace(array_keys(static::$literals), array_values(static::$literals), $expr);
        $expr = explode(' ', $expr);

        if (count($expr) < 5 || count($expr) > 6) {
            throw new \UnexpectedValueException(
                'Cron $expr should have 5 or 6 segments delimited by space'
            );
        }

        if (empty($time)) {
            $time = time();
        } elseif (is_string($time)) {
            $time = strtotime($time);
        } elseif ($time instanceof \DateTime) {
            $time = $time->getTimestamp();
        }

        $time = array_map('intval', explode(' ', date('i G j n w Y t d m N', $time)));

        return [$expr, $time];
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
        if (strpos($offset, '*/') !== false || strpos($offset, '0/') !== false) {
            $parts = explode('/', $offset, 2);

            return $time[$pos] % $parts[1] === 0;
        }

        if (strpos($offset, '/') !== false) {
            $parts    = explode('/', $offset, 2);
            $subparts = explode('-', $parts[0], 2) + [1 => $time[$pos]];

            return ($subparts[0] <= $time[$pos] && $time[$pos] <= $subparts[1] && $parts[1])
                ? in_array($time[$pos], range($subparts[0], $subparts[1], $parts[1]))
                : false;
        }

        if (strpos($offset, '-') !== false) {
            $parts = explode('-', $offset);

            return ($parts[0] <= $time[$pos] && $time[$pos] <= $parts[1]);
        }

        if (is_numeric($offset)) {
            return $time[$pos] == $offset;
        }

        if (($pos === 2 || $pos === 4) && strpbrk($offset, 'LCW#')) {
            return $pos === 4
                ? static::checkWeekDay($offset, $time)
                : static::checkMonthDay($offset, $time);
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
    protected static function checkMonthDay($value, $time)
    {
        $month = $time[8] < 10 ? '0' . $time[8] : $time[8];

        if ($value == 'L') {
            return $time[2] == $time[6];
        }

        if ($pos = strpos($value, 'W')) {
            $value = substr($value, 0, $pos);

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

        if ($pos = strpos($value, 'L')) {
            $value = explode('L', str_replace('7L', '0L', $value));
            $decr = $time[6];
            for ($i = 0; $i < 7; $i++) {
                $decr -= $i;
                if (date('w', strtotime("$time[5]-$month-$decr")) == $value[0]) {
                    return $time[2] == $decr;
                }
            }

            return false;
        }

        if (strpos($value, '#')) {
            $value = explode('#', str_replace('0#', '7#', $value));

            if ($value[0] < 0 || $value[0] > 7 || $value[1] < 1 || $value[1] > 5 || $time[9] != $value[0]) {
                return false;
            }

            return intval($time[7] / 7) == $value[1] - 1;
        }
    }

    /**
     * Instance call.
     *
     * Parse cron expression to decide if it can be run on given time (or default now).
     *
     * @param string $expr The cron expression.
     * @param int    $time The timestamp to validate the cron expr against. Defaults to now.
     *
     * @return bool
     */
    public function isCronDue($expr, $time = null)
    {
        return static::isDue($expr, $time);
    }
}
