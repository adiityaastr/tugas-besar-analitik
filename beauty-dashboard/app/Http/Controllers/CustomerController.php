<?php

namespace App\Http\Controllers;

use App\Services\DataService;

class CustomerController extends Controller
{
    public function index(DataService $ds)
    {
        $metrics = $ds->getCustomerMetrics();
        return view('analytics.customers', compact('metrics'));
    }
}
