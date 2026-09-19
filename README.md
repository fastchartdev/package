# FastChart

FastChart records numeric events and maintains queryable summaries for each event, scope, aggregation, and time period. It is useful for application metrics such as orders, revenue, API usage, or sensor readings.

Each recorded event is summarized as `sum`, `avg`, `count`, `min`, and `max` across daily, weekly, monthly, and yearly periods.

## Requirements

- PHP 8.3 or later
- Laravel 11, 12, or 13

## Installation

Install the package with Composer:

```bash
composer require fastchartdev/package
```

Laravel discovers the package automatically. Publish its configuration and migrations, then run the migrations:

```bash
php artisan vendor:publish --tag="package-config"
php artisan vendor:publish --tag="package-migrations"
php artisan migrate
```

The package stores event records and aggregates in four tables: `events`, `event_records`, `meters`, and `meter_summaries`.

## Configuration

The published `config/fastchart.php` file controls logging, queues, and database connections:

```php
return [
    'debug' => env('FASTCHART_DEBUG', false),

    'queues' => [
        'main' => [
            'connection' => env('FASTCHART_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
            'queue' => env('FASTCHART_QUEUE_NAME', 'event-records'),
            'unique_via_cache_driver' => env('FASTCHART_QUEUE_UNIQUE_VIA_CACHE_DRIVER', 'redis'),
        ],
    ],

    'database' => [
        'main' => [
            'connection' => env('FASTCHART_DB_CONNECTION', env('DB_CONNECTION', 'sqlite')),
        ],
        'event_records' => [
            'connection' => env(
                'FASTCHART_DB_EVENT_RECORDS_CONNECTION',
                config('fastchart.database.main.connection', 'sqlite'),
            ),
        ],
    ],
];
```

By default, all package tables use the application's configured database connection. Set `FASTCHART_DB_EVENT_RECORDS_CONNECTION` when event records should be written to a separate connection. The configured cache driver must support Laravel's unique jobs when processing events asynchronously.

## Recording events

Use the facade to record a numeric value for an event. A scope distinguishes independent streams of the same event—for example, a tenant, user, store, or device.

```php
use Fastchartdev\Package\Facades\Package;

Package::recordEvent(
    eventName: 'orders',
    value: 125.50,
    timestamp: now(),
    scopeValue: 'store-42',
);
```

The call creates the event automatically when needed, persists an event record, and dispatches a job to update all summaries. Its returned value is the created `EventRecord` model.

When a queue connection other than `sync` is configured, run a worker for the configured queue:

```bash
php artisan queue:work --queue=event-records
```

## Querying summaries

Query an aggregation for an event, scope, date range, and period type:

```php
use Fastchartdev\Package\Enums\AggregationEnum;
use Fastchartdev\Package\Enums\PeriodTypeEnum;
use Fastchartdev\Package\Facades\Package;

$summaries = Package::query(
    aggregateFunction: AggregationEnum::SUM,
    eventName: 'orders',
    scopeValue: 'store-42',
    startAt: '2026-01-01',
    endAt: '2026-01-31',
    periodType: PeriodTypeEnum::DAY,
);
```

The result is a collection of `QueryResultData` objects. Each item contains:

```php
[
    'value' => 125.50,
    'start_at' => '2026-01-01 00:00:00',
    'end_at' => '2026-01-01 23:59:59',
]
```

An unknown event raises `EventNotFoundException`. If no matching meter exists yet, the query returns an empty collection. Meters are created when the first record for an event is processed.

### Available aggregations

```php
AggregationEnum::SUM;
AggregationEnum::AVG;
AggregationEnum::COUNT;
AggregationEnum::MIN;
AggregationEnum::MAX;
```

### Available periods and range limits

| Period | Enum | Maximum query range |
| --- | --- | --- |
| Daily | `PeriodTypeEnum::DAY` | 31 days |
| Weekly | `PeriodTypeEnum::WEEK` | 3 months |
| Monthly | `PeriodTypeEnum::MONTH` | 1 year |
| Yearly | `PeriodTypeEnum::YEAR` | 5 years |

Requests beyond these limits raise `LimitExceededException`.

## Debug logging

Set `FASTCHART_DEBUG=true` to write package processing messages to Laravel's log:

```dotenv
FASTCHART_DEBUG=true
```

## Testing

From the package directory:

```bash
composer test
```

## License

FastChart is open-sourced software licensed under the [MIT license](LICENSE.md).
