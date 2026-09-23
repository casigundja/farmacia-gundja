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
            <div class="product-brand">{{ $product->brand?->name ?? $product->category?->name }}</div>
            <h3>{{ $product->name }}</h3>
            <div class="price">R$ {{ number_format((float) $product->sale_price, 2, ',', '.') }}</div>
            @if (($product->stock_quantity ?? 0) > 0)
                <div class="availability">Disponível</div>
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
