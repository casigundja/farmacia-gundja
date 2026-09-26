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
            <div class="price">
                @if ($product->promotional_price && $product->promotional_price < $product->sale_price)
                    <span>{{ number_format((float) $product->promotional_price, 2, ',', '.') }} Kz</span>
                    <span style="font-size:16px;text-decoration:line-through;color:var(--muted);font-weight:normal;margin-left:8px">{{ number_format((float) $product->sale_price, 2, ',', '.') }} Kz</span>
                    <span style="background:#dcfce7;color:#15803d;font-size:12px;padding:3px 8px;border-radius:6px;margin-left:8px;vertical-align:middle">Promoção</span>
                @else
                    <span>{{ number_format((float) $product->sale_price, 2, ',', '.') }} Kz</span>
                @endif
            </div>

            @if ($product->dosage || $product->pharmaceutical_form)
                <div style="margin:10px 0;padding:10px 14px;background:#fdf2f4;border-radius:8px;border:1px solid #fce7ec;display:flex;gap:20px;font-size:13px">
                    @if ($product->dosage)
                        <div><strong>Dosagem:</strong> {{ $product->dosage }}</div>
                    @endif
                    @if ($product->pharmaceutical_form)
                        <div><strong>Forma Farmacêutica:</strong> {{ $product->pharmaceutical_form }}</div>
                    @endif
                    @if ($product->internal_code)
                        <div><strong>Cód:</strong> {{ $product->internal_code }}</div>
                    @endif
                </div>
            @endif

            @if ($product->availableLots->sum('quantity') > 0)
                <p class="availability">✓ Disponível para compra imediata</p>
            @else
                <p class="availability out">✕ Indisponível no momento</p>
            @endif

            @if ($product->requires_prescription)
                <div class="notice" style="background:#fff1f2;border-color:#fecdd3;color:#9f1239;margin:16px 0">
                    <strong>⚠️ Medicamento Sujeito a Receita Médica Obrigatória</strong>
                    <p style="margin:4px 0 0;font-size:12px">Em cumprimento da legislação e regulamentação sanitária da República de Angola, a dispensa deste medicamento requer a apresentação e validação prévia de uma receita médica válida emitida por profissional habilitado. Poderá anexar a sua receita no momento do checkout.</p>
                </div>
            @endif

            @if ($product->description)
                <h3>Sobre o produto</h3><p style="color:var(--muted);line-height:1.6">{{ $product->description }}</p>
            @endif
            <p style="font-size:13px;color:var(--muted)">Categoria: <strong>{{ $product->category->name }}</strong></p>
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
