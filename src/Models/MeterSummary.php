<?php

namespace Fastchartdev\Package\Models;

use Illuminate\Database\Eloquent\Model;

/**
    * @property int $id
    * @property int $meter_id
    * @property int $count
    * @property string|null $scope_value
    * @property float $value
    * @property \Illuminate\Support\Carbon|null $start_at
    * @property \Illuminate\Support\Carbon|null $end_at
    * @property int|null $at
    * @property \Illuminate\Support\Carbon|null $created_at
    * @property \Illuminate\Support\Carbon|null $updated_at
    * @property Meter $meter
*/
class MeterSummary extends Model
{
    protected $fillable = [
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
        'at' => 'integer',
        'start_at' => 'datetime:Y-m-d H:i:s',
        'end_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }

    public function getConnectionName()
    {
        return config('fastchart.connections.main.connection', 'sqlite');
    }
}
