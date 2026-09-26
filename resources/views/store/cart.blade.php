@extends('layouts.store')
@section('title', 'Minha sacola')
@section('content')
<div class="wrap" style="padding:35px 0 65px"><div class="section-head"><div><span class="eyebrow" style="color:var(--wine)">Sua seleção</span><h1 style="font-size:38px;margin:5px 0">Minha sacola</h1></div><a class="text-link" href="{{ route('products.index') }}">Continuar comprando →</a></div>
    @if ($errors->any())<div class="notice" style="margin-bottom:18px;background:#fff0ed;color:#a23d2c;border-color:#f0cfc8">{{ $errors->first() }}</div>@endif
    @if (!empty($hasPrescriptionItems))
        <div class="notice" style="margin-bottom:18px;background:#fff1f2;border-color:#fecdd3;color:#9f1239">
            <strong>⚠️ Atenção: A sua sacola contém medicamento(s) sujeito(s) a Receita Médica</strong>
            <p style="margin:4px 0 0;font-size:13px">De acordo com a regulamentação do setor farmacêutico em Angola, será obrigatório anexar o comprovativo da receita médica no momento de finalizar o pedido para validação pelo farmacêutico de serviço.</p>
        </div>
    @endif
    @if ($cart->items->isEmpty())<div class="empty">Sua sacola está vazia.<p style="margin:12px 0 0"><a class="button" href="{{ route('products.index') }}">Explorar produtos</a></p></div>
    @else
        <div style="display:grid;grid-template-columns:1.6fr .8fr;gap:24px;align-items:start">
            <section style="display:grid;gap:12px">
                @foreach ($cart->items as $item)
                    <article style="display:flex;gap:16px;align-items:center;background:#fff;border:1px solid var(--line);padding:15px;border-radius:13px">
                        <div style="width:82px;height:82px;flex:none;display:grid;place-items:center;background:var(--rose);border-radius:10px;color:var(--wine);font-size:27px">✚</div>
                        <div style="flex:1">
                            <div style="display:flex;align-items:center;gap:8px">
                                <strong>{{ $item->product->name }}</strong>
                                @if ($item->product->requires_prescription)
                                    <span style="background:#fff1f2;color:#be123c;font-size:10px;font-weight:bold;padding:2px 6px;border-radius:4px">Receita</span>
                                @endif
                            </div>
                            <div style="font-size:13px;color:var(--muted)">{{ number_format((float) $item->unit_price, 2, ',', '.') }} Kz cada</div>
                        </div>
                        <form action="{{ route('cart.update', $item) }}" method="POST" style="display:flex;gap:6px;align-items:center">
                            @csrf @method('PATCH')
                            <input aria-label="Quantidade de {{ $item->product->name }}" name="quantity" type="number" min="1" max="99" value="{{ $item->quantity }}" style="width:65px;padding:8px;border:1px solid var(--line);border-radius:7px">
                            <button class="text-link" type="submit">Atualizar</button>
                        </form>
                        <strong>{{ number_format($item->quantity * (float) $item->unit_price, 2, ',', '.') }} Kz</strong>
                        <form action="{{ route('cart.remove', $item) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" aria-label="Remover {{ $item->product->name }}" style="border:0;background:none;color:#9d5969;font-size:18px;cursor:pointer">×</button>
                        </form>
                    </article>
                @endforeach
            </section>
            <aside style="background:#fff;border:1px solid var(--line);padding:22px;border-radius:14px">
                <h2 style="font-size:19px;margin:0 0 20px">Resumo do Pedido</h2>
                <div style="display:flex;justify-content:space-between;color:var(--muted)">
                    <span>Subtotal</span>
                    <span>{{ number_format((float) $subtotal, 2, ',', '.') }} Kz</span>
                </div>
                <hr style="border:0;border-top:1px solid var(--line);margin:18px 0">
                <div style="display:flex;justify-content:space-between;font-weight:800;font-size:18px">
                    <span>Total Estimado</span>
                    <span style="color:var(--wine)">{{ number_format((float) $subtotal, 2, ',', '.') }} Kz</span>
                </div>
                <p style="margin:8px 0 16px;font-size:12px;color:var(--muted)">Taxa de entrega calculada por município no checkout (ou retirada grátis na farmácia).</p>
                <a class="button" style="width:100%" href="{{ route('checkout') }}">Avançar para o Checkout</a>
            </aside>
        </div>
    @endif
</div>
@endsection
