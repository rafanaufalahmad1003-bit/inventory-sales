<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Tampilan khusus jika login sebagai Pelanggan
        if ($user->isPelanggan()) {
            $customer = Customer::where('user_id', $user->id)->first();
            $customerId = $customer ? $customer->id : null;

            $mySales = Sale::with(['items.product'])
                ->where('customer_id', $customerId)
                ->latest('sold_at')
                ->paginate(10);

            $activeRentals = Sale::with(['items.product'])
                ->where('customer_id', $customerId)
                ->where('type', 'rental')
                ->where('status', 'completed')
                ->where('rental_end_date', '>=', Carbon::today())
                ->get();

            $availableProducts = Product::with('category')
                ->inStock()
                ->take(8)
                ->get();

            return view('dashboard.pelanggan', compact('user', 'customer', 'mySales', 'activeRentals', 'availableProducts'));
        }

        // Tampilan Dashboard untuk Owner, Admin, dan Staff
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalProducts = Product::count();
        $lowStockCount = Product::lowStock()->count();
        $totalCategories = Category::count();
        $totalCustomers = Customer::count();
        $totalSuppliers = Supplier::count();

        // Penjualan / Rental
        $todaySalesCount = Sale::where('status', 'completed')->whereDate('sold_at', $today)->count();
        $todaySalesAmount = Sale::where('status', 'completed')->whereDate('sold_at', $today)->sum('total');

        $monthSalesCount = Sale::where('status', 'completed')->where('sold_at', '>=', $startOfMonth)->count();
        $monthSalesAmount = Sale::where('status', 'completed')->where('sold_at', '>=', $startOfMonth)->sum('total');

        $totalSalesAmount = Sale::where('status', 'completed')->sum('total');
        $totalPurchasesAmount = Purchase::where('status', 'completed')->sum('total');

        $recentSales = Sale::with(['customer', 'user', 'items'])
            ->latest('sold_at')
            ->take(5)
            ->get();

        $recentPurchases = Purchase::with(['supplier', 'user', 'items'])
            ->latest('purchased_at')
            ->take(5)
            ->get();

        $lowStockProducts = Product::with('category')
            ->lowStock()
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalProducts',
            'lowStockCount',
            'totalCategories',
            'totalCustomers',
            'totalSuppliers',
            'todaySalesCount',
            'todaySalesAmount',
            'monthSalesCount',
            'monthSalesAmount',
            'totalSalesAmount',
            'totalPurchasesAmount',
            'recentSales',
            'recentPurchases',
            'lowStockProducts'
        ));
    }
}
