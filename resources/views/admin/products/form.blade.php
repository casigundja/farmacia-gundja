@extends('admin.layout')
@section('title', $product->exists ? 'Editar produto' : 'Novo produto')
@section('content')
<div class="admin-head"><div><h1>{{ $product->exists ? 'Editar produto' : 'Novo produto' }}</h1><p>Preencha as informações do catálogo.</p></div><a class="btn btn-secondary" href="{{ route('admin.products.index') }}">Voltar</a></div>
<form class="card admin-form" method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf @if ($product->exists) @method('PUT') @endif
    <div class="field"><label for="name">Nome do produto</label><input id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255"></div>
    <div class="field"><label for="internal_code">Código interno</label><input id="internal_code" name="internal_code" value="{{ old('internal_code', $product->internal_code) }}" required maxlength="255"></div>
    <div class="field"><label for="barcode">Código de barras</label><input id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}" maxlength="255"></div>
    <div class="field"><label for="product_type">Tipo</label><select id="product_type" name="product_type" required>@foreach (['MEDICAMENTO' => 'Medicamento', 'HIGIENE' => 'Higiene', 'COSMETICO' => 'Cosmético', 'PERFUMARIA' => 'Perfumaria', 'SUPLEMENTO' => 'Suplemento', 'BEBE' => 'Bebê', 'OUTRO' => 'Outro'] as $value => $label)<option value="{{ $value }}" @selected(old('product_type', $product->product_type) === $value)>{{ $label }}</option>@endforeach</select></div>
    <div class="field"><label for="category_id">Categoria</label><select id="category_id" name="category_id" required><option value="">Selecione</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
    <div class="field"><label for="brand_id">Marca</label><select id="brand_id" name="brand_id"><option value="">Sem marca</option>@foreach ($brands as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
    <div class="field"><label for="cost_price">Preço de custo</label><input id="cost_price" name="cost_price" type="number" min="0" step="0.01" value="{{ old('cost_price', $product->cost_price ?? '0.00') }}" required></div>
    <div class="field"><label for="sale_price">Preço de venda</label><input id="sale_price" name="sale_price" type="number" min="0" step="0.01" value="{{ old('sale_price', $product->sale_price) }}" required></div>
    <div class="field"><label for="minimum_stock">Estoque mínimo</label><input id="minimum_stock" name="minimum_stock" type="number" min="0" value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}" required></div>
    <div class="field full"><label for="description">Descrição</label><textarea id="description" name="description">{{ old('description', $product->description) }}</textarea></div>
    <div class="field full"><label for="images">Imagens do produto</label><input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple><small class="muted">JPG, PNG ou WebP; até 3 imagens por envio, 2 MB cada. Imagens novas só substituem a principal se a opção abaixo for marcada.</small></div>
    @if ($product->exists && $images->isNotEmpty())<div class="field full"><label><input type="checkbox" name="set_primary" value="1" @checked(old('set_primary'))> Definir a primeira imagem enviada como principal</label></div>@endif
    <div class="field full checks"><label><input type="checkbox" name="requires_prescription" value="1" @checked(old('requires_prescription', $product->requires_prescription))> Requer receita</label><label><input type="checkbox" name="controlled" value="1" @checked(old('controlled', $product->controlled))> Controle específico</label><label><input type="checkbox" name="active" value="1" @checked(old('active', $product->exists ? $product->active : true))> Ativo na vitrine</label></div>
    <div class="field full"><button class="btn" type="submit">{{ $product->exists ? 'Salvar alterações' : 'Cadastrar produto' }}</button></div>
</form>
@if ($product->exists && $images->isNotEmpty())
<section style="margin-top:22px"><h2>Imagens cadastradas</h2><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px">
    @foreach ($images as $image)
        <article class="card" style="padding:12px"><img src="{{ asset('storage/'.$image->path) }}" alt="Imagem de {{ $product->name }}" style="width:100%;height:130px;object-fit:cover;border-radius:8px"><p style="margin:9px 0;font-weight:700">{{ $image->is_primary ? 'Imagem principal' : 'Imagem adicional' }}</p>
            <div style="display:flex;gap:8px">
                @unless ($image->is_primary)<form method="POST" action="{{ route('admin.products.images.primary', [$product, $image]) }}">@csrf @method('PATCH')<button class="btn btn-secondary" type="submit">Tornar principal</button></form>@endunless
                <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" onsubmit="return confirm('Remover esta imagem?')">@csrf @method('DELETE')<button class="btn btn-secondary" type="submit">Remover</button></form>
            </div>
        </article>
    @endforeach
</div></section>
@endif
@endsection
