<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $parsedStart = Carbon::parse($startDate)->startOfDay();
        $parsedEnd = Carbon::parse($endDate)->endOfDay();

        // Laporan Transaksi Rental & Penjualan
        $salesQuery = Sale::with(['customer', 'user', 'items.product'])
            ->whereBetween('sold_at', [$parsedStart, $parsedEnd]);

        if ($request->filled('status')) {
            $salesQuery->where('status', $request->input('status'));
        }

        $sales = $salesQuery->latest('sold_at')->get();
        $totalSalesAmount = $sales->where('status', 'completed')->sum('total');
        $totalSalesCount = $sales->where('status', 'completed')->count();

        // Laporan Transaksi Pembelian Stok
        $purchasesQuery = Purchase::with(['supplier', 'user', 'items.product'])
            ->whereBetween('purchased_at', [$parsedStart, $parsedEnd]);

        if ($request->filled('status')) {
            $purchasesQuery->where('status', $request->input('status'));
        }

        $purchases = $purchasesQuery->latest('purchased_at')->get();
        $totalPurchasesAmount = $purchases->where('status', 'completed')->sum('total');
        $totalPurchasesCount = $purchases->where('status', 'completed')->count();

        // Nilai aset inventaris saat ini
        $allProducts = Product::with('category')->get();
        $totalInventoryValue = $allProducts->sum(function ($p) {
            return $p->stock * $p->purchase_price;
        });

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'sales',
            'totalSalesAmount',
            'totalSalesCount',
            'purchases',
            'totalPurchasesAmount',
            'totalPurchasesCount',
            'totalInventoryValue',
            'allProducts'
        ));
    }
}
