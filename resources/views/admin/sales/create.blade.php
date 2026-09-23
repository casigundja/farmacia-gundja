@extends('admin.layout')
@section('title', 'Nova venda')
@section('content')
<div class="admin-head"><div><h1>Nova venda</h1><p>Selecione os produtos e confirme o pagamento.</p></div><a class="btn btn-secondary" href="{{ route('admin.sales.index') }}">Voltar</a></div>
<form class="card" method="POST" action="{{ route('admin.sales.store') }}" id="sale-form">@csrf
    <div id="sale-items" style="display:grid;gap:10px;margin-bottom:18px"></div>
    <button class="btn btn-secondary" type="button" id="add-sale-item">+ Adicionar produto</button>
    <div class="admin-form" style="margin-top:20px"><div class="field"><label for="customer_id">Cliente (opcional)</label><select id="customer_id" name="customer_id"><option value="">Não identificar cliente</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->user->name }}</option>@endforeach</select></div><div class="field"><label for="discount">Desconto (R$)</label><input id="discount" name="discount" type="number" min="0" step="0.01" value="0"></div><div class="field"><label for="payment_method">Pagamento</label><select id="payment_method" name="payment_method" required>@foreach (['CASH' => 'Dinheiro', 'PIX' => 'PIX', 'DEBIT_CARD' => 'Débito', 'CREDIT_CARD' => 'Crédito', 'OTHER' => 'Outro'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div><div class="field" style="display:flex;align-items:end"><button class="btn" type="submit">Finalizar venda</button></div></div>
</form>
<template id="sale-item-template"><div class="sale-row" style="display:grid;grid-template-columns:1fr 150px auto;gap:10px;align-items:end"><div class="field"><label>Produto</label><select class="sale-product" required><option value="">Selecione um produto</option>@foreach ($products as $product)<option value="{{ $product->id }}" data-price="{{ $product->sale_price }}" data-stock="{{ $product->stock_quantity ?? 0 }}">{{ $product->name }} · estoque {{ $product->stock_quantity ?? 0 }} · R$ {{ number_format((float) $product->sale_price, 2, ',', '.') }}</option>@endforeach</select></div><div class="field"><label>Quantidade</label><input class="sale-quantity" type="number" min="1" value="1" required></div><button class="btn btn-secondary remove-sale-item" type="button">Remover</button></div></template>
<script>
(() => {
    const rows = document.getElementById('sale-items');
    const template = document.getElementById('sale-item-template');
    const addButton = document.getElementById('add-sale-item');
    const addRow = () => {
        const row = template.content.firstElementChild.cloneNode(true);
        row.querySelector('.sale-product').name = `items[${rows.children.length}][product_id]`;
        row.querySelector('.sale-quantity').name = `items[${rows.children.length}][quantity]`;
        row.querySelector('.remove-sale-item').addEventListener('click', () => {
            row.remove();
            [...rows.children].forEach((item, index) => {
                item.querySelector('.sale-product').name = `items[${index}][product_id]`;
                item.querySelector('.sale-quantity').name = `items[${index}][quantity]`;
            });
        });
        rows.appendChild(row);
    };
    addButton.addEventListener('click', addRow);
    addRow();
})();
</script>
@endsection
