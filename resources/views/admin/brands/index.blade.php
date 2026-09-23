@extends('admin.layout')
@section('title', 'Marcas')
@section('content')
<div class="admin-head"><div><h1>Marcas</h1><p>Gerencie as marcas exibidas no catálogo.</p></div>@if (auth()->user()->hasPermission('brands.manage'))<a class="btn" href="{{ route('admin.brands.create') }}">+ Nova marca</a>@endif</div>
<section class="table-wrap"><table><thead><tr><th>Marca</th><th>Produtos</th><th>Status</th>@if (auth()->user()->hasPermission('brands.manage'))<th>Ação</th>@endif</tr></thead><tbody>
@forelse ($brands as $brand)<tr><td><strong>{{ $brand->name }}</strong><div class="muted" style="font-size:12px">{{ $brand->slug }}</div></td><td>{{ $brand->products_count }}</td><td><span class="status {{ $brand->active ? '' : 'off' }}">{{ $brand->active ? 'Ativa' : 'Inativa' }}</span></td>@if (auth()->user()->hasPermission('brands.manage'))<td><a class="btn btn-secondary" href="{{ route('admin.brands.edit', $brand) }}">Editar</a></td>@endif</tr>@empty<tr><td colspan="4" class="muted">Nenhuma marca cadastrada.</td></tr>@endforelse
</tbody></table></section><div style="margin-top:20px">{{ $brands->links() }}</div>
@endsection
