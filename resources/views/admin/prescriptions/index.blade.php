@extends('admin.layout')
@section('title', 'Receitas Médicas')
@section('content')
<div class="admin-head">
    <div>
        <h1>Receitas Médicas</h1>
        <p>Validação farmacêutica de prescrições e cumprimento das normas sanitárias de Angola.</p>
    </div>
</div>

<div style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap">
    <a href="{{ route('admin.prescriptions.index', ['status' => 'PENDING']) }}" class="btn {{ $status === 'PENDING' ? '' : 'btn-secondary' }}" style="font-size:13px;padding:8px 14px">
        Pendentes @if ($pendingCount > 0) <span style="background:white;color:var(--wine);padding:2px 7px;border-radius:999px;font-size:11px;margin-left:6px;font-weight:700">{{ $pendingCount }}</span> @endif
    </a>
    <a href="{{ route('admin.prescriptions.index', ['status' => 'APPROVED']) }}" class="btn {{ $status === 'APPROVED' ? '' : 'btn-secondary' }}" style="font-size:13px;padding:8px 14px">
        Aprovadas
    </a>
    <a href="{{ route('admin.prescriptions.index', ['status' => 'REJECTED']) }}" class="btn {{ $status === 'REJECTED' ? '' : 'btn-secondary' }}" style="font-size:13px;padding:8px 14px">
        Rejeitadas
    </a>
    <a href="{{ route('admin.prescriptions.index', ['status' => '']) }}" class="btn {{ empty($status) ? '' : 'btn-secondary' }}" style="font-size:13px;padding:8px 14px">
        Todas as receitas
    </a>
</div>

<section class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Protocolo</th>
                <th>Cliente / Paciente</th>
                <th>Médico prescritor</th>
                <th>Pedido</th>
                <th>Data envio</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($prescriptions as $prescription)
                <tr>
                    <td>
                        <strong>REC-{{ str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}</strong>
                    </td>
                    <td>
                        <div><strong>{{ $prescription->patient_name ?? $prescription->customer->user->name }}</strong></div>
                        <div class="muted" style="font-size:12px">
                            {{ $prescription->customer->user->phone ?? 'Sem tel.' }}
                            @if ($prescription->customer->nif_bi) · BI: {{ $prescription->customer->nif_bi }} @endif
                        </div>
                    </td>
                    <td>
                        @if ($prescription->doctor_name)
                            <div>{{ $prescription->doctor_name }}</div>
                            <div class="muted" style="font-size:12px">Reg: {{ $prescription->doctor_crm ?? 'N/D' }}</div>
                        @else
                            <span class="muted">Não informado</span>
                        @endif
                    </td>
                    <td>
                        @if ($prescription->order)
                            <a href="{{ route('admin.orders.index', ['q' => $prescription->order->order_number]) }}" style="color:var(--wine);font-weight:bold;text-decoration:underline">
                                {{ $prescription->order->order_number }}
                            </a>
                        @else
                            <span class="muted">Sem pedido vinculado</span>
                        @endif
                    </td>
                    <td>{{ $prescription->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if ($prescription->status === 'PENDING')
                            <span class="status" style="background:#fff8e6;color:#8f6300;font-weight:bold">Aguardando análise</span>
                        @elseif ($prescription->status === 'APPROVED')
                            <span class="status">Aprovada</span>
                            @if ($prescription->reviewer)
                                <div class="muted" style="font-size:11px;margin-top:3px">Por {{ $prescription->reviewer->name }}</div>
                            @endif
                        @elseif ($prescription->status === 'REJECTED')
                            <span class="status off">Rejeitada</span>
                            @if ($prescription->reviewer)
                                <div class="muted" style="font-size:11px;margin-top:3px">Por {{ $prescription->reviewer->name }}</div>
                            @endif
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.prescriptions.show', $prescription) }}" class="btn btn-secondary" style="padding:6px 12px;font-size:12px">
                            {{ $prescription->isPending() ? 'Avaliar Receita' : 'Ver Detalhes' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">Nenhuma receita médica encontrada nesta listagem.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

<div style="margin-top:20px">
    {{ $prescriptions->links() }}
</div>
@endsection
