<?php

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

    public function __construct()
    {
        $this->validator = new Validator;
    }

    /**
     * Checks if a cron segment satisfies given time.
     *
     * @param string $segment
     * @param int    $pos
     * @param int    $time
     *
     * @return bool
     */
    public function checkDue($segment, $pos, $time)
    {
        $isDue   = true;
        $offsets = \explode(',', \trim($segment));

        foreach ($offsets as $offset) {
            if (null === $isDue = $this->isOffsetDue($offset, $pos, $time)) {
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
     * @param array  $time
     *
     * @return bool|null
     */
    protected function isOffsetDue($offset, $pos, $time)
    {
        if (\strpos($offset, '/') !== false) {
            return $this->validator->inStep($time[$pos], $offset);
        }

        if (\strpos($offset, '-') !== false) {
            return $this->validator->inRange($time[$pos], $offset);
        }

        if (\is_numeric($offset)) {
            return $time[$pos] == $offset;
        }

        return $this->checkModifier($offset, $pos, $time);
    }

    protected function checkModifier($offset, $pos, $time)
    {
        $isModifier = \strpbrk($offset, 'LCW#');

        if ($pos === 2 && $isModifier) {
            return $this->validator->isValidMonthDay($offset, $time);
        }

        if ($pos === 4 && $isModifier) {
            return $this->validator->isValidWeekDay($offset, $time);
        }

        return null;
    }
}
