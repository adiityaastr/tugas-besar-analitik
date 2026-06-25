<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DataService
{
    protected string $productsPath;
    protected string $transactionsPath;

    public function __construct()
    {
        $this->productsPath    = storage_path('app/product_code - Sheet1.csv');
        $this->transactionsPath = storage_path('app/transactions - Sheet1.csv');
    }

    // ------------------------------------------------------------------ //
    //  Raw parsers
    // ------------------------------------------------------------------ //

    public function getProducts(): array
    {
        return Cache::remember('products', 300, fn () => $this->parseCSV($this->productsPath));
    }

    public function getTransactions(): array
    {
        return Cache::remember('transactions', 300, fn () => $this->parseCSV($this->transactionsPath));
    }

    protected function parseCSV(string $path): array
    {
        if (!file_exists($path)) return [];

        $rows   = [];
        $handle = fopen($path, 'r');
        if (!$handle) return [];

        $header = fgetcsv($handle, 0, ',');
        if (!$header) { fclose($handle); return []; }

        // Trim BOM / whitespace from header
        $header = array_map(fn ($h) => trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF"), $header);

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if (count($data) === count($header)) {
                $rows[] = array_combine($header, $data);
            }
        }

        fclose($handle);
        return $rows;
    }

    // ------------------------------------------------------------------ //
    //  Sales Performance Metrics
    // ------------------------------------------------------------------ //

    public function getSalesMetrics(): array
    {
        $transactions = $this->getTransactions();

        $totalNetSales    = 0;
        $totalGrossSales  = 0;
        $totalDiscount    = 0;
        $totalQty         = 0;
        $returnedCount    = 0;
        $cancelledCount   = 0;
        $deliveredCount   = 0;
        $ratingSum        = 0;
        $ratingCount      = 0;

        // Monthly trend: key = YYYY-MM
        $monthlyNet       = [];
        $monthlyGross     = [];

        // Platform breakdown
        $platformNet      = [];
        $platformGross    = [];
        $platformCount    = [];
        $platformFee      = [];

        // Campaign
        $campaignNet      = [];
        $campaignCount    = [];

        // Delivery performance
        $deliveryDaysSum  = 0;
        $deliveryDaysCount= 0;

        foreach ($transactions as $t) {
            $net     = (float)($t['net_sales']       ?? 0);
            $gross   = (float)($t['gross_sales']     ?? 0);
            $disc    = (float)($t['discount_amount'] ?? 0);
            $qty     = (int)  ($t['quantity']        ?? 0);
            $fee     = (float)($t['platform_fee']    ?? 0);
            $rating  = (float)($t['customer_rating'] ?? 0);
            $platform = trim($t['platform'] ?? 'Unknown');
            $campaign = trim($t['campaign_name'] ?? 'No Campaign');
            $returned = strtolower(trim($t['returned_flag'] ?? 'No'));
            $status   = strtolower(trim($t['delivery_status'] ?? ''));
            $days     = (int)($t['delivery_days'] ?? 0);

            // Parse date  dd/mm/yyyy  or  yyyy-mm-dd
            $dateStr = trim($t['transaction_date'] ?? '');
            $month   = $this->parseMonth($dateStr);

            $totalNetSales   += $net;
            $totalGrossSales += $gross;
            $totalDiscount   += $disc;
            $totalQty        += $qty;

            if ($month) {
                $monthlyNet[$month]   = ($monthlyNet[$month]   ?? 0) + $net;
                $monthlyGross[$month] = ($monthlyGross[$month] ?? 0) + $gross;
            }

            // Platform
            $platformNet[$platform]   = ($platformNet[$platform]   ?? 0) + $net;
            $platformGross[$platform] = ($platformGross[$platform] ?? 0) + $gross;
            $platformCount[$platform] = ($platformCount[$platform] ?? 0) + 1;
            $platformFee[$platform]   = ($platformFee[$platform]   ?? 0) + $fee;

            // Campaign
            $campaignNet[$campaign]   = ($campaignNet[$campaign]   ?? 0) + $net;
            $campaignCount[$campaign] = ($campaignCount[$campaign] ?? 0) + 1;

            if ($returned === 'yes') $returnedCount++;
            if (in_array($status, ['cancelled', 'cancel'])) $cancelledCount++;
            if (in_array($status, ['delivered', 'terkirim'])) $deliveredCount++;

            if ($rating > 0) { $ratingSum += $rating; $ratingCount++; }
            if ($days > 0)   { $deliveryDaysSum += $days; $deliveryDaysCount++; }
        }

        $totalTx = count($transactions);
        ksort($monthlyNet);
        ksort($monthlyGross);
        arsort($platformNet);
        arsort($campaignNet);

        return [
            'total_net_sales'       => $totalNetSales,
            'total_gross_sales'     => $totalGrossSales,
            'total_discount'        => $totalDiscount,
            'total_qty'             => $totalQty,
            'total_transactions'    => $totalTx,
            'avg_order_value'       => $totalTx > 0 ? $totalNetSales / $totalTx : 0,
            'returned_count'        => $returnedCount,
            'return_rate'           => $totalTx > 0 ? ($returnedCount / $totalTx) * 100 : 0,
            'cancelled_count'       => $cancelledCount,
            'cancel_rate'           => $totalTx > 0 ? ($cancelledCount / $totalTx) * 100 : 0,
            'delivered_count'       => $deliveredCount,
            'avg_rating'            => $ratingCount > 0 ? $ratingSum / $ratingCount : 0,
            'avg_delivery_days'     => $deliveryDaysCount > 0 ? $deliveryDaysSum / $deliveryDaysCount : 0,
            'monthly_net'           => $monthlyNet,
            'monthly_gross'         => $monthlyGross,
            'platform_net'          => $platformNet,
            'platform_gross'        => $platformGross,
            'platform_count'        => $platformCount,
            'platform_fee'          => $platformFee,
            'campaign_net'          => $campaignNet,
            'campaign_count'        => $campaignCount,
        ];
    }

    // ------------------------------------------------------------------ //
    //  Product Performance Metrics
    // ------------------------------------------------------------------ //

    public function getProductMetrics(): array
    {
        $transactions = $this->getTransactions();
        $products     = collect($this->getProducts())->keyBy('product_code');

        $productNet    = [];
        $productQty    = [];
        $productCount  = [];
        $categoryNet   = [];
        $categoryQty   = [];
        $productRating = [];
        $productRatingCount = [];

        foreach ($transactions as $t) {
            $code   = trim($t['product_code'] ?? '');
            $net    = (float)($t['net_sales'] ?? 0);
            $qty    = (int)  ($t['quantity']  ?? 0);
            $rating = (float)($t['customer_rating'] ?? 0);

            $product  = $products->get($code);
            $category = $product['category'] ?? 'Unknown';

            $productNet[$code]   = ($productNet[$code]   ?? 0) + $net;
            $productQty[$code]   = ($productQty[$code]   ?? 0) + $qty;
            $productCount[$code] = ($productCount[$code] ?? 0) + 1;
            $categoryNet[$category]   = ($categoryNet[$category]   ?? 0) + $net;
            $categoryQty[$category]   = ($categoryQty[$category]   ?? 0) + $qty;

            if ($rating > 0) {
                $productRating[$code]      = ($productRating[$code]      ?? 0) + $rating;
                $productRatingCount[$code] = ($productRatingCount[$code] ?? 0) + 1;
            }
        }

        arsort($productNet);
        arsort($categoryNet);

        // Build rich top-10 products
        $top10 = [];
        $i = 0;
        foreach ($productNet as $code => $net) {
            if ($i++ >= 10) break;
            $p = $products->get($code, []);
            $avgRating = isset($productRatingCount[$code]) && $productRatingCount[$code] > 0
                ? $productRating[$code] / $productRatingCount[$code]
                : 0;
            $top10[] = [
                'code'          => $code,
                'name'          => $p['product_name'] ?? $code,
                'category'      => $p['category']     ?? '-',
                'net_sales'     => $net,
                'qty'           => $productQty[$code] ?? 0,
                'transactions'  => $productCount[$code] ?? 0,
                'stock'         => (int)($p['stock_qty'] ?? 0),
                'selling_price' => (float)($p['selling_price'] ?? 0),
                'production_cost'=> (float)($p['production_cost'] ?? 0),
                'avg_rating'    => $avgRating,
            ];
        }

        // Bottom 10 (low performers)
        asort($productNet);
        $bottom10 = [];
        $i = 0;
        foreach ($productNet as $code => $net) {
            if ($i++ >= 10) break;
            $p = $products->get($code, []);
            $bottom10[] = [
                'code'      => $code,
                'name'      => $p['product_name'] ?? $code,
                'category'  => $p['category']     ?? '-',
                'net_sales' => $net,
                'qty'       => $productQty[$code] ?? 0,
                'stock'     => (int)($p['stock_qty'] ?? 0),
            ];
        }

        return [
            'top_products'    => $top10,
            'bottom_products' => $bottom10,
            'category_net'    => $categoryNet,
            'category_qty'    => $categoryQty,
            'product_qty'     => $productQty,
            'product_net'     => $productNet,  // already sorted asc
        ];
    }

    // ------------------------------------------------------------------ //
    //  Customer Insight Metrics
    // ------------------------------------------------------------------ //

    public function getCustomerMetrics(): array
    {
        $transactions = $this->getTransactions();

        $ageGroups     = ['<18' => 0, '18-24' => 0, '25-34' => 0, '35-44' => 0, '45+' => 0];
        $genderCount   = [];
        $membershipNet = [];
        $membershipCount= [];
        $ratingDist    = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $cityNet       = [];
        $provinceNet   = [];
        $paymentCount  = [];
        $channelCount  = [];

        foreach ($transactions as $t) {
            $age        = (int)($t['customer_age']    ?? 0);
            $gender     = trim($t['customer_gender']  ?? 'Unknown');
            $membership = trim($t['membership_tier']  ?? 'Unknown');
            $rating     = (int)($t['customer_rating'] ?? 0);
            $net        = (float)($t['net_sales']     ?? 0);
            $city       = trim($t['city']             ?? 'Unknown');
            $province   = trim($t['province']         ?? 'Unknown');
            $payment    = trim($t['payment_method']   ?? 'Unknown');
            $channel    = trim($t['sales_channel_type'] ?? 'Unknown');

            // Age group
            if ($age < 18)       $ageGroups['<18']++;
            elseif ($age <= 24)  $ageGroups['18-24']++;
            elseif ($age <= 34)  $ageGroups['25-34']++;
            elseif ($age <= 44)  $ageGroups['35-44']++;
            else                 $ageGroups['45+']++;

            $genderCount[$gender] = ($genderCount[$gender] ?? 0) + 1;

            $membershipNet[$membership]   = ($membershipNet[$membership]   ?? 0) + $net;
            $membershipCount[$membership] = ($membershipCount[$membership] ?? 0) + 1;

            if ($rating >= 1 && $rating <= 5) $ratingDist[$rating]++;

            $cityNet[$city]       = ($cityNet[$city]       ?? 0) + $net;
            $provinceNet[$province] = ($provinceNet[$province] ?? 0) + $net;

            $paymentCount[$payment] = ($paymentCount[$payment] ?? 0) + 1;
            $channelCount[$channel] = ($channelCount[$channel] ?? 0) + 1;
        }

        arsort($cityNet);
        arsort($provinceNet);
        arsort($membershipNet);

        return [
            'age_groups'       => $ageGroups,
            'gender_count'     => $genderCount,
            'membership_net'   => $membershipNet,
            'membership_count' => $membershipCount,
            'rating_dist'      => $ratingDist,
            'top_cities'       => array_slice($cityNet, 0, 10, true),
            'top_provinces'    => $provinceNet,
            'payment_count'    => $paymentCount,
            'channel_count'    => $channelCount,
            'total_unique_customers' => count(array_unique(array_column($transactions, 'customer_id'))),
        ];
    }

    // ------------------------------------------------------------------ //
    //  Operational Insight Metrics
    // ------------------------------------------------------------------ //

    public function getOperationalMetrics(): array
    {
        $transactions = $this->getTransactions();

        $deliveryStatusCount = [];
        $returnReasonCount   = [];
        $returnByProduct     = [];
        $returnByPlatform    = [];
        $deliveryDaysDist    = [];
        $cancelByPlatform    = [];

        $products = collect($this->getProducts())->keyBy('product_code');

        foreach ($transactions as $t) {
            $status    = trim($t['delivery_status'] ?? 'Unknown');
            $returned  = strtolower(trim($t['returned_flag'] ?? 'No'));
            $reason    = trim($t['return_reason']  ?? '');
            $code      = trim($t['product_code']   ?? '');
            $platform  = trim($t['platform']       ?? 'Unknown');
            $days      = (int)($t['delivery_days'] ?? 0);

            $deliveryStatusCount[$status] = ($deliveryStatusCount[$status] ?? 0) + 1;

            if ($returned === 'yes') {
                $r = $reason ?: 'Unspecified';
                $returnReasonCount[$r] = ($returnReasonCount[$r] ?? 0) + 1;
                $pname = $products->get($code)['product_name'] ?? $code;
                $returnByProduct[$pname] = ($returnByProduct[$pname] ?? 0) + 1;
                $returnByPlatform[$platform] = ($returnByPlatform[$platform] ?? 0) + 1;
            }

            if (in_array(strtolower($status), ['cancelled', 'cancel'])) {
                $cancelByPlatform[$platform] = ($cancelByPlatform[$platform] ?? 0) + 1;
            }

            if ($days > 0) {
                $bucket = $days <= 2 ? '1-2d' : ($days <= 4 ? '3-4d' : ($days <= 6 ? '5-6d' : '7d+'));
                $deliveryDaysDist[$bucket] = ($deliveryDaysDist[$bucket] ?? 0) + 1;
            }
        }

        arsort($returnReasonCount);
        arsort($returnByProduct);
        arsort($returnByPlatform);

        $dayOrder = ['1-2d', '3-4d', '5-6d', '7d+'];
        $sortedDaysDist = [];
        foreach ($dayOrder as $k) {
            $sortedDaysDist[$k] = $deliveryDaysDist[$k] ?? 0;
        }

        return [
            'delivery_status_count' => $deliveryStatusCount,
            'return_reason_count'   => $returnReasonCount,
            'return_by_product'     => array_slice($returnByProduct, 0, 10, true),
            'return_by_platform'    => $returnByPlatform,
            'cancel_by_platform'    => $cancelByPlatform,
            'delivery_days_dist'    => $sortedDaysDist,
            'total_transactions'    => count($transactions),
        ];
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    protected function parseMonth(string $dateStr): ?string
    {
        if (empty($dateStr)) return null;

        // dd/mm/yyyy
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateStr, $m)) {
            return $m[3] . '-' . $m[2];
        }
        // yyyy-mm-dd
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dateStr, $m)) {
            return $m[1] . '-' . $m[2];
        }
        return null;
    }
}
