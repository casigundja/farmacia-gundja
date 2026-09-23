@extends('admin.layout')
@section('title', $category->exists ? 'Editar categoria' : 'Nova categoria')
@section('content')
<div class="admin-head"><div><h1>{{ $category->exists ? 'Editar categoria' : 'Nova categoria' }}</h1></div><a class="btn btn-secondary" href="{{ route('admin.categories.index') }}">Voltar</a></div>
<form class="card" method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" style="display:grid;gap:15px;max-width:680px">@csrf @if ($category->exists) @method('PUT') @endif<div class="field"><label for="name">Nome</label><input id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="255"></div><div class="field"><label for="description">Descrição</label><textarea id="description" name="description">{{ old('description', $category->description) }}</textarea></div><label><input type="checkbox" name="active" value="1" @checked(old('active', $category->exists ? $category->active : true))> Ativa na vitrine</label><button class="btn" type="submit">Salvar categoria</button></form>
@endsection
