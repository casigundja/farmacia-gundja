@extends('admin.layout')
@section('title', 'Avaliação da Receita Médica')
@section('content')
<div class="admin-head">
    <div>
        <h1>Receita REC-{{ str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}</h1>
        <p>Conferência farmacêutica e decisão sanitária.</p>
    </div>
    <a href="{{ route('admin.prescriptions.index') }}" class="btn btn-secondary">← Voltar às receitas</a>
</div>

<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:24px;align-items:start">
    <!-- Lado Esquerdo: Imagem / Documento da Receita -->
    <div class="card">
        <h2 style="margin-top:0;font-size:18px;margin-bottom:14px">Documento da Receita</h2>
        @php
            $fileUrl = asset('storage/' . $prescription->file_path);
            $extension = strtolower(pathinfo($prescription->file_path, PATHINFO_EXTENSION));
        @endphp

        <div style="background:#f4ecef;border-radius:10px;padding:12px;text-align:center;min-height:380px;display:flex;flex-direction:column;align-items:center;justify-content:center">
            @if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp']))
                <a href="{{ $fileUrl }}" target="_blank" title="Clique para ampliar">
                    <img src="{{ $fileUrl }}" alt="Receita Médica" style="max-width:100%;max-height:550px;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,0.1)">
                </a>
                <p class="muted" style="margin-top:10px;font-size:12px">Clique na imagem para abrir em tamanho original</p>
            @elseif ($extension === 'pdf')
                <div style="width:100%">
                    <iframe src="{{ $fileUrl }}" style="width:100%;height:520px;border:none;border-radius:8px"></iframe>
                    <div style="margin-top:10px">
                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-secondary" style="font-size:12px">Abrir PDF em nova aba ↗</a>
                    </div>
                </div>
            @else
                <div style="padding:40px">
                    <p>Arquivo anexado: <strong>{{ basename($prescription->file_path) }}</strong></p>
                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-secondary">Descarregar documento</a>
                </div>
            @endif
        </div>

        <div style="margin-top:18px;display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px">
            <div>
                <span class="muted">Paciente / Titular:</span>
                <div><strong>{{ $prescription->patient_name ?? $prescription->customer->user->name }}</strong></div>
            </div>
            <div>
                <span class="muted">Data do Envio:</span>
                <div>{{ $prescription->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <span class="muted">Médico Prescritor:</span>
                <div><strong>{{ $prescription->doctor_name ?? 'Não especificado' }}</strong></div>
            </div>
            <div>
                <span class="muted">Nº de Registo / CRM:</span>
                <div>{{ $prescription->doctor_crm ?? 'Não informado' }}</div>
            </div>
        </div>
    </div>

    <!-- Lado Direito: Pedido, Cliente e Ação do Farmacêutico -->
    <div style="display:flex;flex-direction:column;gap:20px">
        <!-- Status Atual & Ação -->
        <div class="card" style="border-left:5px solid {{ $prescription->status === 'APPROVED' ? '#2f7253' : ($prescription->status === 'REJECTED' ? '#a23d2c' : '#b8860b') }}">
            <h3 style="margin-top:0;font-size:16px;margin-bottom:10px">Decisão Farmacêutica</h3>
            
            <div style="margin-bottom:14px">
                <span class="muted">Estado atual: </span>
                @if ($prescription->status === 'PENDING')
                    <span class="status" style="background:#fff8e6;color:#8f6300;font-weight:bold">Aguardando Avaliação</span>
                @elseif ($prescription->status === 'APPROVED')
                    <span class="status">Aprovada</span>
                @elseif ($prescription->status === 'REJECTED')
                    <span class="status off">Rejeitada</span>
                @endif
            </div>

            @if ($prescription->reviewed_at)
                <div style="font-size:13px;background:#fcf8f9;padding:12px;border-radius:8px;margin-bottom:14px">
                    <div><strong>Avaliador:</strong> {{ $prescription->reviewer?->name ?? 'Farmacêutico' }}</div>
                    <div><strong>Data:</strong> {{ $prescription->reviewed_at->format('d/m/Y H:i') }}</div>
                    @if ($prescription->review_notes)
                        <div style="margin-top:6px"><strong>Parecer técnico:</strong> {{ $prescription->review_notes }}</div>
                    @endif
                </div>
            @endif

            @if (auth()->user()->hasPermission('prescriptions.manage'))
                <form action="{{ route('admin.prescriptions.review', $prescription) }}" method="POST" style="margin-top:10px">
                    @csrf
                    <div style="margin-bottom:14px">
                        <label style="display:block;font-size:12px;font-weight:bold;margin-bottom:6px">Decisão do Profissional:</label>
                        <div style="display:flex;gap:18px">
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                                <input type="radio" name="status" value="APPROVED" {{ old('status', $prescription->status) === 'APPROVED' ? 'checked' : '' }} required>
                                <span style="font-weight:bold;color:#2f7253">Aprovar Receita</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                                <input type="radio" name="status" value="REJECTED" {{ old('status', $prescription->status) === 'REJECTED' ? 'checked' : '' }}>
                                <span style="font-weight:bold;color:#a23d2c">Rejeitar Receita</span>
                            </label>
                        </div>
                    </div>

                    <div style="margin-bottom:14px">
                        <label for="review_notes" style="display:block;font-size:12px;font-weight:bold;margin-bottom:6px">
                            Observações / Justificativa Técnica:
                        </label>
                        <textarea id="review_notes" name="review_notes" rows="3" placeholder="Indique a dosagem validada, posologia ou motivo em caso de recusa..." style="width:100%;border:1px solid var(--line);border-radius:8px;padding:8px;box-sizing:border-box">{{ old('review_notes', $prescription->review_notes) }}</textarea>
                    </div>

                    <button type="submit" class="btn" style="width:100%">
                        {{ $prescription->isPending() ? 'Concluir Avaliação' : 'Atualizar Avaliação' }}
                    </button>
                </form>
            @endif
        </div>

        <!-- Pedido Vinculado -->
        @if ($prescription->order)
            <div class="card">
                <h3 style="margin-top:0;font-size:16px;margin-bottom:12px;display:flex;justify-content:space-between">
                    <span>Pedido {{ $prescription->order->order_number }}</span>
                    <span class="status">{{ $prescription->order->statusLabel() }}</span>
                </h3>
                <div style="font-size:13px;margin-bottom:10px">
                    <span class="muted">Total do pedido:</span> <strong>{{ $prescription->order->formattedTotal() }}</strong>
                </div>

                <div style="border-top:1px solid var(--line);padding-top:10px">
                    <span class="muted" style="font-size:12px;text-transform:uppercase;font-weight:bold">Itens solicitados:</span>
                    <ul style="list-style:none;padding-left:0;margin:8px 0 0;font-size:13px">
                        @foreach ($prescription->order->items as $item)
                            <li style="padding:6px 0;border-bottom:1px solid #f4ecef;display:flex;justify-content:space-between">
                                <div>
                                    <strong>{{ $item->product->name }}</strong>
                                    @if ($item->product->dosage || $item->product->pharmaceutical_form)
                                        <div class="muted" style="font-size:11px">{{ $item->product->pharmaceutical_form }} {{ $item->product->dosage }}</div>
                                    @endif
                                    @if ($item->product->requires_prescription)
                                        <span style="font-size:10px;background:#fde8e8;color:#9b1c1c;padding:1px 5px;border-radius:4px;font-weight:bold">Requer Receita</span>
                                    @endif
                                </div>
                                <div style="text-align:right">
                                    <div>{{ $item->quantity }} un.</div>
                                    <div class="muted">{{ number_format($item->unit_price, 2, ',', '.') }} Kz</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Cliente -->
        <div class="card">
            <h3 style="margin-top:0;font-size:16px;margin-bottom:10px">Dados do Cliente</h3>
            <div style="font-size:13px;line-height:1.6">
                <div><strong>Nome:</strong> {{ $prescription->customer->user->name }}</div>
                <div><strong>E-mail:</strong> {{ $prescription->customer->user->email }}</div>
                <div><strong>Telefone:</strong> {{ $prescription->customer->user->phone ?? 'Não informado' }}</div>
                <div><strong>BI / NIF:</strong> {{ $prescription->customer->nif_bi ?? 'Não cadastrado' }}</div>
                @if ($prescription->customer->province || $prescription->customer->municipality)
                    <div style="margin-top:6px">
                        <strong>Localização:</strong> {{ $prescription->customer->municipality }} - {{ $prescription->customer->province }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
