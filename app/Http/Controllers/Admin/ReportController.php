<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = $filters['from'] ?? today()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? today()->toDateString();
        $sales = Sale::query()->where('status', 'COMPLETED')->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
        $saleCount = (clone $sales)->count();
        $revenue = (float) (clone $sales)->sum('total');
        $discounts = (float) (clone $sales)->sum('discount');
        $averageTicket = $saleCount > 0 ? $revenue / $saleCount : 0;
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'COMPLETED')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as quantity_sold'), DB::raw('SUM(sale_items.subtotal) as revenue'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('quantity_sold')
            ->limit(10)
            ->get();
        $stock = Product::query()->with('category')->withSum('lots as stock_quantity', 'quantity')->where('active', true)->orderBy('name')->limit(20)->get();
        $movements = StockMovement::query()->with(['product', 'user'])->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])->latest()->limit(20)->get();

        return view('admin.reports.index', compact('from', 'to', 'saleCount', 'revenue', 'discounts', 'averageTicket', 'topProducts', 'stock', 'movements'));
    }
}
