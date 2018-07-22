## adhocore/cron-expr

[![Latest Version](https://img.shields.io/github/release/adhocore/php-cron-expr.svg?style=flat-square)](https://github.com/adhocore/php-cron-expr/releases)
[![Travis Build](https://img.shields.io/travis/adhocore/php-cron-expr/master.svg?style=flat-square)](https://travis-ci.org/adhocore/php-cron-expr?branch=master)
[![Scrutinizer CI](https://img.shields.io/scrutinizer/g/adhocore/php-cron-expr.svg?style=flat-square)](https://scrutinizer-ci.com/g/adhocore/php-cron-expr/?branch=master)
[![Codecov branch](https://img.shields.io/codecov/c/github/adhocore/php-cron-expr/master.svg?style=flat-square)](https://codecov.io/gh/adhocore/php-cron-expr)
[![StyleCI](https://styleci.io/repos/94228363/shield)](https://styleci.io/repos/94228363)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)


- Lightweight Cron expression parser library for PHP.
- Very fast because it bails early in case a segment doesnt match.

## Installation
```bash
composer require adhocore/cron-expr
```

## Usage
```php
use Ahc\Cron\Expression;

Expression::isDue('@always');
Expression::isDue('@hourly', '2015-01-01 00:00:00');
Expression::isDue('*/20 * * * *', new DateTime);
Expression::isDue('5-34/4 * * * *', time());

// Dont like static calls? Below is possible too!
$expr = new Expression;
$expr->isCronDue('*/1 * * * *', time());
```

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

### Modifiers
Following modifiers supported

- *Day of Month / 3rd segment:*
	- `L` stands for last day of month (eg: `L` could mean 29th for February in leap year)
	- `W` stands for closest week day (eg: `10W` is closest week days (MON-FRI) to 10th date)
- *Day of Week / 5th segment:*
	- `L` stands for last weekday of month (eg: `2L` is last monday)
	- `#` stands for nth day of week in the month (eg: `1#2` is second sunday)
