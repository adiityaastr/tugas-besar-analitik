<?php

namespace App\Http\Controllers;

use App\Services\DataService;

class MarketplaceController extends Controller
{
    public function index(DataService $ds)
    {
        $metrics = $ds->getSalesMetrics();

        $platforms = [];
        foreach ($metrics['platform_net'] as $name => $net) {
            $count = $metrics['platform_count'][$name] ?? 0;
            $gross = $metrics['platform_gross'][$name] ?? 0;
            $fee   = $metrics['platform_fee'][$name]   ?? 0;
            $platforms[] = [
                'name'      => $name,
                'net'       => $net,
                'gross'     => $gross,
                'count'     => $count,
                'fee'       => $fee,
                'aov'       => $count > 0 ? $net / $count : 0,
                'fee_rate'  => $gross > 0 ? ($fee / $gross) * 100 : 0,
                'disc_rate' => $gross > 0 ? (($gross - $net - $fee) / $gross) * 100 : 0,
            ];
        }

        // Sort by net sales desc
        usort($platforms, fn ($a, $b) => $b['net'] <=> $a['net']);

        return view('analytics.marketplace', compact('platforms', 'metrics'));
    }
}
