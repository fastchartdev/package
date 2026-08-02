<?php

namespace Fastchartdev\Package;

use Carbon\CarbonPeriod;

class Package
{
    public function generateAtFromRange(\DateTimeInterface|string $startAt, \DateTimeInterface|string $endAt, string $periodType)
        $period = CarbonPeriod::create(
            ($startAt),
            '1 '.$periodType,
            ($endAt)
        );

        $ats = [];

        foreach ($period as $date) {
            switch ($periodType) {
                case 'day':
                    $ats[] = $date->format('Ymd');
                    break;
                case 'week':
                    $ats[] = $date->format('YW');
                    break;
                case 'month':
                    $ats[] = $date->format('Ym');
                    break;
                case 'year':
                    $ats[] = $date->format('Y');
                    break;
            }
        }

        return collect($ats);
    }

    public function debug($message)
    {
        if (config('fastchart.debug')) {
            info($message);
        }
    }
}
