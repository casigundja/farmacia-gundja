@extends('admin.layout')
@section('title', 'Produtos')
@section('content')
<div class="admin-head"><div><h1>Produtos</h1><p>Cadastro e situação do catálogo.</p></div>@if (auth()->user()->hasPermission('products.manage'))<a class="btn" href="{{ route('admin.products.create') }}">+ Novo produto</a>@endif</div>
<section class="table-wrap"><table><thead><tr><th>Código</th><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Estoque mínimo</th><th>Status</th>@if (auth()->user()->hasPermission('products.manage'))<th>Ações</th>@endif</tr></thead><tbody>
@forelse ($products as $product)
<tr><td>{{ $product->internal_code }}</td><td><strong>{{ $product->name }}</strong><div class="muted" style="font-size:12px">{{ $product->brand?->name ?? 'Sem marca' }}</div></td><td>{{ $product->category->name }}</td><td>{{ number_format((float) $product->sale_price, 2, ',', '.') }} Kz</td><td>{{ $product->stock_quantity ?? 0 }}</td><td>{{ $product->minimum_stock }}</td><td><span class="status {{ $product->active ? '' : 'off' }}">{{ $product->active ? 'Ativo' : 'Inativo' }}</span></td>@if (auth()->user()->hasPermission('products.manage'))<td style="white-space:nowrap"><a class="btn btn-secondary" href="{{ route('admin.products.edit', $product) }}">Editar</a><form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline" onsubmit="return confirm('Desativar este produto?')">@csrf @method('DELETE')<button class="btn btn-secondary" type="submit">Desativar</button></form></td>@endif</tr>
@empty<tr><td colspan="8" class="muted">Nenhum produto cadastrado.</td></tr>@endforelse
</tbody></table></section>
<div style="margin-top:20px">{{ $products->links() }}</div>
@endsection
