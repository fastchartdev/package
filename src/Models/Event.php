<?php

namespace Fastchartdev\Package\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|Meter[] $meters
 * @property Collection|EventRecord[] $eventRecords
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
        return config('fastchart.database.main.connection', 'sqlite');
    }
}
