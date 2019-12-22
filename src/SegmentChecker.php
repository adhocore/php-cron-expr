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
 * Cron Expression segment checker.
 *
 * This class checks if a cron segment satisfies given time.
 *
 * @author Jitendra Adhikari <jiten.adhikary@gmail.com>
 */
class SegmentChecker
{
    /** @var Validator */
    protected $validator;

    public function __construct(Validator $validator = null)
    {
        $this->validator = $validator ?: new Validator;
    }

    /**
     * Checks if a cron segment satisfies given time.
     *
     * @param string $segment
     * @param int    $pos
     * @param array  $times
     *
     * @return bool
     */
    public function checkDue(string $segment, int $pos, array $times): bool
    {
        $offsets = \explode(',', \trim($segment));

        foreach ($offsets as $offset) {
            if ($this->isOffsetDue($offset, $pos, $times)) {
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
     * @param array  $times
     *
     * @return bool
     */
    protected function isOffsetDue(string $offset, int $pos, array $times): bool
    {
        if (\strpos($offset, '/') !== false) {
            return $this->validator->inStep($times[$pos], $offset);
        }

        if (\strpos($offset, '-') !== false) {
            return $this->validator->inRange($times[$pos], $offset);
        }

        if (\is_numeric($offset)) {
            return $times[$pos] == $offset;
        }

        return $this->checkModifier($offset, $pos, $times);
    }

    protected function checkModifier(string $offset, int $pos, array $times): bool
    {
        $isModifier = \strpbrk($offset, 'LCW#');

        if ($pos === 2 && $isModifier) {
            return $this->validator->isValidMonthDay($offset, $times);
        }

        if ($pos === 4 && $isModifier) {
            return $this->validator->isValidWeekDay($offset, $times);
        }

        $this->validator->unexpectedValue($pos, $offset);
    }
}
