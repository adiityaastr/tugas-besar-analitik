<?php

namespace App\Http\Controllers;

use App\Services\DataService;

class SalesController extends Controller
{
    public function index(DataService $ds)
    {
        $metrics = $ds->getSalesMetrics();

        // Prepare monthly trend for Chart.js
        $trendLabels   = array_keys($metrics['monthly_net']);
        $trendNet      = array_values($metrics['monthly_net']);
        $trendGross    = array_values($metrics['monthly_gross']);

        // Platform data
        $platLabels = array_keys($metrics['platform_net']);
        $platNet    = array_values($metrics['platform_net']);
        $platCount  = array_map(fn ($k) => $metrics['platform_count'][$k] ?? 0, $platLabels);
        $platFee    = array_map(fn ($k) => $metrics['platform_fee'][$k]   ?? 0, $platLabels);

        // Campaign top 8
        $campaignData = [];
        $i = 0;
        foreach ($metrics['campaign_net'] as $name => $net) {
            if ($i++ >= 8) break;
            $campaignData[] = [
                'name'  => $name,
                'net'   => $net,
                'count' => $metrics['campaign_count'][$name] ?? 0,
            ];
        }

        return view('analytics.sales', compact(
            'metrics',
            'trendLabels', 'trendNet', 'trendGross',
            'platLabels', 'platNet', 'platCount', 'platFee',
            'campaignData'
        ));
    }
}
