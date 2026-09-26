<article class="product-card">
    <a href="{{ route('products.show', ['product' => $product->slug]) }}" aria-label="Ver {{ $product->name }}">
        <div class="product-visual">
            @if ($product->images->first())
                <img src="{{ asset('storage/'.$product->images->first()->path) }}" alt="{{ $product->name }}">
            @else
                <span aria-hidden="true">✚</span>
            @endif
        </div>
        <div class="product-body">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                <span class="product-brand">{{ $product->brand?->name ?? $product->category?->name }}</span>
                @if ($product->requires_prescription)
                    <span style="background:#fff1f2;color:#be123c;border:1px solid #fecdd3;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700" title="Medicamento sujeito a receita médica">Receita Obrigatória</span>
                @endif
            </div>
            <h3>{{ $product->name }}</h3>
            @if ($product->dosage || $product->pharmaceutical_form)
                <div style="font-size:12px;color:var(--muted);margin-bottom:6px">{{ implode(' · ', array_filter([$product->dosage, $product->pharmaceutical_form])) }}</div>
            @endif
            <div class="price">
                @if ($product->promotional_price && $product->promotional_price < $product->sale_price)
                    <span>{{ number_format((float) $product->promotional_price, 2, ',', '.') }} Kz</span>
                    <span style="font-size:12px;text-decoration:line-through;color:var(--muted);font-weight:normal;margin-left:6px">{{ number_format((float) $product->sale_price, 2, ',', '.') }} Kz</span>
                @else
                    <span>{{ number_format((float) $product->sale_price, 2, ',', '.') }} Kz</span>
                @endif
            </div>
            @if (($product->stock_quantity ?? 0) > 0)
                <div class="availability">Disponível em Estoque</div>
            @else
                <div class="availability out">Indisponível no momento</div>
            @endif
        </div>
    </a>
    <div style="padding:0 15px 15px">
        @if (($product->stock_quantity ?? 0) > 0)
            @auth
                @if (auth()->user()->isCustomer())
                    <form action="{{ route('cart.add', $product) }}" method="POST">@csrf<input type="hidden" name="quantity" value="1"><button class="button" style="width:100%;padding:9px;font-size:13px" type="submit">Adicionar à sacola</button></form>
                @endif
            @else
                <a class="button" style="width:100%;padding:9px;font-size:13px" href="{{ route('login') }}">Entrar para comprar</a>
            @endauth
        @endif
    </div>
</article>
