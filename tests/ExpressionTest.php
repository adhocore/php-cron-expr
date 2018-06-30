<?php

namespace Ahc\Cron\Test;

use Ahc\Cron\Expression;

class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider scheduleProvider
     */
    public function test_isDue($expr, $time, $foo, $expected, $throwsAt = false)
    {
        if ($throwsAt) {
            $this->setExpectedException(
                \UnexpectedValueException::class,
                "Invalid offset value at segment #$throwsAt"
            );
        }

        $actual = Expression::isDue($expr, $time);

        $this->assertSame($expected, $actual, 'The expression ' . $expr . ' has failed');
    }

    public function test_isCronDue()
    {
        $expr = new Expression;

        $this->assertTrue(is_bool($expr->isCronDue('*/1 * * * *', time())));
    }

    public function test_isDue_throws_if_expr_invalid()
    {
        $this->setExpectedException(\UnexpectedValueException::class);

        Expression::isDue('@invalid');
    }

    public function test_isDue_throws_if_modifier_invalid()
    {
        $this->setExpectedException(\UnexpectedValueException::class);

        Expression::isDue('* * 2L * *');
    }

    /**
     * The test cases are taken from awesome mtdowling/cron-expression package. Thank you.
     *
     * @link https://github.com/mtdowling/cron-expression/
     *
     * Data provider for cron schedule
     *
     * @return array
     */
    public function scheduleProvider()
    {
        return [
            ['@always', time(), '', true],
            ['@5minutes', new \DateTime('2017-05-10 02:30:00'), '', true],
            ['* * 7W * *', '2017-10-15 20:00:00', '', false],

            ['*/2 */2 * * *', '2015-08-10 21:47:27', '2015-08-10 22:00:00', false],
            ['* * * * *', '2015-08-10 21:50:37', '2015-08-10 21:50:00', true],
            ['* 20,21,22 * * *', '2015-08-10 21:50:00', '2015-08-10 21:50:00', true],
            // Handles CSV values
            ['* 20,22 * * *', '2015-08-10 21:50:00', '2015-08-10 22:00:00', false],
            // CSV values can be complex
            ['* 5,21-22 * * *', '2015-08-10 21:50:00', '2015-08-10 21:50:00', true],
            ['7-9 * */9 * *', '2015-08-10 22:02:33', '2015-08-18 00:07:00', false],
            // 15th minute, of the second hour, every 15 days, in January, every Friday
            ['1 * * * 7', '2015-08-10 21:47:27', '2015-08-16 00:01:00', false],
            // Test with exact times
            ['47 21 * * *', strtotime('2015-08-10 21:47:30'), '2015-08-10 21:47:00', true],
            // Test Day of the week (issue #1)
            // According cron implementation, 0|7 = sunday, 1 => monday, etc
            ['* * * * 0', strtotime('2011-06-15 23:09:00'), '2011-06-19 00:00:00', false],
            ['* * * * 7', strtotime('2011-06-15 23:09:00'), '2011-06-19 00:00:00', false],
            ['* * * * 1', strtotime('2011-06-15 23:09:00'), '2011-06-20 00:00:00', false],
            // Should return the sunday date as 7 equals 0
            ['0 0 * * MON,SUN', strtotime('2011-06-15 23:09:00'), '2011-06-19 00:00:00', false],
            ['0 0 * * 1,7', strtotime('2011-06-15 23:09:00'), '2011-06-19 00:00:00', false],
            ['0 0 * * 0-4', strtotime('2011-06-15 23:09:00'), '2011-06-16 00:00:00', false],
            ['0 0 * * 7-4', strtotime('2011-06-15 23:09:00'), '2011-06-16 00:00:00', false],
            ['0 0 * * 4-7', strtotime('2011-06-15 23:09:00'), '2011-06-16 00:00:00', false],
            ['0 0 * * 7-3', strtotime('2011-06-15 23:09:00'), '2011-06-19 00:00:00', false],
            ['0 0 * * 3-7', strtotime('2011-06-15 23:09:00'), '2011-06-16 00:00:00', false],
            ['0 0 * * 3-7', strtotime('2011-06-18 23:09:00'), '2011-06-19 00:00:00', false],
            // Test lists of values and ranges (Abhoryo)
            ['0 0 * * 2-7', strtotime('2011-06-20 23:09:00'), '2011-06-21 00:00:00', false],
            ['0 0 * * 0,2-6', strtotime('2011-06-20 23:09:00'), '2011-06-21 00:00:00', false],
            ['0 0 * * 2-7', strtotime('2011-06-18 23:09:00'), '2011-06-19 00:00:00', false],
            ['0 0 * * 4-7', strtotime('2011-07-19 00:00:00'), '2011-07-21 00:00:00', false],
            // Test increments of ranges
            ['0-12/4 * * * *', strtotime('2011-06-20 12:04:00'), '2011-06-20 12:04:00', true],
            ['4-59/2 * * * *', strtotime('2011-06-20 12:04:00'), '2011-06-20 12:04:00', true],
            ['4-59/2 * * * *', strtotime('2011-06-20 12:06:00'), '2011-06-20 12:06:00', true],
            ['4-59/3 * * * *', strtotime('2011-06-20 12:06:00'), '2011-06-20 12:07:00', false],
            ['0 0 * * 0,2-6', strtotime('2011-06-20 23:09:00'), '2011-06-21 00:00:00', false],
            // Test Day of the Week and the Day of the Month (issue #1)
            ['0 0 1 1 0', strtotime('2011-06-15 23:09:00'), '2012-01-01 00:00:00', false],
            ['0 0 1 JAN 0', strtotime('2011-06-15 23:09:00'), '2012-01-01 00:00:00', false],
            ['0 0 1 * 0', strtotime('2011-06-15 23:09:00'), '2012-01-01 00:00:00', false],
            ['0 0 L * *', strtotime('2011-07-15 00:00:00'), '2011-07-31 00:00:00', false],
            // Test the W day of the week modifier for day of the month field
            ['0 0 2W * *', strtotime('2011-07-01 00:00:00'), '2011-07-01 00:00:00', true],
            ['0 0 1W * *', strtotime('2011-05-01 00:00:00'), '2011-05-02 00:00:00', false],
            ['0 0 1W * *', strtotime('2011-07-01 00:00:00'), '2011-07-01 00:00:00', true],
            ['0 0 3W * *', strtotime('2011-07-01 00:00:00'), '2011-07-04 00:00:00', false],
            ['0 0 16W * *', strtotime('2011-07-01 00:00:00'), '2011-07-15 00:00:00', false],
            ['0 0 28W * *', strtotime('2011-07-01 00:00:00'), '2011-07-28 00:00:00', false],
            ['0 0 30W * *', strtotime('2011-07-01 00:00:00'), '2011-07-29 00:00:00', false],
            ['0 0 31W * *', strtotime('2011-07-01 00:00:00'), '2011-07-29 00:00:00', false],
            // Test the year field
            ['* * * * * 2012', strtotime('2011-05-01 00:00:00'), '2012-01-01 00:00:00', false],
            // Test the last weekday of a month
            ['* * * * 5L', strtotime('2011-07-01 00:00:00'), '2011-07-29 00:00:00', false],
            ['* * * * 6L', strtotime('2011-07-01 00:00:00'), '2011-07-30 00:00:00', false],
            ['* * * * 7L', strtotime('2011-07-01 00:00:00'), '2011-07-31 00:00:00', false],
            ['* * * * 1L', strtotime('2011-07-24 00:00:00'), '2011-07-25 00:00:00', false],
            ['* * * * TUEL', strtotime('2011-07-24 00:00:00'), '2011-07-26 00:00:00', false],
            ['* * * 1 5L', strtotime('2011-12-25 00:00:00'), '2012-01-27 00:00:00', false],
            // Test the hash symbol for the nth weekday of a given month
            ['* * * * 5#2', strtotime('2011-07-01 00:00:00'), '2011-07-08 00:00:00', false],
            ['* * * * 5#1', strtotime('2011-07-01 00:00:00'), '2011-07-01 00:00:00', true],
            ['* * * * 3#4', strtotime('2011-07-01 00:00:00'), '2011-07-27 00:00:00', false],
            ['* * * * 4W', strtotime('2011-07-01 00:00:00'), '2011-07-27 00:00:00', false, 4], // seg 4
            ['* * * 1L *', strtotime('2011-07-01 00:00:00'), '2011-07-27 00:00:00', false, 3], // seg 3
        ];
    }
}
