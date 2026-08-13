<?php

namespace Fastchartdev\Package;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Fastchartdev\Package\Data\QueryResultData;
use Fastchartdev\Package\Enums\AggregationEnum;
use Fastchartdev\Package\Enums\EventRecordStatusEnum;
use Fastchartdev\Package\Enums\PeriodTypeEnum;
use Fastchartdev\Package\Exceptions\EventNotFoundException;
use Fastchartdev\Package\Exceptions\LimitExceededException;
use Fastchartdev\Package\Jobs\RecordEventJob;
use Fastchartdev\Package\Models\Event;
use Fastchartdev\Package\Models\EventRecord;
use Fastchartdev\Package\Models\Meter;
use Fastchartdev\Package\Models\MeterSummary;

class Package
{
    public function query(AggregationEnum $aggregateFunction, string $eventName, string $scopeValue, \DateTimeInterface|string $startAt, \DateTimeInterface|string $endAt, PeriodTypeEnum $periodType)
    {
        $event = Event::where('name', $eventName)
            ->first();

        if (! $event) {
            throw new EventNotFoundException($eventName);
        }

        $ats = $this->generateAtFromRange($startAt, $endAt, $periodType->value);

        $startAt = Carbon::parse($startAt);
        $endAt = Carbon::parse($endAt);

        if ($periodType === PeriodTypeEnum::DAY) {
            if ($startAt->diffInDays($endAt) > 31) {
                throw new LimitExceededException('The range between start_at and end_at should not be more than 31 days for '.$periodType->value.' period type');
            }
        } elseif ($periodType === PeriodTypeEnum::WEEK) {
            if ($endAt->gt($startAt->copy()->addWeeks(12))) {
                throw new LimitExceededException('The range between start_at and end_at should not be more than 3 months for '.$periodType->value.' period type');
            }
        } elseif ($periodType === PeriodTypeEnum::MONTH) {
            if ($endAt->gt($startAt->copy()->addMonths(12))) {
                throw new LimitExceededException('The range between start_at and end_at should not be more than 1 year for '.$periodType->value.' period type');
            }
        } elseif ($periodType === PeriodTypeEnum::YEAR) {
            if ($endAt->gt($startAt->copy()->addYears(5))) {
                throw new LimitExceededException('The range between start_at and end_at should not be more than 5 years for '.$periodType->value.' period type');
            }
        }

        $meter = Meter::where('event_id', $event->id)
            ->where('aggregation', $aggregateFunction->value)
            ->where('period_type', $periodType->value)
            ->first();

        if (! $meter) {
            return QueryResultData::collect([]);
        }

        $query = MeterSummary::query()
            ->where('meter_id', $meter->id)
            ->where('scope_value', $scopeValue)
            ->whereIn('at', $ats)
            ->get();

        return QueryResultData::collect($query);
    }

    public function recordEvent(string $eventName, float|int $value, \DateTimeInterface|string $timestamp, string $scopeValue): EventRecord
    {
        $event = Event::where('name', $eventName)
            ->first();

        if (! $event) {
            $event = Event::create([
                'name' => $eventName,
            ]);
        }

        $eventRecord = EventRecord::create([
            'event_id' => $event->id,
            'value' => $value,
            'timestamp' => $timestamp,
            'scope_value' => $scopeValue,
            'status' => EventRecordStatusEnum::PENDING,
            'started_at' => null,
            'completed_at' => null,
            'failed_at' => null,
        ]);

        RecordEventJob::dispatch($eventRecord->id);

        return $eventRecord;
    }

    public function generateAtFromRange(\DateTimeInterface|string $startAt, \DateTimeInterface|string $endAt, string $periodType)
    {
        $period = CarbonPeriod::create(
            ($startAt),
            '1 '.$periodType,
            ($endAt)
        );

        $ats = [];

        foreach ($period as $date) {
            switch ($periodType) {
                case 'day':
                    $ats[] = $date->format('Ymd');
                    break;
                case 'week':
                    $ats[] = $date->format('YW');
                    break;
                case 'month':
                    $ats[] = $date->format('Ym');
                    break;
                case 'year':
                    $ats[] = $date->format('Y');
                    break;
            }
        }

        return collect($ats);
    }

    public function debug($message)
    {
        if (config('fastchart.debug')) {
            info($message);
        }
    }
}
