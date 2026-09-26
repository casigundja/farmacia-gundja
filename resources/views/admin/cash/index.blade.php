@extends('admin.layout')
@section('title', 'Controle de Caixa')
@section('content')
<div class="admin-head">
    <div>
        <h1>Gestão de Caixa</h1>
        <p>Abertura de turno, movimentações de balcão (sangrias e suprimentos) e conferência de fechamento.</p>
    </div>
</div>

@if ($openRegister)
    <!-- Caixa Ativo Aberto -->
    <div style="background:#eaf4ee;border:1px solid #c2e2cf;border-radius:12px;padding:16px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <div>
            <div style="display:flex;align-items:center;gap:10px">
                <span class="status" style="background:#2f7253;color:white;font-weight:bold">CAIXA ABERTO</span>
                <strong style="font-size:16px">{{ $openRegister->branch->name }}</strong>
            </div>
            <div class="muted" style="font-size:13px;margin-top:4px">
                Aberto em {{ $openRegister->opened_at->format('d/m/Y \à\s H:i') }} por {{ auth()->user()->name }}
            </div>
        </div>
        <div style="text-align:right">
            <span class="muted" style="font-size:12px">Saldo esperado em dinheiro no caixa:</span>
            <div style="font-size:24px;font-weight:bold;color:var(--wine)">
                {{ number_format($expectedBalance, 2, ',', '.') }} Kz
            </div>
        </div>
    </div>

    <!-- Indicadores do Caixa Aberto -->
    <div class="cards" style="margin-bottom:24px">
        <div class="metric">
            <span>Fundo de troco inicial</span>
            <strong>{{ number_format($openRegister->opening_balance, 2, ',', '.') }} Kz</strong>
        </div>
        <div class="metric">
            <span>Vendas em dinheiro</span>
            <strong>{{ number_format((float) $openRegister->movements()->where('type', 'SALE')->sum('amount'), 2, ',', '.') }} Kz</strong>
        </div>
        <div class="metric">
            <span>Suprimentos (entradas)</span>
            <strong style="color:#2f7253">+{{ number_format((float) $openRegister->movements()->where('type', 'SUPPLEMENT')->sum('amount'), 2, ',', '.') }} Kz</strong>
        </div>
        <div class="metric">
            <span>Sangrias (retiradas)</span>
            <strong style="color:#a23d2c">-{{ number_format((float) $openRegister->movements()->where('type', 'BLEED')->sum('amount'), 2, ',', '.') }} Kz</strong>
        </div>
    </div>

    <!-- Ações: Registrar Movimento & Fechamento -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px">
        <!-- Registrar Movimento -->
        <div class="card">
            <h3 style="margin-top:0;font-size:16px;margin-bottom:14px">Registrar Sangria ou Suprimento</h3>
            <form action="{{ route('admin.cash.movement', $openRegister) }}" method="POST">
                @csrf
                <div class="field" style="margin-bottom:12px">
                    <label for="type">Tipo de movimentação</label>
                    <select id="type" name="type" required>
                        <option value="BLEED">Sangria (Retirada de valor do caixa)</option>
                        <option value="SUPPLEMENT">Suprimento (Entrada / Troco adicional)</option>
                    </select>
                </div>
                <div class="field" style="margin-bottom:12px">
                    <label for="amount">Valor (Kz)</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" placeholder="Ex: 5000.00" required>
                </div>
                <div class="field" style="margin-bottom:14px">
                    <label for="reason">Motivo / Justificativa</label>
                    <input id="reason" name="reason" type="text" placeholder="Ex: Retirada de segurança ou reforço de moedas" required>
                </div>
                <button type="submit" class="btn btn-secondary" style="width:100%">Lançar Movimento</button>
            </form>
        </div>

        <!-- Fechar Caixa -->
        <div class="card" style="border-left:4px solid var(--wine)">
            <h3 style="margin-top:0;font-size:16px;margin-bottom:14px">Fechamento do Turno</h3>
            <p class="muted" style="font-size:13px;margin-bottom:14px">
                Conte as notas e moedas presentes fisicamente na gaveta do caixa e informe o valor total apurado. O sistema fará a conferência com o saldo contábil.
            </p>
            <form action="{{ route('admin.cash.close', $openRegister) }}" method="POST" onsubmit="return confirm('Confirma o fechamento deste caixa? Esta ação encerra o turno atual.')">
                @csrf
                <div class="field" style="margin-bottom:12px">
                    <label for="closing_balance_physical">Saldo Físico em Dinheiro (Kz)</label>
                    <input id="closing_balance_physical" name="closing_balance_physical" type="number" step="0.01" min="0" placeholder="Ex: {{ number_format($expectedBalance, 2, '.', '') }}" required>
                </div>
                <div class="field" style="margin-bottom:14px">
                    <label for="notes">Observações do Fechamento</label>
                    <input id="notes" name="notes" type="text" placeholder="Ex: Turno sem divergências ou motivo de quebra">
                </div>
                <button type="submit" class="btn" style="width:100%;background:#a23d2c">Encerrar Caixa</button>
            </form>
        </div>
    </div>

    <!-- Movimentações do Caixa Atual -->
    <div class="admin-head" style="margin-bottom:12px">
        <div><h2 style="margin:0;font-size:18px">Movimentações do caixa em aberto</h2></div>
    </div>
    <section class="table-wrap" style="margin-bottom:34px">
        <table>
            <thead>
                <tr>
                    <th>Horário</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Forma</th>
                    <th>Motivo / Descrição</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($openRegister->movements()->latest()->get() as $mov)
                    <tr>
                        <td>{{ $mov->created_at->format('H:i:s') }}</td>
                        <td>
                            @if ($mov->type === 'OPENING')
                                <span class="status">Abertura</span>
                            @elseif ($mov->type === 'SALE')
                                <span class="status" style="background:#e8f4fd;color:#1e6aa8">Venda Balcão</span>
                            @elseif ($mov->type === 'SUPPLEMENT')
                                <span class="status" style="background:#eaf4ee;color:#2f7253">+ Suprimento</span>
                            @elseif ($mov->type === 'BLEED')
                                <span class="status off">- Sangria</span>
                            @endif
                        </td>
                        <td><strong>{{ number_format($mov->amount, 2, ',', '.') }} Kz</strong></td>
                        <td>{{ $mov->payment_method }}</td>
                        <td>{{ $mov->reason }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Nenhuma movimentação até o momento.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

@else
    <!-- Nenhum Caixa Aberto: Formulário de Abertura -->
    <div class="card" style="max-width:650px;margin:0 auto 34px;border-top:4px solid var(--wine)">
        <h2 style="margin-top:0;font-size:20px;margin-bottom:6px">Abrir Novo Turno de Caixa</h2>
        <p class="muted" style="margin-bottom:20px">Inicie o seu atendimento de balcão informando a unidade e o fundo de troco inicial.</p>

        <form action="{{ route('admin.cash.open') }}" method="POST">
            @csrf
            <div class="field" style="margin-bottom:14px">
                <label for="branch_id">Filial / Unidade de Operação</label>
                <select id="branch_id" name="branch_id" required>
                    <option value="">Selecione a unidade...</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->municipality }})</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label for="opening_balance">Fundo de Troco Inicial (Kz)</label>
                <input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" value="0.00" required>
            </div>

            <div class="field" style="margin-bottom:18px">
                <label for="open_notes">Observações iniciais (opcional)</label>
                <input id="open_notes" name="notes" type="text" placeholder="Ex: Turno da manhã, gaveta 1">
            </div>

            <button type="submit" class="btn" style="width:100%;font-size:15px;padding:12px">
                Confirmar e Abrir Caixa
            </button>
        </form>
    </div>
