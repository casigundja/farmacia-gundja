@extends('layouts.store')
@section('title', 'Meus pedidos')
@section('content')
<div class="wrap" style="padding:35px 0 65px"><div class="section-head"><div><span class="eyebrow" style="color:var(--wine)">Área do cliente</span><h1 style="font-size:38px;margin:5px 0">Meus pedidos</h1><p>Acompanhe o andamento das suas compras.</p></div></div>
    @if ($orders->isEmpty())<div class="empty">Você ainda não fez nenhum pedido.<p style="margin:12px 0 0"><a class="button" href="{{ route('products.index') }}">Ver produtos</a></p></div>@else
        <div style="display:grid;gap:12px">@foreach ($orders as $order)<a href="{{ route('customer.orders.show', $order) }}" style="display:flex;justify-content:space-between;gap:15px;align-items:center;background:white;border:1px solid var(--line);padding:18px;border-radius:12px"><div><strong>{{ $order->order_number }}</strong><div style="font-size:13px;color:var(--muted)">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->items()->sum('quantity') }} item(ns) · {{ $order->delivery_type === 'PICKUP' ? 'Retirada na Farmácia' : 'Entrega ao Domicílio' }}</div></div><div style="text-align:right"><strong>{{ number_format((float) $order->total, 2, ',', '.') }} Kz</strong><div style="font-size:12px;color:var(--wine);font-weight:600">{{ $order->statusLabel() }}</div></div></a>@endforeach</div><div style="margin-top:20px">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
