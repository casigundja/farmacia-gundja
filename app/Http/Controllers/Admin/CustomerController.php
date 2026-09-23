<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->with('user')
            ->withCount('orders')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim().'%';
                $query->whereHas('user', fn ($users) => $users->where('name', 'like', $search)->orWhere('email', 'like', $search))
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('cpf', 'like', $search);
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(Customer $customer): View
    {
        $customer->load(['user', 'addresses', 'orders' => fn ($orders) => $orders->latest()->with('payments')]);

        return view('admin.customers.show', compact('customer'));
    }
}
