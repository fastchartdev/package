<?php

namespace Fastchartdev\Package\Data;

use Spatie\LaravelData\Data;

class QueryResultData extends Data
{
    public function __construct(
        public float $value,
        public string $start_at,
        public string $end_at,
    ) {}
}
