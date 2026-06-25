<?php

namespace App\Http\Controllers;

use App\Services\DataService;

class ProductController extends Controller
{
    public function index(DataService $ds)
    {
        $metrics  = $ds->getProductMetrics();
        $products = $ds->getProducts();

        // Enrich top/bottom with margin
        $enrich = function (array $list) {
            return array_map(function ($p) {
                $sellingPrice   = (float)($p['selling_price']    ?? 0);
                $productionCost = (float)($p['production_cost']  ?? 0);
                $margin = $sellingPrice > 0
                    ? (($sellingPrice - $productionCost) / $sellingPrice) * 100
                    : 0;
                return array_merge($p, [
                    'margin_pct'      => $margin,
                    'selling_price'   => $sellingPrice,
                    'production_cost' => $productionCost,
                ]);
            }, $list);
        };

        $topProducts    = $enrich($metrics['top_products']);
        $bottomProducts = $enrich($metrics['bottom_products']);

        $catLabels = array_keys($metrics['category_net']);
        $catNet    = array_values($metrics['category_net']);
        $catQty    = array_map(fn ($k) => $metrics['category_qty'][$k] ?? 0, $catLabels);

        return view('analytics.products', compact(
            'topProducts', 'bottomProducts',
            'catLabels', 'catNet', 'catQty', 'products'
        ));
    }
}
