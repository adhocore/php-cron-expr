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

    /** @var Normalizer */
    protected $normalizer;

    public function __construct(SegmentChecker $checker = null, Normalizer $normalizer = null)
    {
        $this->checker    = $checker ?: new SegmentChecker;
        $this->normalizer = $normalizer ?: new Normalizer;

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

        foreach (\explode(' ', $this->normalizer->normalizeExpr($expr)) as $pos => $segment) {
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
            $expr = $this->normalizer->normalizeExpr($expr);

            if (!isset($cache[$expr])) {
                $cache[$expr] = $this->isCronDue($expr, $time);
            }

            if ($cache[$expr]) {
                $dues[] = $name;
            }
        }

        return $dues;
    }
}
