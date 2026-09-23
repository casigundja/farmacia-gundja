@extends('admin.layout')
@section('title', $brand->exists ? 'Editar marca' : 'Nova marca')
@section('content')
<div class="admin-head"><div><h1>{{ $brand->exists ? 'Editar marca' : 'Nova marca' }}</h1></div><a class="btn btn-secondary" href="{{ route('admin.brands.index') }}">Voltar</a></div>
<form class="card" method="POST" action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" style="display:grid;gap:15px;max-width:680px">@csrf @if ($brand->exists) @method('PUT') @endif<div class="field"><label for="name">Nome</label><input id="name" name="name" value="{{ old('name', $brand->name) }}" required maxlength="255"></div><label><input type="checkbox" name="active" value="1" @checked(old('active', $brand->exists ? $brand->active : true))> Ativa na vitrine</label><button class="btn" type="submit">Salvar marca</button></form>
@endsection
