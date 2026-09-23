@extends('layouts.store')

@section('title', $product->name)

@section('content')
<div class="wrap">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Início</a> / <a href="{{ route('products.index') }}">Produtos</a> / {{ $product->name }}</div>
    <article class="product-detail">
        <div class="detail-visual">
            @if ($product->images->first())
                <img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}">
            @else
                <span aria-hidden="true">✚</span>
            @endif
        </div>
        <div class="detail-copy">
            <span class="eyebrow" style="color:var(--wine)">{{ $product->brand?->name ?? $product->category->name }}</span>
            <h1>{{ $product->name }}</h1>
            <div class="price">R$ {{ number_format((float) $product->sale_price, 2, ',', '.') }}</div>
            @if ($product->availableLots->sum('quantity') > 0)
                <p class="availability">Disponível para compra</p>
            @else
                <p class="availability out">Indisponível no momento</p>
            @endif
            @if ($product->requires_prescription || $product->controlled)
                <p class="notice">Este produto possui regras específicas de venda. Consulte a farmácia para confirmar os requisitos aplicáveis.</p>
            @endif
            @if ($product->description)
                <h3>Sobre o produto</h3><p style="color:var(--muted)">{{ $product->description }}</p>
            @endif
            <p style="font-size:13px;color:var(--muted)">Categoria: {{ $product->category->name }}</p>
            @if ($product->availableLots->sum('quantity') > 0)
                @auth
                    @if (auth()->user()->isCustomer())
                        <form action="{{ route('cart.add', $product) }}" method="POST" style="display:flex;gap:10px;align-items:center">@csrf<label for="quantity">Quantidade</label><input id="quantity" name="quantity" type="number" min="1" max="99" value="1" style="width:75px;padding:10px;border:1px solid var(--line);border-radius:8px"><button class="button" type="submit">Adicionar à sacola</button></form>
                    @endif
                @else
                    <a class="button" href="{{ route('login') }}">Entrar para comprar</a>
                @endauth
            @endif
        </div>
    </article>
</div>
@endsection
