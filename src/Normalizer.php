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

class Normalizer
{
    const YEARLY      = '@yearly';
    const ANNUALLY    = '@annually';
    const MONTHLY     = '@monthly';
    const WEEKLY      = '@weekly';
    const DAILY       = '@daily';
    const HOURLY      = '@hourly';
    const ALWAYS      = '@always';
    const FIVE_MIN    = '@5minutes';
    const TEN_MIN     = '@10minutes';
    const FIFTEEN_MIN = '@15minutes';
    const THIRTY_MIN  = '@30minutes';

    protected static $expressions = [
        self::YEARLY      => '0 0 1 1 *',
        self::ANNUALLY    => '0 0 1 1 *',
        self::MONTHLY     => '0 0 1 * *',
        self::WEEKLY      => '0 0 * * 0',
        self::DAILY       => '0 0 * * *',
        self::HOURLY      => '0 * * * *',
        self::ALWAYS      => '* * * * *',
        self::FIVE_MIN    => '*/5 * * * *',
        self::TEN_MIN     => '*/10 * * * *',
        self::FIFTEEN_MIN => '*/15 * * * *',
        self::THIRTY_MIN  => '0,30 * * * *',
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

    public function normalizeExpr(string $expr): string
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
