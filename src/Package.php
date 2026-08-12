<?php

namespace Fastchartdev\Package;

use Carbon\CarbonPeriod;
use Fastchartdev\Package\Enums\EventRecordStatusEnum;
use Fastchartdev\Package\Jobs\RecordEventJob;
use Fastchartdev\Package\Models\Event;
use Fastchartdev\Package\Models\EventRecord;

class Package
{
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
