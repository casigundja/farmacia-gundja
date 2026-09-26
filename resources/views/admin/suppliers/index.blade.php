@extends('admin.layout')
@section('title', 'Fornecedores')
@section('content')
<div class="admin-head">
    <div>
        <h1>Fornecedores e Distribuidores</h1>
        <p>Gestão de fornecedores de medicamentos, produtos hospitalares e dermocosméticos em Angola.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start">
    <!-- Lista de Fornecedores -->
    <div>
        <section class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fornecedor / Razão Social</th>
                        <th>NIF / Identificação</th>
                        <th>Contato</th>
                        <th>Produtos & Lotes</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>
                                <div><strong>{{ $supplier->name }}</strong></div>
                                @if ($supplier->company_name)
                                    <div class="muted" style="font-size:12px">{{ $supplier->company_name }}</div>
                                @endif
                                @if ($supplier->address)
                                    <div class="muted" style="font-size:11px">{{ $supplier->address }}</div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $supplier->nif ?? 'N/D' }}</strong>
                            </td>
                            <td>
                                <div>{{ $supplier->phone ?? 'Sem telefone' }}</div>
                                @if ($supplier->email)
                                    <div class="muted" style="font-size:12px">{{ $supplier->email }}</div>
                                @endif
                                @if ($supplier->contact_person)
                                    <div class="muted" style="font-size:11px">Resp: {{ $supplier->contact_person }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size:12px">
                                    <span>{{ $supplier->products_count }} produtos</span> ·
                                    <span>{{ $supplier->lots_count }} lotes</span>
                                </div>
                            </td>
                            <td>
                                @if ($supplier->active)
                                    <span class="status">Ativo</span>
                                @else
                                    <span class="status off">Inativo</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-secondary" style="padding:6px 10px;font-size:11px" onclick="editSupplier({{ json_encode($supplier) }})">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">Nenhum fornecedor cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <div style="margin-top:16px">{{ $suppliers->links() }}</div>
    </div>

    <!-- Formulário Fornecedor -->
    <div class="card" id="supplier-card">
        <h2 id="form-title" style="margin-top:0;font-size:18px;margin-bottom:14px">Cadastrar Fornecedor</h2>
        <form id="supplier-form" action="{{ route('admin.suppliers.store') }}" method="POST">
            @csrf
            <div id="method-container"></div>

            <div class="field" style="margin-bottom:12px">
                <label for="name">Nome Comercial / Fantasia *</label>
                <input id="name" name="name" type="text" placeholder="Ex: Angomédica Distribuidora" required>
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="company_name">Razão Social / Empresa</label>
                <input id="company_name" name="company_name" type="text" placeholder="Ex: Angomédica Produtos Farmacêuticos Lda">
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="nif">NIF (Número de Identificação Fiscal)</label>
                <input id="nif" name="nif" type="text" placeholder="Ex: 5418901234">
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="phone">Telefone de Contato</label>
                <input id="phone" name="phone" type="text" placeholder="Ex: +244 923 111 222">
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="email">E-mail Comercial</label>
                <input id="email" name="email" type="email" placeholder="Ex: comercial@angomedica.ao">
            </div>

            <div class="field" style="margin-bottom:12px">
                <label for="contact_person">Pessoa de Contato / Representante</label>
                <input id="contact_person" name="contact_person" type="text" placeholder="Ex: Dr. Manuel dos Santos">
            </div>

            <div class="field" style="margin-bottom:14px">
                <label for="address">Endereço da Empresa</label>
                <input id="address" name="address" type="text" placeholder="Ex: Zona Industrial de Viana, Luanda">
            </div>

            <div class="checks" style="margin-bottom:18px">
                <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
                    <input type="checkbox" id="active" name="active" value="1" checked>
                    Fornecedor ativo para compras e lotes
                </label>
            </div>

            <div style="display:flex;gap:10px">
                <button type="submit" id="submit-btn" class="btn" style="flex:1">Salvar Fornecedor</button>
                <button type="button" id="cancel-btn" class="btn btn-secondary" style="display:none" onclick="resetForm()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSupplier(supplier) {
    document.getElementById('form-title').innerText = 'Editar Fornecedor: ' + supplier.name;
    const form = document.getElementById('supplier-form');
    form.action = '/admin/fornecedores/' + supplier.id;
    document.getElementById('method-container').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    
    document.getElementById('name').value = supplier.name || '';
    document.getElementById('company_name').value = supplier.company_name || '';
    document.getElementById('nif').value = supplier.nif || '';
    document.getElementById('phone').value = supplier.phone || '';
    document.getElementById('email').value = supplier.email || '';
    document.getElementById('contact_person').value = supplier.contact_person || '';
    document.getElementById('address').value = supplier.address || '';
    document.getElementById('active').checked = Boolean(supplier.active);
    
    document.getElementById('submit-btn').innerText = 'Atualizar Fornecedor';
    document.getElementById('cancel-btn').style.display = 'inline-flex';
    document.getElementById('supplier-card').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Cadastrar Fornecedor';
    const form = document.getElementById('supplier-form');
    form.action = '{{ route("admin.suppliers.store") }}';
    document.getElementById('method-container').innerHTML = '';
    form.reset();
    document.getElementById('active').checked = true;
    document.getElementById('submit-btn').innerText = 'Salvar Fornecedor';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>
@endsection
