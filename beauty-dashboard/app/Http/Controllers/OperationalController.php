<?php

namespace App\Http\Controllers;

use App\Services\DataService;

class OperationalController extends Controller
{
    public function index(DataService $ds)
    {
        $metrics = $ds->getOperationalMetrics();
        $sales   = $ds->getSalesMetrics();

        $returnRate  = $sales['return_rate'];
        $cancelRate  = $sales['cancel_rate'];
        $avgDelivery = $sales['avg_delivery_days'];

        return view('analytics.operational', compact(
            'metrics', 'returnRate', 'cancelRate', 'avgDelivery'
        ));
    }
}
