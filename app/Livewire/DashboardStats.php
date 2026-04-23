<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardStats extends Component
{
    public $dateRange = 'today';
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = Carbon::today()->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
    }

    public function updatedDateRange($value)
    {
        if ($value === 'today') {
            $this->startDate = Carbon::today()->format('Y-m-d');
            $this->endDate = Carbon::today()->format('Y-m-d');
        } elseif ($value === 'yesterday') {
            $this->startDate = Carbon::yesterday()->format('Y-m-d');
            $this->endDate = Carbon::yesterday()->format('Y-m-d');
        } elseif ($value === 'this_week') {
            $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
        } elseif ($value === 'this_month') {
            $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($value === 'last_month') {
            $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
            $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        } elseif ($value === 'this_year') {
            $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
            $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
        }

        $this->dispatch('stats-updated', $this->chartData);
    }

    public function updatedStartDate()
    {
        $this->dispatch('stats-updated', $this->chartData);
    }

    public function updatedEndDate()
    {
        $this->dispatch('stats-updated', $this->chartData);
    }

    public function getStatsProperty()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // Previous period for comparison
        $diff = $start->diffInDays($end) + 1;
        $prevStart = (clone $start)->subDays($diff);
        $prevEnd = (clone $end)->subDays($diff);

        // Current Stats
        $currentTransactions = Transaction::completed()
            ->whereBetween('transaction_date', [$start, $end]);
        
        $totalSales = (float) $currentTransactions->sum('grand_total');
        $transactionCount = $currentTransactions->count();
        $avgValue = $transactionCount > 0 ? $totalSales / $transactionCount : 0;

        // Previous Stats
        $prevTransactions = Transaction::completed()
            ->whereBetween('transaction_date', [$prevStart, $prevEnd]);
        
        $prevTotalSales = (float) $prevTransactions->sum('grand_total');
        $prevTransactionCount = $prevTransactions->count();

        // Comparison Percentages
        $salesChange = $prevTotalSales > 0 ? (($totalSales - $prevTotalSales) / $prevTotalSales) * 100 : 100;
        $countChange = $prevTransactionCount > 0 ? (($transactionCount - $prevTransactionCount) / $prevTransactionCount) * 100 : 100;

        // Financials
        $itemsQuery = TransactionItem::whereHas('transaction', function($q) use ($start, $end) {
            $q->where('status', 'completed')
              ->whereBetween('transaction_date', [$start, $end]);
        });

        $grossRevenue = $totalSales;
        $cogs = (float) $itemsQuery->sum(DB::raw('cost_price * quantity'));
        $grossProfit = $grossRevenue - $cogs;

        // Inventory
        $inventoryStats = [
            'total_items' => Product::active()->sum('stock'),
            'normal' => Product::active()->where('stock', '>=', 10)->count(),
            'low' => Product::active()->whereBetween('stock', [1, 9])->count(),
            'out_of_stock' => Product::active()->where('stock', '<=', 0)->count(),
            'total_value' => (float) Product::active()->sum(DB::raw('stock * cost_price')),
        ];

        // Products
        $topSellers = TransactionItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->whereHas('transaction', function($q) use ($start, $end) {
                $q->where('status', 'completed')
                  ->whereBetween('transaction_date', [$start, $end]);
            })
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $slowMoving = Product::active()
            ->whereNotExists(function ($query) use ($start, $end) {
                $query->select(DB::raw(1))
                    ->from('transaction_items')
                    ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                    ->whereColumn('transaction_items.product_id', 'products.id')
                    ->where('transactions.status', 'completed')
                    ->whereBetween('transactions.transaction_date', [$start, $end]);
            })
            ->limit(5)
            ->get();

        return [
            'total_sales' => $totalSales,
            'transaction_count' => $transactionCount,
            'avg_value' => $avgValue,
            'sales_change' => $salesChange,
            'count_change' => $countChange,
            'gross_revenue' => $grossRevenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'inventory' => $inventoryStats,
            'top_sellers' => $topSellers,
            'slow_moving' => $slowMoving,
        ];
    }

    public function getChartDataProperty()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // Sales Trend
        $salesTrend = Transaction::completed()
            ->whereBetween('transaction_date', [$start, $end])
            ->orderBy('transaction_date')
            ->get()
            ->groupBy(fn($item) => $item->transaction_date->format('Y-m-d'))
            ->map(fn($group) => [
                'date' => $group->first()->transaction_date->format('Y-m-d'),
                'total' => $group->sum('grand_total')
            ])
            ->values();

        // Category Distribution
        $categoryData = TransactionItem::join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereHas('transaction', function($q) use ($start, $end) {
                $q->where('status', 'completed')
                  ->whereBetween('transaction_date', [$start, $end]);
            })
            ->select('categories.name', DB::raw('SUM(transaction_items.subtotal) as total'))
            ->groupBy('categories.name')
            ->get();

        // Peak Hours
        $peakHours = Transaction::completed()
            ->whereBetween('transaction_date', [$start, $end])
            ->get()
            ->groupBy(fn($item) => $item->transaction_date->format('H'))
            ->map(fn($group, $hour) => [
                'hour' => (int) $hour,
                'count' => $group->count()
            ])
            ->sortBy('hour')
            ->values();

        return [
            'sales_trend' => $salesTrend,
            'categories' => $categoryData,
            'peak_hours' => $peakHours,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-stats', [
            'stats' => $this->stats,
            'chartData' => $this->chartData,
        ]);
    }
}
