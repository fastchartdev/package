<?php

namespace Fastchartdev\Package\Models;

use Illuminate\Database\Eloquent\Model;

/**
    * @property int $id
    * @property string $name
    * @property \Illuminate\Support\Carbon|null $created_at
    * @property \Illuminate\Support\Carbon|null $updated_at
    * @property \Illuminate\Database\Eloquent\Collection|Meter[] $meters
    * @property \Illuminate\Database\Eloquent\Collection|EventRecord[] $eventRecords
*/
class Event extends Model
{
    protected $fillable = [
        'name',
    ];

    public function meters()
    {
        return $this->hasMany(Meter::class);
    }

    public function eventRecords()
    {
        return $this->hasMany(EventRecord::class);
    }

    public function getConnectionName()
    {
        return config('fastchart.connections.main.connection', 'sqlite');
    }
}
