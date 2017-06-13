## adhcore/cron-expr [![build status](https://travis-ci.org/adhocore/cron-expr.svg?branch=master)](https://travis-ci.org/adhocore/cron-expr)

- Lightweight Cron expression parser library for PHP.

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
- *@5minuts* - every 5 minutes
- *@10minuts* - every 10 minutes
- *@15minuts* - every 15 minutes
- *@30minuts* - every 30 minutes
- *@always* - every minute

### Modifiers
Following modifiers supported

- *Day of Month / 3rd segment:*
	- `L` stands for last day of month (eg: `L` could mean 29th for February in leap year)
	- `W` stands for closest week day (eg: `10W` is closest week days (MON-FRI) to 10th date)
- *Day of Week / 5th segment:*
	- `L` stands for last weekday of month (eg: `2L` is last monday)
	- `#` stands for nth day of week in the month (eg: `1#2` is second sunday)
