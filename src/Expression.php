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
     * @param  string $expr  The cron expression.
     * @param  int    $time  The timestamp to validate the cron expr against. Defaults to now.
     *
     * @return bool
     */
    public static function isDue($expr, $time = null)
    {
        if (isset(static::$expressions[$expr])) {
            $expr = static::$expressions[$expr];
        }

        if ('' === trim($expr, '?* ')) {
            return true;
        }

        if (empty($time)) {
            $time = time();
        } elseif (is_string($time)) {
            $time = strtotime($time);
        } elseif ($time instanceof \DateTime) {
            $time = $time->getTimestamp();
        }

        $expr = explode(' ', str_ireplace(array_keys(static::$literals), array_values(static::$literals), $expr));
        if (count($expr) < 5 || count($expr) > 6) {
            throw new \UnexpectedValueException('Cron $expr should have 5 or 6 segments delimited by space');
        }

        $time = array_map('intval', explode(' ', date('i G j n w Y t d m N', $time)));

        foreach ($expr as $pos => $value) {
            if ($value === '*' || $value === '?') {
                continue;
            }

            $isDue = true;
            $value = explode(',', trim($value));

            foreach ($value as $offset) {
                if (strpos($offset, '*/') !== false || strpos($offset, '0/') !== false) {
                    $parts = explode('/', $offset, 2);
                    $isDue = $time[$pos] % $parts[1] === 0;
                } elseif (strpos($offset, '/') !== false) {
                    $parts    = explode('/', $offset, 2);
                    $subparts = explode('-', $parts[0], 2) + [1 => $time[$pos]];
                    $isDue    = $subparts[0] <= $time[$pos] && $time[$pos] <= $subparts[1] && $parts[1]
                        ? in_array($time[$pos], range($subparts[0], $subparts[1], $parts[1]))
                        : false;
                } elseif (strpos($offset, '-') !== false) {
                    $parts = explode('-', $offset);
                    $isDue = $parts[0] <= $time[$pos] && $time[$pos] <= $parts[1];
                } elseif (is_numeric($offset)) {
                    $isDue = $time[$pos] == $offset;
                } elseif (strpbrk($offset, 'LCW#')) {
                    $isDue = static::checkModifier($offset, $pos, $time);
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
     * Check if modifiers [L C W #] are satisfied.
     * 
     * @internal
     *
     * @param  string $value
     * @param  int    $pos
     * @param  int    $time
     *
     * @return bool
     */
    protected static function checkModifier($value, $pos, $time)
    {
        $month = $time[8] < 10 ? '0' . $time[8] : $time[8];

        // Day of month.
        if ($pos === 2) {
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

        // Day of week.
        if ($pos === 4) {
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

        throw new \UnexpectedValueException(sprintf('Invalid modifier value %s for segment #%d', $value, $pos));
    }
}
