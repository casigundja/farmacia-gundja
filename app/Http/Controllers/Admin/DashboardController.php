<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $salesTotal = Sale::query()->where('status', 'COMPLETED')->whereDate('created_at', today())->sum('total');
        $ordersPending = Order::query()->whereIn('status', ['PENDING', 'CONFIRMED'])->count();
        $customersCount = Customer::query()->count();
        $lowStockProducts = Product::query()
            ->withSum('availableLots as stock_quantity', 'quantity')
            ->where('active', true)
            ->havingRaw('COALESCE(stock_quantity, 0) <= minimum_stock')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('salesTotal', 'ordersPending', 'customersCount', 'lowStockProducts'));
    }
}
