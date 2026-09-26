@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')
<div class="admin-head">
    <div>
        <h1>Visão Geral da Farmácia Gundja</h1>
        <p>Acompanhamento operacional em tempo real · Mercado Angolano (Kz).</p>
    </div>
</div>

@if ($pendingPrescriptions > 0)
    <div style="background:#fff8e6;border:1px solid #fed7aa;border-left:5px solid #d97706;border-radius:10px;padding:14px 18px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <div>
            <strong style="color:#92400e;font-size:15px">Atenção Farmacêutica Requerida</strong>
            <p style="margin:2px 0 0;color:#b45309;font-size:13px">
                Existem <strong>{{ $pendingPrescriptions }}</strong> receita(s) médica(s) aguardando avaliação profissional para liberação de pedidos.
            </p>
        </div>
        <a href="{{ route('admin.prescriptions.index', ['status' => 'PENDING']) }}" class="btn" style="background:#d97706;font-size:12px;padding:8px 14px">
            Revisar Receitas Agora →
        </a>
    </div>
@endif

<div class="cards" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr))">
    <div class="metric">
        <span>Vendas hoje (Balcão)</span>
        <strong>{{ number_format((float) $salesTotal, 2, ',', '.') }} Kz</strong>
    </div>
    <div class="metric">
        <span>Pedidos online pendentes</span>
        <strong>{{ $ordersPending }}</strong>
    </div>
    <div class="metric" style="{{ $pendingPrescriptions > 0 ? 'border-color:#fed7aa;' : '' }}">
        <span>Receitas para validar</span>
        <strong style="{{ $pendingPrescriptions > 0 ? 'color:#d97706;' : '' }}">{{ $pendingPrescriptions }}</strong>
    </div>
    <div class="metric">
        <span>Caixas abertos hoje</span>
        <strong>{{ $openCashRegisters }}</strong>
    </div>
    <div class="metric">
        <span>Clientes cadastrados</span>
        <strong>{{ number_format($customersCount, 0, ',', '.') }}</strong>
    </div>
    <div class="metric">
        <span>Lotes a vencer (60 dias)</span>
        <strong style="{{ $expiringLotsCount > 0 ? 'color:#a23d2c;' : '' }}">{{ $expiringLotsCount }}</strong>
    </div>
</div>

<section class="table-wrap">
    <table>
        <thead>
            <tr>
                <th colspan="4" style="font-size:13px;font-weight:bold;color:var(--wine)">Produtos com Alerta de Estoque Mínimo</th>
            </tr>
            <tr>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Estoque Atual</th>
                <th>Estoque Mínimo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lowStockProducts as $product)
                <tr>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        @if ($product->dosage || $product->pharmaceutical_form)
                            <span class="muted" style="font-size:12px">({{ $product->dosage }} - {{ $product->pharmaceutical_form }})</span>
                        @endif
                    </td>
                    <td>{{ $product->category->name }}</td>
                    <td>
                        <strong style="color:{{ ($product->stock_quantity ?? 0) <= 0 ? '#a23d2c' : '#d97706' }}">
                            {{ $product->stock_quantity ?? 0 }} un.
                        </strong>
                    </td>
                    <td>{{ $product->minimum_stock }} un.</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">Nenhum produto abaixo do estoque mínimo no momento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
