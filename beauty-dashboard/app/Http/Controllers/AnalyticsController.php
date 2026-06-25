<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    /**
     * Display the Apriori Association Rules Dashboard.
     */
    public function index()
    {
        $rules = [];
        $overstockBundles = [];
        $file_name = 'association_rules.csv';

        $path = storage_path('app/' . $file_name);

        // Check if rules CSV file exists on disk
        if (file_exists($path)) {
            if (($handle = fopen($path, "r")) !== FALSE) {
                // Get header
                $header = fgetcsv($handle, 1000, ",");
                
                // Read rows
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) >= 5) {
                        $rules[] = [
                            'antecedent' => $data[0],
                            'consequent' => $data[1],
                            'support' => floatval($data[2]),
                            'confidence' => floatval($data[3]),
                            'lift' => floatval($data[4]),
                        ];
                    }
                }
                fclose($handle);
            }
        }

        // List of identified overstock items from the analysis
        $overstockItems = [
            'Hydra Grip Primer' => ['days_left' => 596, 'stock' => 1185],
            'Ultra Black Liner' => ['days_left' => 938, 'stock' => 2157],
            'Brow Mascara' => ['days_left' => 265, 'stock' => 624],
            'Flawless Foundation' => ['days_left' => 447, 'stock' => 1248],
            'Soft Blur Powder Cake' => ['days_left' => 1034, 'stock' => 2311],
            'Velvet Air Cushion' => ['days_left' => 452, 'stock' => 867],
        ];

        // Find matches in rules to bundle overstocked items
        foreach ($rules as $rule) {
            $ant = $rule['antecedent'];
            $cons = $rule['consequent'];
            
            // Check if antecedent is an overstock item
            if (array_key_exists($ant, $overstockItems)) {
                $overstockBundles[] = [
                    'overstock_item' => $ant,
                    'days_left' => $overstockItems[$ant]['days_left'],
                    'stock' => $overstockItems[$ant]['stock'],
                    'bundle_with' => $cons,
                    'support' => $rule['support'],
                    'confidence' => $rule['confidence'],
                    'lift' => $rule['lift'],
                    'recommendation' => "Bundel " . $ant . " + " . $cons . " dengan harga paket hemat untuk mengosongkan gudang."
                ];
            }
        }

        // Sort overstock bundles by lift to show the strongest recommendations first
        usort($overstockBundles, function ($a, $b) {
            return $b['lift'] <=> $a['lift'];
        });

        return view('analytics.apriori', [
            'rules' => $rules,
            'overstockBundles' => array_slice($overstockBundles, 0, 10), // Show top 10 overstock bundles
            'total_rules' => count($rules),
            'has_data' => file_exists($path)
        ]);
    }

    /**
     * Run the Apriori Python Script.
     */
    public function runAnalysis()
    {
        $scriptPath = base_path('scripts/apriori_analysis.py');
        
        // Detect absolute python path or fall back to standard PATH command
        $pythonCommand = 'C:\\Users\\rizal\\AppData\\Local\\Programs\\Python\\Python314\\python.exe';
        
        Log::info("Running Apriori Python script: {$pythonCommand} {$scriptPath}");
        
        // Execute python script
        $result = Process::run("{$pythonCommand} {$scriptPath}");
        
        if ($result->successful()) {
            Log::info("Apriori analysis finished successfully. Output: " . $result->output());
            return redirect()->route('analytics.apriori')
                ->with('success', 'Analisis Apriori berhasil dijalankan ulang!');
        } else {
            Log::error("Apriori analysis failed. Error: " . $result->errorOutput());
            return redirect()->route('analytics.apriori')
                ->with('error', 'Gagal menjalankan analisis: ' . $result->errorOutput());
        }
    }
}
