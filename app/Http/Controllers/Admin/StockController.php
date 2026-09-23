<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\AuditService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->with('lots')->withSum('lots as stock_quantity', 'quantity')->orderBy('name')->paginate(20);
        $movements = StockMovement::query()->with(['product', 'user'])->latest()->limit(12)->get();

        return view('admin.stock.index', compact('products', 'movements'));
    }

    public function entry(Request $request, StockService $stockService, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'lot_number' => ['required', 'string', 'max:255'],
            'expiration_date' => ['nullable', 'date', 'after:today'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $product = Product::query()->findOrFail($data['product_id']);
        DB::transaction(function () use ($product, $data, $request, $stockService, $auditService): void {
            $lot = $stockService->addStock($product, $data['lot_number'], $data['expiration_date'] ?? null, (int) $data['quantity'], $request->user(), $data['reason'] ?? 'Entrada de estoque');
            $auditService->log('STOCK_ENTRY', $product, $request->user(), null, ['lot_id' => $lot->id, 'quantity' => $lot->quantity], $request->ip(), $request->userAgent());
        }, 3);

        return to_route('admin.stock.index')->with('success', 'Entrada de estoque registrada.');
    }

    public function adjust(Request $request, StockService $stockService, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'lot_id' => ['required', 'integer', 'exists:lots,id'],
            'adjustment' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);
        $product = Product::query()->findOrFail($data['product_id']);
        $lot = Lot::query()->findOrFail($data['lot_id']);

        try {
            $updatedLot = DB::transaction(function () use ($product, $lot, $data, $request, $stockService, $auditService): Lot {
                $updatedLot = $stockService->adjustLot($product, $lot, (int) $data['adjustment'], $request->user(), $data['reason']);
                $auditService->log('STOCK_ADJUSTMENT', $product, $request->user(), null, ['lot_id' => $updatedLot->id, 'quantity' => $updatedLot->quantity, 'reason' => $data['reason']], $request->ip(), $request->userAgent());

                return $updatedLot;
            }, 3);
        } catch (\DomainException $exception) {
            return back()->withErrors(['adjustment' => $exception->getMessage()])->withInput();
        }

        return to_route('admin.stock.index')->with('success', 'Ajuste de estoque registrado.');
    }
}
