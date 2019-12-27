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
 * Cron Expression Parser.
 *
 * This class checks if a cron expression is due to run on given timestamp (or default now).
 * Acknowledgement: The initial idea came from {@link http://stackoverflow.com/a/5727346}.
 *
 * @author Jitendra Adhikari <jiten.adhikary@gmail.com>
 */
class Expression
{
    /** @var Expression */
    protected static $instance;

    /** @var SegmentChecker */
    protected $checker;

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

    public function __construct(SegmentChecker $checker = null)
    {
        $this->checker = $checker ?: new SegmentChecker;

        if (null === static::$instance) {
            static::$instance = $this;
        }
    }

    public static function instance(): self
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Parse cron expression to decide if it can be run on given time (or default now).
     *
     * @param string $expr The cron expression.
     * @param mixed  $time The timestamp to validate the cron expr against. Defaults to now.
     *
     * @return bool
     */
    public static function isDue(string $expr, $time = null): bool
    {
        return static::instance()->isCronDue($expr, $time);
    }

    /**
     * Filter only the jobs that are due.
     *
     * @param array $jobs Jobs with cron exprs. [job1 => cron-expr1, job2 => cron-expr2, ...]
     * @param mixed $time The timestamp to validate the cron expr against. Defaults to now.
     *
     * @return array Due job names: [job1name, ...];
     */
    public static function getDues(array $jobs, $time = null): array
    {
        return static::instance()->filter($jobs, $time);
    }

    /**
     * Instance call.
     *
     * Parse cron expression to decide if it can be run on given time (or default now).
     *
     * @param string $expr The cron expression.
     * @param mixed  $time The timestamp to validate the cron expr against. Defaults to now.
     *
     * @return bool
     */
    public function isCronDue(string $expr, $time = null): bool
    {
        $this->checker->setReference(new ReferenceTime($time));

        foreach (\explode(' ', $this->normalizeExpr($expr)) as $pos => $segment) {
            if ($segment === '*' || $segment === '?') {
                continue;
            }

            if (!$this->checker->checkDue($segment, $pos)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Filter only the jobs that are due.
     *
     * @param array $jobs Jobs with cron exprs. [job1 => cron-expr1, job2 => cron-expr2, ...]
     * @param mixed $time The timestamp to validate the cron expr against. Defaults to now.
     *
     * @return array Due job names: [job1name, ...];
     */
    public function filter(array $jobs, $time = null): array
    {
        $dues = $cache = [];

        foreach ($jobs as $name => $expr) {
            $expr = $this->normalizeExpr($expr);

            if (!isset($cache[$expr])) {
                $cache[$expr] = $this->isCronDue($expr, $time);
            }

            if ($cache[$expr]) {
                $dues[] = $name;
            }
        }

        return $dues;
    }

    protected function normalizeExpr(string $expr): string
    {
        $expr = \trim($expr);

        if (isset(static::$expressions[$expr])) {
            return static::$expressions[$expr];
        }

        $expr  = \preg_replace('~\s+~', ' ', $expr);
        $count = \substr_count($expr, ' ');

        if ($count < 4 || $count > 5) {
            throw new \UnexpectedValueException(
                'Cron $expr should have 5 or 6 segments delimited by space'
            );
        }

        return \str_ireplace(\array_keys(static::$literals), \array_values(static::$literals), $expr);
    }
}
