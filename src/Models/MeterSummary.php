<?php

namespace Fastchartdev\Package\Models;

use Illuminate\Database\Eloquent\Model;

class MeterSummary extends Model
{
    protected $fillable = [
        'id',
        'meter_id',
        'count',
        'scope_value',
        'value',
        'start_at',
        'end_at',
        'at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'meter_id' => 'integer',
        'count' => 'integer',
        'value' => 'decimal:2',
        'start_at' => 'datetime:Y-m-d H:i:s',
        'end_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }
}
