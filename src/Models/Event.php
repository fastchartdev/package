<?php

namespace Fastchartdev\Package\Models;

use Illuminate\Database\Eloquent\Model;

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
}
