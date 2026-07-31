<?php

namespace Fastchartdev\Package\Models;

use Fastchartdev\Package\Enums\AggregationEnum;
use Fastchartdev\Package\Enums\PeriodTypeEnum;
use Illuminate\Database\Eloquent\Model;

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
}
