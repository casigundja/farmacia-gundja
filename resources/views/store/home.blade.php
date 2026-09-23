@extends('layouts.store')

@section('title', 'Cuidado e bem-estar')

@section('content')
<div class="wrap">
    <section class="hero">
        <div class="hero-copy">
            <span class="eyebrow">Cuidado que acompanha você</span>
            <h1>Sua saúde merece atenção todos os dias.</h1>
            <p>Encontre produtos para cuidar de você e de quem você ama, com praticidade e confiança.</p>
            <form class="search" action="{{ route('products.index') }}" method="GET">
                <input name="q" type="search" placeholder="O que você está procurando?" aria-label="Pesquisar produtos">
                <button class="button" type="submit">Buscar produtos</button>
            </form>
        </div>
    </section>

    <section class="section">
        <div class="section-head"><div><span class="eyebrow" style="color:var(--wine)">Encontre com facilidade</span><h2>Explore por categoria</h2><p>Um cuidado para cada momento.</p></div><a class="text-link" href="{{ route('products.index') }}">Ver todas →</a></div>
        <div class="category-grid">
            @forelse ($categories as $category)
                <a class="category-tile" href="{{ route('products.index', ['category' => $category->id]) }}"><span class="category-symbol">✚</span><strong>{{ $category->name }}</strong><span>Conheça os produtos</span></a>
            @empty
                <div class="empty" style="grid-column:1/-1">As categorias serão exibidas aqui assim que o catálogo for cadastrado.</div>
            @endforelse
        </div>
    </section>

    <section class="section">
        <div class="section-head"><div><span class="eyebrow" style="color:var(--wine)">Seleção da farmácia</span><h2>Produtos em destaque</h2><p>Itens para cuidar de você no dia a dia.</p></div><a class="text-link" href="{{ route('products.index') }}">Ver catálogo →</a></div>
        @if ($products->isNotEmpty())
            <div class="product-grid">@foreach ($products as $product) @include('components.store-product-card', ['product' => $product]) @endforeach</div>
        @else
            <div class="empty">Estamos preparando nosso catálogo. Volte em breve para conhecer os produtos.</div>
        @endif
    </section>
</div>
@endsection
