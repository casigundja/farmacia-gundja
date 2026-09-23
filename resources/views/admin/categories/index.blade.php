@extends('admin.layout')
@section('title', 'Categorias')
@section('content')
<div class="admin-head"><div><h1>Categorias</h1><p>Organize os produtos da vitrine.</p></div>@if (auth()->user()->hasPermission('categories.manage'))<a class="btn" href="{{ route('admin.categories.create') }}">+ Nova categoria</a>@endif</div>
<section class="table-wrap"><table><thead><tr><th>Categoria</th><th>Produtos</th><th>Status</th>@if (auth()->user()->hasPermission('categories.manage'))<th>Ação</th>@endif</tr></thead><tbody>
@forelse ($categories as $category)<tr><td><strong>{{ $category->name }}</strong><div class="muted" style="font-size:12px">{{ $category->slug }}</div></td><td>{{ $category->products_count }}</td><td><span class="status {{ $category->active ? '' : 'off' }}">{{ $category->active ? 'Ativa' : 'Inativa' }}</span></td>@if (auth()->user()->hasPermission('categories.manage'))<td><a class="btn btn-secondary" href="{{ route('admin.categories.edit', $category) }}">Editar</a></td>@endif</tr>@empty<tr><td colspan="4" class="muted">Nenhuma categoria cadastrada.</td></tr>@endforelse
</tbody></table></section><div style="margin-top:20px">{{ $categories->links() }}</div>
@endsection
