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
    /** @var ReferenceTime */
    protected $reference;

    /** @var Validator */
    protected $validator;

    public function __construct(Validator $validator = null)
    {
        $this->validator = $validator ?: new Validator;
    }

    public function setReference(ReferenceTime $reference)
    {
        $this->reference = $reference;
    }

    /**
     * Checks if a cron segment satisfies given time.
     *
     * @param string $segment
     * @param int    $pos
     *
     * @return bool
     */
    public function checkDue(string $segment, int $pos): bool
    {
        $offsets = \explode(',', \trim($segment));

        foreach ($offsets as $offset) {
            if ($this->isOffsetDue($offset, $pos)) {
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
     *
     * @return bool
     */
    protected function isOffsetDue(string $offset, int $pos): bool
    {
        if (\strpos($offset, '/') !== false) {
            return $this->validator->inStep($this->reference->get($pos), $offset);
        }

        if (\strpos($offset, '-') !== false) {
            return $this->validator->inRange($this->reference->get($pos), $offset);
        }

        if (\is_numeric($offset)) {
            return $this->reference->isAt($offset, $pos);
        }

        return $this->checkModifier($offset, $pos);
    }

    protected function checkModifier(string $offset, int $pos): bool
    {
        $isModifier = \strpbrk($offset, 'LCW#');

        if ($pos === ReferenceTime::MONTHDAY && $isModifier) {
            return $this->validator->isValidMonthDay($offset, $this->reference);
        }

        if ($pos === ReferenceTime::WEEKDAY && $isModifier) {
            return $this->validator->isValidWeekDay($offset, $this->reference);
        }

        $this->validator->unexpectedValue($pos, $offset);
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
}
