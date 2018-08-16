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
    public function checkDue($segment, $pos, $times)
    {
        $isDue   = true;
        $offsets = \explode(',', \trim($segment));

        foreach ($offsets as $offset) {
            if (null === $isDue = $this->isOffsetDue($offset, $pos, $times)) {
                throw new \UnexpectedValueException(
                    sprintf('Invalid offset value at segment #%d: %s', $pos, $offset)
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
     * @param array  $times
     *
     * @return bool|null
     */
    protected function isOffsetDue($offset, $pos, $times)
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

    protected function checkModifier($offset, $pos, $times)
    {
        $isModifier = \strpbrk($offset, 'LCW#');

        if ($pos === 2 && $isModifier) {
            return $this->validator->isValidMonthDay($offset, $times);
        }

        if ($pos === 4 && $isModifier) {
            return $this->validator->isValidWeekDay($offset, $times);
        }

        return null;
    }
}
