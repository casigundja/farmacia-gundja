<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->where('active', true)->orderBy('name')->get();
        $products = Product::query()
            ->with(['brand', 'images'])
            ->withSum('availableLots as stock_quantity', 'quantity')
            ->where('active', true)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('store.home', compact('categories', 'products'));
    }
}
