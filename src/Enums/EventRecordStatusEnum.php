<?php

namespace Fastchartdev\Package\Enums;

enum EventRecordStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case IN_PROGRESS = 'in_progress';
}
