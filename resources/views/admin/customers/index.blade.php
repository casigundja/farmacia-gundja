@extends('admin.layout')
@section('title', 'Clientes')
@section('content')
<div class="admin-head"><div><h1>Clientes</h1><p>{{ $customers->total() }} cadastro(s) de cliente</p></div></div>
<form method="GET" class="card" style="display:flex;gap:10px;margin-bottom:16px"><input name="search" value="{{ request('search') }}" placeholder="Buscar nome, e-mail, CPF ou telefone" style="flex:1;min-width:0;border:1px solid var(--line);border-radius:8px;padding:10px"><button class="btn">Buscar</button></form>
<div class="table-wrap"><table><thead><tr><th>Cliente</th><th>Contato</th><th>Cadastro</th><th>Pedidos</th><th></th></tr></thead><tbody>
@forelse ($customers as $customer)<tr><td><strong>{{ $customer->user->name }}</strong><div class="muted">{{ $customer->cpf ?: 'CPF não informado' }}</div></td><td>{{ $customer->user->email }}<div class="muted">{{ $customer->phone ?: 'Telefone não informado' }}</div></td><td>{{ $customer->created_at->format('d/m/Y') }}</td><td>{{ $customer->orders_count }}</td><td><a class="btn btn-secondary" href="{{ route('admin.customers.show', $customer) }}">Detalhes</a></td></tr>@empty<tr><td colspan="5" class="muted">Nenhum cliente encontrado.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:18px">{{ $customers->links() }}</div>
@endsection
