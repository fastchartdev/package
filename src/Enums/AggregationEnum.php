<?php

namespace Fastchartdev\Package\Enums;

enum AggregationEnum: string
{
    case SUM = 'sum';
    case AVG = 'avg';
    case COUNT = 'count';
    case MIN = 'min';
    case MAX = 'max';
}
