<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403);
        $orders = Order::query()->where('customer_id', $customer->id)->with('payments')->latest()->paginate(12);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->customer_id === $request->user()->customer?->id, 404);
        $order->load(['items.product', 'payments', 'address']);

        return view('customer.orders.show', compact('order'));
    }
}
