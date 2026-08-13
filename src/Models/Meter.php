<?php

namespace Fastchartdev\Package\Models;

use Fastchartdev\Package\Enums\AggregationEnum;
use Fastchartdev\Package\Enums\PeriodTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property AggregationEnum $aggregation
 * @property PeriodTypeEnum $period_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Event $event
 */
class Meter extends Model
{
    protected $fillable = [
        'event_id',
        'aggregation',
        'period_type',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'aggregation' => AggregationEnum::class,
        'period_type' => PeriodTypeEnum::class,
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getConnectionName()
    {
        return config('fastchart.database.main.connection', 'sqlite');
    }
}
