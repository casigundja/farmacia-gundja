@extends('admin.layout')
@section('title', 'Estoque')
@section('content')
<div class="admin-head"><div><h1>Estoque e lotes</h1><p>Consulte saldos, validade e histórico de movimentações.</p></div></div>
<section class="table-wrap"><table><thead><tr><th>Produto</th><th>Estoque</th><th>Mínimo</th><th>Situação</th>@if (auth()->user()->hasPermission('stock.manage'))<th>Movimentação</th>@endif</tr></thead><tbody>
@forelse ($products as $product)
    @php($currentStock = (int) ($product->stock_quantity ?? 0))
    <tr><td><strong>{{ $product->name }}</strong><div class="muted" style="font-size:12px">{{ $product->internal_code }}</div></td><td>{{ $currentStock }}</td><td>{{ $product->minimum_stock }}</td><td><span class="status {{ $currentStock <= $product->minimum_stock ? 'off' : '' }}">{{ $currentStock === 0 ? 'Sem estoque' : ($currentStock <= $product->minimum_stock ? 'Estoque baixo' : 'Normal') }}</span></td>
        @if (auth()->user()->hasPermission('stock.manage'))<td><details><summary style="color:var(--wine);cursor:pointer">Registrar entrada</summary><form action="{{ route('admin.stock.entry') }}" method="POST" style="display:grid;gap:7px;min-width:220px;margin-top:10px">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><input name="lot_number" placeholder="Número do lote" required><input name="expiration_date" type="date" min="{{ today()->addDay()->toDateString() }}"><input name="quantity" type="number" min="1" placeholder="Quantidade" required><input name="reason" placeholder="Motivo (opcional)"><button class="btn" type="submit">Salvar entrada</button></form></details></td>@endif
    </tr>
    @if ($product->lots->isNotEmpty())<tr><td colspan="{{ auth()->user()->hasPermission('stock.manage') ? 5 : 4 }}" style="background:#fdfafb"><details><summary class="muted" style="cursor:pointer">Ver {{ $product->lots->count() }} lote(s)</summary><div style="display:grid;gap:8px;margin-top:10px">
        @foreach ($product->lots as $lot)
            @if (auth()->user()->hasPermission('stock.manage'))<form action="{{ route('admin.stock.adjust') }}" method="POST" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><input type="hidden" name="lot_id" value="{{ $lot->id }}"><span style="min-width:210px">Lote {{ $lot->lot_number }} · {{ $lot->expiration_date?->format('d/m/Y') ?? 'sem validade' }} · saldo {{ $lot->quantity }}</span><input name="adjustment" type="number" placeholder="+/- quantidade" required style="width:135px"><input name="reason" placeholder="Motivo obrigatório" required><button class="btn btn-secondary" type="submit">Ajustar</button></form>
            @else<div>Lote {{ $lot->lot_number }} · {{ $lot->expiration_date?->format('d/m/Y') ?? 'sem validade' }} · saldo {{ $lot->quantity }}</div>@endif
        @endforeach
    </div></details></td></tr>@endif
@empty<tr><td colspan="5" class="muted">Nenhum produto cadastrado.</td></tr>@endforelse
</tbody></table></section>
<div style="margin:18px 0">{{ $products->links() }}</div>
<div class="admin-head" style="margin-top:34px"><div><h2 style="margin:0">Movimentações recentes</h2></div></div>
<section class="table-wrap"><table><thead><tr><th>Data</th><th>Produto</th><th>Movimento</th><th>Quantidade</th><th>Usuário</th><th>Motivo</th></tr></thead><tbody>@forelse ($movements as $movement)<tr><td>{{ $movement->created_at->format('d/m/Y H:i') }}</td><td>{{ $movement->product->name }}</td><td>{{ str($movement->type)->replace('_', ' ')->title() }}</td><td>{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td><td>{{ $movement->user?->name ?? 'Sistema' }}</td><td>{{ $movement->reason ?? '—' }}</td></tr>@empty<tr><td colspan="6" class="muted">Nenhuma movimentação registrada.</td></tr>@endforelse</tbody></table></section>
@endsection
