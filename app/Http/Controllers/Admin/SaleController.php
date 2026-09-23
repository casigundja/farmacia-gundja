<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(): View
    {
        $sales = Sale::query()->with(['employee.user', 'customer.user', 'payments'])->latest()->paginate(20);

        return view('admin.sales.index', compact('sales'));
    }

    public function create(): View
    {
        $products = Product::query()->withSum('availableLots as stock_quantity', 'quantity')->where('active', true)->orderBy('name')->get();
        $customers = Customer::query()->with('user')->orderBy('id')->get();

        return view('admin.sales.create', compact('products', 'customers'));
    }

    public function store(Request $request, SaleService $saleService): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:CASH,PIX,CREDIT_CARD,DEBIT_CARD,OTHER'],
        ]);
        $customer = null;
        if ($request->filled('customer_id')) {
            $customerId = $request->validate(['customer_id' => ['integer', 'exists:customers,id']])['customer_id'];
            $customer = Customer::query()->findOrFail($customerId);
        }

        try {
            $sale = $saleService->createSale(
                $data['items'],
                (float) ($data['discount'] ?? 0),
                $data['payment_method'],
                $request->user(),
                $customer,
                $request->ip(),
                $request->userAgent(),
            );
        } catch (\DomainException $exception) {
            return back()->withErrors(['sale' => $exception->getMessage()])->withInput();
        }

        return to_route('admin.sales.index')->with('success', 'Venda '.$sale->sale_number.' finalizada com sucesso.');
    }

    public function cancel(Request $request, Sale $sale, SaleService $saleService): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        try {
            $saleService->cancelSale($sale, $request->user(), $data['reason'], $request->ip(), $request->userAgent());
        } catch (\DomainException $exception) {
            return back()->withErrors(['sale' => $exception->getMessage()]);
        }

        return to_route('admin.sales.index')->with('success', 'Venda cancelada e estoque devolvido. Registre o estorno do pagamento fora do sistema.');
    }
}
