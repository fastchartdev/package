<?php

namespace Fastchartdev\Package\Models;

use Fastchartdev\Package\Enums\EventRecordStatusEnum;
use Illuminate\Database\Eloquent\Model;

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
}
