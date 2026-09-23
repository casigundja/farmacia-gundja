@extends('layouts.store')

@section('title', 'Produtos')

@section('content')
<div class="wrap">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Início</a> / Produtos</div>
    <div class="section-head"><div><span class="eyebrow" style="color:var(--wine)">Vitrine</span><h1 style="font-size:38px;margin:6px 0">Produtos</h1><p>Encontre o que você precisa para cuidar de você.</p></div></div>
    <form class="filters" method="GET" action="{{ route('products.index') }}">
        <div><label for="q">Pesquisar</label><input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nome ou código"></div>
        <div><label for="category">Categoria</label><select id="category" name="category"><option value="">Todas</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div><label for="brand">Marca</label><select id="brand" name="brand"><option value="">Todas</option>@foreach ($brands as $brand)<option value="{{ $brand->id }}" @selected(($filters['brand'] ?? '') == $brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
        <div><label for="min">Preço mínimo</label><input id="min" name="min" type="number" min="0" step="0.01" value="{{ $filters['min'] ?? '' }}"></div>
        <div><label for="max">Preço máximo</label><input id="max" name="max" type="number" min="0" step="0.01" value="{{ $filters['max'] ?? '' }}"></div>
        <div style="display:flex;align-items:end"><button class="button" type="submit">Filtrar</button></div>
        <label style="grid-column:1/-1;display:flex;align-items:center;gap:8px;font-size:13px;color:var(--ink)"><input type="checkbox" name="available" value="1" @checked($filters['available'] ?? false)> Mostrar somente produtos disponíveis</label>
    </form>
    <p style="color:var(--muted);font-size:13px">{{ $products->total() }} produto(s) encontrado(s)</p>
    @if ($products->isNotEmpty())
        <div class="product-grid">@foreach ($products as $product) @include('components.store-product-card', ['product' => $product]) @endforeach</div>
        <div class="pagination">{{ $products->links() }}</div>
    @else
        <div class="empty">Nenhum produto encontrado. Tente alterar os filtros da busca.</div>
    @endif
</div>
@endsection
