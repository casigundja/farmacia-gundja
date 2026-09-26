@extends('admin.layout')
@section('title', 'Filiais e Unidades')
@section('content')
<div class="admin-head">
    <div>
        <h1>Filiais da Farmácia Gundja</h1>
        <p>Gestão de unidades físicas, pontos de retirada e estoque distribuído em Angola.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start">
    <!-- Lista de Filiais -->
    <div>
        <section class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código / Nome</th>
                        <th>Localização</th>
                        <th>Contatos & Horário</th>
                        <th>Atividade</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr>
                            <td>
                                <div><strong>{{ $branch->name }}</strong></div>
                                <span class="muted" style="font-size:11px;font-family:monospace;background:#f0e9eb;padding:1px 5px;border-radius:4px">{{ $branch->code }}</span>
                            </td>
                            <td>
                                <div><strong>{{ $branch->municipality }}</strong>, {{ $branch->province }}</div>
                                <div class="muted" style="font-size:12px">{{ $branch->address }}</div>
                                @if ($branch->commune)
                                    <div class="muted" style="font-size:11px">Comuna: {{ $branch->commune }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $branch->phone }}</div>
                                @if ($branch->opening_hours)
                                    <div class="muted" style="font-size:11px">{{ $branch->opening_hours }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size:12px">
                                    <span>{{ $branch->lots_count }} lotes</span> ·
                                    <span>{{ $branch->sales_count }} vendas</span>
                                </div>
                            </td>
                            <td>
                                @if ($branch->active)
                                    <span class="status">Ativa</span>
                                @else
                                    <span class="status off">Inativa</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-secondary" style="padding:6px 10px;font-size:11px" onclick="editBranch({{ json_encode($branch) }})">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">Nenhuma filial cadastrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <div style="margin-top:16px">{{ $branches->links() }}</div>
    </div>

    <!-- Formulário Nova / Editar Filial -->
    <div class="card" id="branch-card">
        <h2 id="form-title" style="margin-top:0;font-size:18px;margin-bottom:14px">Cadastrar Nova Unidade</h2>
        <form id="branch-form" action="{{ route('admin.branches.store') }}" method="POST">
            @csrf
            <div id="method-container"></div>

            <div class="field" style="margin-bottom:12px">
                <label for="name">Nome da Farmácia / Unidade *</label>
                <input id="name" name="name" type="text" placeholder="Ex: Farmácia Gundja - Talatona" required>
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="code">Código Interno (único) *</label>
                <input id="code" name="code" type="text" placeholder="Ex: TALATONA" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
                <div class="field">
                    <label for="province">Província *</label>
                    <input id="province" name="province" type="text" value="Luanda" required>
                </div>
                <div class="field">
                    <label for="municipality">Município *</label>
                    <input id="municipality" name="municipality" type="text" placeholder="Ex: Talatona" required>
                </div>
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="commune">Comuna / Distrito</label>
                <input id="commune" name="commune" type="text" placeholder="Ex: Talatona Centro">
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="address">Endereço completo *</label>
                <input id="address" name="address" type="text" placeholder="Ex: Av. Luanda Sul, Shopping Talatona" required>
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="phone">Telefone / WhatsApp *</label>
                <input id="phone" name="phone" type="text" placeholder="Ex: +244 923 000 000" required>
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="email">E-mail da Filial</label>
                <input id="email" name="email" type="email" placeholder="Ex: talatona@farmaciagundja.ao">
            </div>

            <div class="field" style="margin-bottom:14px">
                <label for="opening_hours">Horário de Funcionamento</label>
                <input id="opening_hours" name="opening_hours" type="text" placeholder="Ex: Seg a Sab: 07h30 às 22h00 | Dom: 08h às 18h">
            </div>

            <div class="checks" style="margin-bottom:18px">
                <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
                    <input type="checkbox" id="active" name="active" value="1" checked>
                    Unidade ativa para operação e retirada
                </label>
            </div>

            <div style="display:flex;gap:10px">
                <button type="submit" id="submit-btn" class="btn" style="flex:1">Salvar Unidade</button>
                <button type="button" id="cancel-btn" class="btn btn-secondary" style="display:none" onclick="resetForm()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editBranch(branch) {
    document.getElementById('form-title').innerText = 'Editar Unidade: ' + branch.name;
    const form = document.getElementById('branch-form');
    form.action = '/admin/filiais/' + branch.id;
    document.getElementById('method-container').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    
    document.getElementById('name').value = branch.name || '';
    document.getElementById('code').value = branch.code || '';
    document.getElementById('province').value = branch.province || '';
    document.getElementById('municipality').value = branch.municipality || '';
    document.getElementById('commune').value = branch.commune || '';
    document.getElementById('address').value = branch.address || '';
    document.getElementById('phone').value = branch.phone || '';
    document.getElementById('email').value = branch.email || '';
    document.getElementById('opening_hours').value = branch.opening_hours || '';
    document.getElementById('active').checked = Boolean(branch.active);
    
    document.getElementById('submit-btn').innerText = 'Atualizar Unidade';
    document.getElementById('cancel-btn').style.display = 'inline-flex';
    document.getElementById('branch-card').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Cadastrar Nova Unidade';
    const form = document.getElementById('branch-form');
    form.action = '{{ route("admin.branches.store") }}';
    document.getElementById('method-container').innerHTML = '';
    form.reset();
    document.getElementById('province').value = 'Luanda';
    document.getElementById('active').checked = true;
    document.getElementById('submit-btn').innerText = 'Salvar Unidade';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>
@endsection
