<?php

namespace Fastchartdev\Package\Jobs;

use Fastchartdev\Package\Enums\AggregationEnum;
use Fastchartdev\Package\Enums\EventRecordStatusEnum;
use Fastchartdev\Package\Enums\PeriodTypeEnum;
use Fastchartdev\Package\Facades\At;
use Fastchartdev\Package\Facades\Package;
use Fastchartdev\Package\Models\EventRecord;
use Fastchartdev\Package\Models\Meter;
use Fastchartdev\Package\Models\MeterSummary;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class RecordEventJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $eventRecordId,
    ) {
        $this->onConnection(config('fastchart.queues.main.connection', 'sync'));
        $this->onQueue(config('fastchart.queues.main.queue', 'event-records'));
    }

    public function uniqueId(): string
    {
        return (string) $this->eventRecordId;
    }

    public function uniqueVia(): Repository
    {
        return Cache::driver(config('fastchart.queues.main.unique_via_cache_driver', 'redis'));
    }

    public function backoff(): array
    {
        return config('fastchart.queues.main.backoff', [
            10, // Retry after 10 seconds
            30, // Retry after 30 seconds
            60 * 1, // Retry after 1 minute
            60 * 2, // Retry after 2 minutes
            60 * 5, // Retry after 5 minutes
            60 * 10, // Retry after 10 minutes
            60 * 30, // Retry after 30 minutes
            60 * 60, // Retry after 1 hour
        ]);
    }

    public function middleware(): array
    {
        $eventRecord = EventRecord::find($this->eventRecordId);

        if (! $eventRecord) {
            return [
                (new WithoutOverlapping('RecordEventJob:'.$this->eventRecordId))
                    ->releaseAfter(rand(30, 60)) // Retry after a random time between 30 and 60 seconds
                    ->shared(),
            ];
        }

        $event = $eventRecord->event;

        $releaseAfter = rand(30, 60);

        $return = [
            (new WithoutOverlapping('RecordEventJob:'.$this->eventRecordId))
                ->releaseAfter($releaseAfter) // Retry after a random time between 30 and 60 seconds
                ->shared(),
            (new WithoutOverlapping('RecordEventJob:event-'.$event->id.',scope_value-'.$eventRecord->scope_value))
                ->releaseAfter($releaseAfter) // Retry after a random time between 30 and 60 seconds
                ->shared(),
        ];

        return $return;
    }

    public function tags(): array
    {
        return [
            'event-record',
            'event-record:'.$this->eventRecordId,
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $eventRecord = EventRecord::find($this->eventRecordId);

        Package::debug("[RecordEventJob][Info] Processing event record ID {$this->eventRecordId}");

        if ($eventRecord && $eventRecord->status === EventRecordStatusEnum::COMPLETED) {
            Package::debug("[RecordEventJob] Event record ID {$this->eventRecordId} is already completed. Skipping.");

            return;
        }

        if ($eventRecord) {
            try {
                $eventRecord->update([
                    'status' => EventRecordStatusEnum::IN_PROGRESS,
                    'started_at' => now(),
                ]);

                $event = $eventRecord->event;

                $meters = Meter::where('event_id', $event->id)->get();

                if ($meters->isEmpty()) {
                    $eventRecord->update([
                        'status' => EventRecordStatusEnum::FAILED,
                        'failed_at' => now(),
                        'failure_reason' => 'Meter not found for the event.',
                    ]);

                    return;
                }

                DB::connection(config('fastchart.database.main'))->beginTransaction();

                Package::debug("[RecordEventJob][Info] Processing event record ID {$eventRecord->id} for event ID {$event->id}");

                foreach ($meters as $meter) {
                    Package::debug("[RecordEventJob][Info] Processing meter ID {$meter->id} {$meter->period_type->value} for event record ID {$eventRecord->id}");

                    $at = Package::generateAtFromRange($eventRecord->timestamp, $eventRecord->timestamp, $meter->period_type->value)->first();

                    Package::debug("[RecordEventJob][Info] Generated 'at' value: {$at} for meter ID {$meter->id} and event record ID {$eventRecord->id}");

                    $meterSummary = MeterSummary::where('meter_id', $meter->id)
                        ->where('scope_value', $eventRecord->scope_value)
                        ->where('at', $at)
                        ->first();

                    try {
                        if (! $meterSummary) {
                            switch ($meter->period_type) {
                                case PeriodTypeEnum::DAY:
                                    $start_at = $eventRecord->timestamp->startOfDay();
                                    $end_at = $eventRecord->timestamp->endOfDay();
                                    break;
                                case PeriodTypeEnum::WEEK:
                                    $start_at = $eventRecord->timestamp->startOfWeek();
                                    $end_at = $eventRecord->timestamp->endOfWeek();
                                    break;
                                case PeriodTypeEnum::MONTH:
                                    $start_at = $eventRecord->timestamp->startOfMonth();
                                    $end_at = $eventRecord->timestamp->endOfMonth();
                                    break;
                                case PeriodTypeEnum::YEAR:
                                    $start_at = $eventRecord->timestamp->startOfYear();
                                    $end_at = $eventRecord->timestamp->endOfYear();
                                    break;
                                default:
                                    DB::connection(config('fastchart.database.main'))->rollBack();
                                    $eventRecord->update([
                                        'status' => EventRecordStatusEnum::FAILED,
                                        'failed_at' => now(),
                                        'failure_reason' => 'Unsupported period type for meter.',
                                    ]);
                                    Package::debug("[RecordEventJob][Failed] Unsupported period type for meter ID {$meter->id}. Event record ID {$eventRecord->id} marked as failed.");

                                    return; // Unsupported period type
                            }

                            switch ($meter->aggregation) {
                                case AggregationEnum::SUM:
                                    $meterSummary = MeterSummary::create([
                                        'meter_id' => $meter->id,
                                        'scope_value' => $eventRecord->scope_value,
                                        'value' => $eventRecord->value,
                                        'start_at' => $start_at,
                                        'end_at' => $end_at,
                                        'at' => $at,
                                    ]);
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value {$eventRecord->value}, total value now {$meterSummary->value}");
                                    break;
                                case AggregationEnum::COUNT:
                                    $meterSummary = MeterSummary::create([
                                        'meter_id' => $meter->id,
                                        'scope_value' => $eventRecord->scope_value,
                                        'value' => 1, // Count starts at 1
                                        'start_at' => $start_at,
                                        'end_at' => $end_at,
                                        'at' => $at,
                                    ]);
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value 1, total value now {$meterSummary->value}");
                                    break;
                                case AggregationEnum::MIN:
                                    $meterSummary = MeterSummary::create([
                                        'meter_id' => $meter->id,
                                        'scope_value' => $eventRecord->scope_value,
                                        'value' => $eventRecord->value,
                                        'start_at' => $start_at,
                                        'end_at' => $end_at,
                                        'at' => $at,
                                    ]);
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value {$eventRecord->value}, total value now {$meterSummary->value}");
                                    break;
                                case AggregationEnum::MAX:
                                    $meterSummary = MeterSummary::create([
                                        'meter_id' => $meter->id,
                                        'scope_value' => $eventRecord->scope_value,
                                        'value' => $eventRecord->value,
                                        'start_at' => $start_at,
                                        'end_at' => $end_at,
                                        'at' => $at,
                                    ]);
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value {$eventRecord->value}, total value now {$meterSummary->value}");
                                    break;
                                case AggregationEnum::AVG:
                                    $meterSummary = MeterSummary::create([
                                        'meter_id' => $meter->id,
                                        'scope_value' => $eventRecord->scope_value,
                                        'value' => $eventRecord->value,
                                        'count' => 1,
                                        'start_at' => $start_at,
                                        'end_at' => $end_at,
                                        'at' => $at,
                                    ]);
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value {$eventRecord->value}, total value now {$meterSummary->value}");
                                    break;
                                default:
                                    $eventRecord->update([
                                        'status' => EventRecordStatusEnum::FAILED,
                                        'failed_at' => now(),
                                        'failure_reason' => 'Unsupported aggregation type for meter. '.$meter->aggregation->value,
                                    ]);
                                    break;
                            }

                            Package::debug("[RecordEventJob][Created] new meter summary for meter ID {$meter->id} with value {$eventRecord->value} from {$start_at} to {$end_at}");
                        } else {
                            Package::debug("[RecordEventJob][Found] existing meter summary for meter ID {$meter->id} with value {$meterSummary->value}");
                            switch ($meter->aggregation) {
                                case AggregationEnum::SUM:
                                    $meterSummary->increment('value', $eventRecord->value);
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value {$eventRecord->value}, total value now {$meterSummary->value}");
                                    break;
                                case AggregationEnum::COUNT:
                                    $meterSummary->increment('value');
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value 1, total value now {$meterSummary->value}");
                                    break;
                                case AggregationEnum::MIN:
                                    if ($eventRecord->value < $meterSummary->value) {
                                        $meterSummary->update(['value' => $eventRecord->value]);
                                    }
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value {$eventRecord->value}, total value now {$meterSummary->value}");
                                    break;
                                case AggregationEnum::MAX:
                                    if ($eventRecord->value > $meterSummary->value) {
                                        $meterSummary->update(['value' => $eventRecord->value]);
                                    }
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value {$eventRecord->value}, total value now {$meterSummary->value}");
                                    break;
                                case AggregationEnum::AVG:
                                    $currentTotal = $meterSummary->value * $meterSummary->count;
                                    $newCount = $meterSummary->count + 1;
                                    $newAverage = ($currentTotal + $eventRecord->value) / $newCount;
                                    $meterSummary->update(['value' => $newAverage, 'count' => $newCount]);
                                    Package::debug("[RecordEventJob][Updated] meter summary for meter ID {$meter->id} with new value {$eventRecord->value}, total value now {$meterSummary->value}");
                                    break;
                                default:
                                    $eventRecord->update([
                                        'status' => EventRecordStatusEnum::FAILED,
                                        'failed_at' => now(),
                                        'failure_reason' => 'Unsupported aggregation type for meter. '.$meter->aggregation->value,
                                    ]);
                                    Package::debug("[RecordEventJob][Failed] Unsupported aggregation type for meter ID {$meter->id}. Event record ID {$eventRecord->id} marked as failed.");
                                    break;
                            }
                        }
                    } catch (\Exception $e) {
                        DB::connection(config('fastchart.database.main'))->rollBack();
                        $eventRecord->update([
                            'status' => EventRecordStatusEnum::FAILED,
                            'failed_at' => now(),
                            'failure_reason' => $e->getMessage(),
                        ]);
                        Package::debug("[RecordEventJob][Failed] Exception occurred while processing meter ID {$meter->id} for event record ID {$eventRecord->id}: ".$e->getMessage());

                        return; // Stop processing if an error occurs
                    }
                }

                $eventRecord->update([
                    'status' => EventRecordStatusEnum::COMPLETED,
                    'completed_at' => now(),
                ]);

                DB::connection(config('fastchart.database.main'))->commit();

                return;
            } catch (\Exception $e) {
                $eventRecord->update([
                    'status' => EventRecordStatusEnum::FAILED,
                    'failed_at' => now(),
                    'failure_reason' => '(TRC) '.$e->getMessage(),
                ]);
                Package::debug("[RecordEventJob][Failed] Exception occurred while processing event record ID {$this->eventRecordId}: ".$e->getMessage());
                DB::connection(config('fastchart.database.main'))->rollBack();

                return;
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        $eventRecord = EventRecord::find($this->eventRecordId);

        if ($eventRecord) {
            $eventRecord->update([
                'status' => EventRecordStatusEnum::FAILED,
                'failed_at' => now(),
                'failure_reason' => $exception ? ('(JF) '.$exception->getMessage()) : '(JF) Unknown error',
            ]);
        }
    }
}
