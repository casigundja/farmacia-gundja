@extends('layouts.store')
@section('title', 'Pedido '.$order->order_number)
@section('content')
<div class="wrap" style="padding:35px 0 65px">
    <div class="section-head">
        <div>
            <span class="eyebrow" style="color:var(--wine)">Acompanhamento de Encomenda</span>
            <h1 style="font-size:34px;margin:5px 0">Pedido {{ $order->order_number }}</h1>
            <p>Realizado em {{ $order->created_at->format('d/m/Y às H:i') }}</p>
        </div>
        <a class="text-link" href="{{ route('customer.orders.index') }}">← Todas as encomendas</a>
    </div>

    @if ($order->prescription)
        <div style="margin-bottom:20px;padding:16px 20px;border-radius:12px;background:{{ $order->prescription->isApproved() ? '#ecfdf5;border:1px solid #a7f3d0;color:#065f46' : ($order->prescription->isRejected() ? '#fef2f2;border:1px solid #fecaca;color:#991b1b' : '#fffbeb;border:1px solid #fde68a;color:#92400e') }}">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <strong>Validação Farmacêutica da Receita Médica: {{ $order->prescription->status === 'APPROVED' ? 'Aprovada ✓' : ($order->prescription->status === 'REJECTED' ? 'Rejeitada ✕' : 'Em Análise pelo Farmacêutico ⏳') }}</strong>
                <a href="{{ asset('storage/'.$order->prescription->file_path) }}" target="_blank" style="text-decoration:underline;font-size:13px">Ver documento enviado</a>
            </div>
            @if ($order->prescription->review_notes)
                <p style="margin:6px 0 0;font-size:13px"><strong>Parecer do Farmacêutico:</strong> {{ $order->prescription->review_notes }}</p>
            @endif
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1.3fr .7fr;gap:20px;align-items:start">
        <section style="background:white;border:1px solid var(--line);border-radius:14px;padding:24px">
            <h2 style="font-size:19px;margin-bottom:14px">Itens do pedido</h2>
            @foreach ($order->items as $item)
                <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--line)">
                    <div>
                        <strong>{{ $item->quantity }} × {{ $item->product->name }}</strong>
                        @if ($item->product->requires_prescription)
                            <span style="background:#fff1f2;color:#be123c;font-size:10px;padding:2px 6px;border-radius:4px;margin-left:6px">Receita</span>
                        @endif
                    </div>
                    <strong>{{ number_format((float) $item->subtotal, 2, ',', '.') }} Kz</strong>
                </div>
            @endforeach

            <div style="margin-top:16px;display:grid;gap:6px;font-size:14px">
                <div style="display:flex;justify-content:space-between;color:var(--muted)">
                    <span>Subtotal</span>
                    <span>{{ number_format((float) $order->subtotal, 2, ',', '.') }} Kz</span>
                </div>
                <div style="display:flex;justify-content:space-between;color:var(--muted)">
                    <span>Taxa de Entrega</span>
                    <span>{{ number_format((float) $order->shipping, 2, ',', '.') }} Kz</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-weight:800;font-size:20px;margin-top:8px;border-top:1px solid var(--line);padding-top:10px;color:var(--wine)">
                    <span>Total Pago / A Pagar</span>
                    <span>{{ number_format((float) $order->total, 2, ',', '.') }} Kz</span>
                </div>
            </div>
        </section>

        <aside style="background:white;border:1px solid var(--line);border-radius:14px;padding:24px">
            <h2 style="font-size:19px;margin-bottom:12px">Estado do Pedido</h2>
            <div style="padding:10px 14px;background:#fdf2f4;border-radius:8px;color:var(--wine);font-weight:bold;font-size:14px;margin-bottom:16px">
                ● {{ $order->statusLabel() }}
            </div>

            <h3 style="font-size:15px;margin:20px 0 6px">Modalidade: {{ $order->delivery_type === 'PICKUP' ? 'Retirada na Farmácia' : 'Entrega ao Domicílio' }}</h3>
            @if ($order->delivery_type === 'PICKUP')
                <p style="color:var(--muted);font-size:13px;line-height:1.5">
                    <strong>Filial de Retirada:</strong><br>
                    {{ $order->branch?->name ?? 'Farmácia Gundja - Unidade Sede' }}<br>
                    {{ $order->branch?->address ?? 'Rua Rainha Ginga, Edifício Gundja, Luanda' }}<br>
                    Tel: {{ $order->branch?->phone ?? '(+244) 923 100 200' }}
                </p>
            @else
                <p style="color:var(--muted);font-size:13px;line-height:1.5">
                    <strong>Endereço de Entrega:</strong><br>
                    {{ $order->address->formattedAddress() }}
                </p>
            @endif

            <h3 style="font-size:15px;margin:20px 0 6px">Pagamento</h3>
            @foreach ($order->payments as $payment)
                <p style="font-size:13px;color:var(--muted);margin-bottom:4px">
                    <strong>{{ $payment->methodLabel() }}</strong><br>
                    Valor: {{ $payment->formattedAmount() }} · Estado: <span style="font-weight:600">{{ str($payment->status)->title() }}</span>
                </p>
            @endforeach

            @if ($order->payment_proof_path)
                <div style="margin-top:10px">
                    <a href="{{ asset('storage/'.$order->payment_proof_path) }}" target="_blank" class="text-link" style="font-size:12px">📄 Ver Comprovativo de Pagamento</a>
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
