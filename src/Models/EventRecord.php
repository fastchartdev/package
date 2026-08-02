<?php

namespace Fastchartdev\Package\Models;

use Fastchartdev\Package\Enums\EventRecordStatusEnum;
use Illuminate\Database\Eloquent\Model;

/**
    * @property int $id
    * @property int $event_id
    * @property float $value
    * @property \Illuminate\Support\Carbon $timestamp
    * @property string|null $scope_value
    * @property EventRecordStatusEnum $status
    * @property \Illuminate\Support\Carbon|null $started_at
    * @property \Illuminate\Support\Carbon|null $completed_at
    * @property \Illuminate\Support\Carbon|null $failed_at
    * @property string|null $failure_reason
    * @property \Illuminate\Support\Carbon|null $created_at
    * @property \Illuminate\Support\Carbon|null $updated_at
    * @property Event $event
*/
class EventRecord extends Model
{
    protected $fillable = [
        'event_id',
        'value',
        'timestamp',
        'scope_value',
        'status',
        'started_at',
        'completed_at',
        'failed_at',
        'failure_reason',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'timestamp' => 'datetime:Y-m-d H:i:s',
        'started_at' => 'datetime:Y-m-d H:i:s',
        'completed_at' => 'datetime:Y-m-d H:i:s',
        'failed_at' => 'datetime:Y-m-d H:i:s',
        'status' => EventRecordStatusEnum::class,
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getConnectionName()
    {
        return config('fastchart.connections.event_records.connection', 'sqlite');
    }
}
