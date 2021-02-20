## adhocore/cron-expr

[![Latest Version](https://img.shields.io/github/release/adhocore/php-cron-expr.svg?style=flat-square)](https://github.com/adhocore/php-cron-expr/releases)
[![Travis Build](https://img.shields.io/travis/adhocore/php-cron-expr/master.svg?style=flat-square)](https://travis-ci.org/adhocore/php-cron-expr?branch=master)
[![Scrutinizer CI](https://img.shields.io/scrutinizer/g/adhocore/php-cron-expr.svg?style=flat-square)](https://scrutinizer-ci.com/g/adhocore/php-cron-expr/?branch=master)
[![Codecov branch](https://img.shields.io/codecov/c/github/adhocore/php-cron-expr/master.svg?style=flat-square)](https://codecov.io/gh/adhocore/php-cron-expr)
[![StyleCI](https://styleci.io/repos/94228363/shield)](https://styleci.io/repos/94228363)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)


- Lightweight Cron expression parser library for PHP.
- Zero dependency.
- Very **fast** because it bails early in case a segment doesnt match.
- Real [benchmark](https://github.com/adhocore/php-cron-bench) shows it is about 7.54x to 12.92x faster than `dragonmantank/cron-expression`

## Installation

```bash
composer require adhocore/cron-expr

# PHP5.6 or lower
composer require adhocore/cron-expr:0.1.0
```

## Usage

**Basic**

```php
use Ahc\Cron\Expression;
use Ahc\Cron\Normalizer;

Expression::isDue('@always');
Expression::isDue(Normalizer::HOURLY, '2015-01-01 00:00:00');
Expression::isDue('*/20 * * * *', new DateTime);
Expression::isDue('5-34/4 * * * *', time());

// Dont like static calls? Below is possible too!
$expr = new Expression;
$expr->isCronDue('*/1 * * * *', time());
```

**Bulk checks**

When checking for several jobs at once, if more than one of the jobs share equivalent expression
then the evaluation is done only once per go thus greatly improving performnce.

```php
use Ahc\Cron\Expression;

$jobs = [
    'job1' => '*/2 */2 * * *',
    'job1' => '* 20,21,22 * * *',
    'job3' => '7-9 * */9 * *',
    'job4' => '*/5 * * * *',
    'job5' => '@5minutes',     // equivalent to job4 (so it is due if job4 is due)
    'job6' => '7-9 * */9 * *', // exact same as job3 (so it is due if job3 is due)
];

// The second param $time can be used same as above: null/time()/date string/DateTime
$dues = Expression::getDues($jobs, '2015-08-10 21:50:00');
// ['job1', 'job4', 'job5']

// Dont like static calls? Below is possible too!
$expr = new Expression;
$dues = $expr->filter($jobs, time());
```

### Cron Expression

Cron expression normally consists of 5 segments viz:
```
<minute> <hour> <day> <month> <weekday>
```
and sometimes there can be 6th segment for `<year>` at the end.

### Real Abbreviations

You can use real abbreviations for month and week days. eg: `JAN`, `dec`, `fri`, `SUN`

### Tags

Following tags are available and they are converted to real cron expressions before parsing:

- *@yearly* or *@annually* - every year
- *@monthly* - every month
- *@daily* - every day
- *@weekly* - every week
- *@hourly* - every hour
- *@5minutes* - every 5 minutes
- *@10minutes* - every 10 minutes
- *@15minutes* - every 15 minutes
- *@30minutes* - every 30 minutes
- *@always* - every minute

> You can refer them with constants from `Ahc\Cron\Normalizer` like `Ahc\Cron\Normalizer::WEEKLY`

### Modifiers

Following modifiers supported

- *Day of Month / 3rd segment:*
    - `L` stands for last day of month (eg: `L` could mean 29th for February in leap year)
    - `W` stands for closest week day (eg: `10W` is closest week days (MON-FRI) to 10th date)
- *Day of Week / 5th segment:*
    - `L` stands for last weekday of month (eg: `2L` is last monday)
    - `#` stands for nth day of week in the month (eg: `1#2` is second sunday)

## LICENSE

> &copy; [MIT](./LICENSE) | 2017-2019, Jitendra Adhikari

## Credits

This project is release managed by [please](https://github.com/adhocore/please).
