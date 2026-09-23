@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')
<div class="admin-head"><div><h1>Visão geral</h1><p>Resumo da operação de hoje.</p></div></div>
<div class="cards"><div class="metric"><span>Vendas hoje</span><strong>R$ {{ number_format((float) $salesTotal, 2, ',', '.') }}</strong></div><div class="metric"><span>Pedidos pendentes</span><strong>{{ $ordersPending }}</strong></div><div class="metric"><span>Clientes cadastrados</span><strong>{{ number_format($customersCount, 0, ',', '.') }}</strong></div><div class="metric"><span>Produtos com estoque baixo</span><strong>{{ $lowStockProducts->count() }}</strong></div></div>
<section class="table-wrap"><table><thead><tr><th colspan="4">Atenção ao estoque</th></tr><tr><th>Produto</th><th>Categoria</th><th>Estoque</th><th>Mínimo</th></tr></thead><tbody>@forelse ($lowStockProducts as $product)<tr><td>{{ $product->name }}</td><td>{{ $product->category->name }}</td><td>{{ $product->stock_quantity ?? 0 }}</td><td>{{ $product->minimum_stock }}</td></tr>@empty<tr><td colspan="4" class="muted">Nenhum produto abaixo do estoque mínimo.</td></tr>@endforelse</tbody></table></section>
@endsection