@endif

<!-- Histórico de Caixas Fechados -->
<div class="admin-head" style="margin-top:30px">
    <div>
        <h2 style="margin:0;font-size:20px">Histórico de Fechamentos</h2>
        <p class="muted" style="font-size:13px">Conferência dos turnos concluídos e conciliação de diferenças.</p>
    </div>
</div>

<section class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Filial</th>
                <th>Operador</th>
                <th>Abertura</th>
                <th>Fechamento</th>
                <th>Fundo Inicial</th>
                <th>Saldo Sistema</th>
                <th>Saldo Físico</th>
                <th>Diferença</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($history as $closed)
                <tr>
                    <td><strong>{{ $closed->branch->name }}</strong></td>
                    <td>{{ $closed->user->name }}</td>
                    <td>{{ $closed->opened_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $closed->closed_at ? $closed->closed_at->format('d/m/Y H:i') : '—' }}</td>
                    <td>{{ number_format($closed->opening_balance, 2, ',', '.') }} Kz</td>
                    <td>{{ number_format($closed->closing_balance_system ?? 0, 2, ',', '.') }} Kz</td>
                    <td><strong>{{ number_format($closed->closing_balance_physical ?? 0, 2, ',', '.') }} Kz</strong></td>
                    <td>
                        @php($diff = (float) ($closed->difference ?? 0))
                        @if ($diff == 0)
                            <span class="status" style="background:#eaf4ee;color:#2f7253">Sem diferença</span>
                        @elseif ($diff > 0)
                            <span class="status" style="background:#e8f4fd;color:#1e6aa8">+{{ number_format($diff, 2, ',', '.') }} Kz (sobra)</span>
                        @else
                            <span class="status off">{{ number_format($diff, 2, ',', '.') }} Kz (quebra)</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Nenhum histórico de fechamento de caixa registrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

<div style="margin-top:20px">
    {{ $history->links() }}
</div>
@endsection
