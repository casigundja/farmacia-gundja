@extends('admin.layout')
@section('title', 'Vendas')
@section('content')
<div class="admin-head">
    <div><h1>Vendas presenciais</h1><p>Histórico das vendas registradas no balcão.</p></div>
    @if (auth()->user()->hasPermission('sales.manage'))<a class="btn" href="{{ route('admin.sales.create') }}">+ Nova venda</a>@endif
</div>
@if ($errors->any())<div class="notice" style="margin-bottom:16px;background:#fff0ed;color:#a23d2c;border-color:#f0cfc8">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@if (session('success'))<div class="notice" style="margin-bottom:16px">{{ session('success') }}</div>@endif
<section class="table-wrap"><table>
    <thead><tr><th>Venda</th><th>Data</th><th>Funcionário</th><th>Cliente</th><th>Pagamento</th><th>Total</th><th>Status / ações</th></tr></thead>
    <tbody>
        @forelse ($sales as $sale)
            <tr>
                <td><strong>{{ $sale->sale_number }}</strong></td>
                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $sale->employee->user->name }}</td>
                <td>{{ $sale->customer?->user?->name ?? 'Não identificado' }}</td>
                <td>@foreach ($sale->payments as $payment)<div>{{ str($payment->method)->replace('_', ' ')->title() }} · {{ str($payment->status)->title() }}</div>@endforeach</td>
                <td>R$ {{ number_format((float) $sale->total, 2, ',', '.') }}</td>
                <td>
                    <span class="status">{{ str($sale->status)->title() }}</span>
                    @if ($sale->status === 'COMPLETED' && auth()->user()->hasPermission('sales.manage'))
                        <form action="{{ route('admin.sales.cancel', $sale) }}" method="POST" style="display:flex;gap:6px;min-width:260px;margin-top:8px" onsubmit="return confirm('Confirma o cancelamento desta venda? O estorno do pagamento deverá ser feito fora do sistema.')">
                            @csrf
                            <input name="reason" required maxlength="255" placeholder="Motivo do cancelamento" aria-label="Motivo do cancelamento" style="min-width:0;padding:7px;border:1px solid var(--line);border-radius:8px">
                            <button class="btn btn-secondary" type="submit">Cancelar</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">Nenhuma venda registrada.</td></tr>
        @endforelse
    </tbody>
</table></section>
<div style="margin-top:20px">{{ $sales->links() }}</div>
@endsection
