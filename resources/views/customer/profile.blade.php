@extends('layouts.store')
@section('title', 'Meu perfil')
@section('content')
<div class="wrap" style="padding:35px 0 65px">
    <div class="section-head"><div><span class="eyebrow" style="color:var(--wine)">Área do cliente</span><h1 style="font-size:38px;margin:5px 0">Meu perfil</h1><p>Atualize seus dados e gerencie os endereços de entrega.</p></div><a class="button" href="{{ route('customer.orders.index') }}">Meus pedidos</a></div>
    <section style="background:white;border:1px solid var(--line);border-radius:14px;padding:22px;margin-bottom:24px">
        <h2 style="font-size:21px">Dados pessoais</h2>
        <form method="POST" action="{{ route('customer.profile.update') }}" class="profile-grid">@csrf @method('PUT')
            <label>Nome<input required name="name" value="{{ old('name', $customer->user->name) }}"></label>
            <label>E-mail<input required type="email" name="email" value="{{ old('email', $customer->user->email) }}"></label>
            <label>CPF<input name="cpf" value="{{ old('cpf', $customer->cpf) }}" maxlength="14"></label>
            <label>Data de nascimento<input type="date" name="birth_date" value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}"></label>
            <label>Telefone<input name="phone" value="{{ old('phone', $customer->phone) }}" maxlength="30"></label>
            <div style="align-self:end"><button class="button" type="submit">Salvar perfil</button></div>
        </form>
    </section>
    <section style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">
        <div>
            <h2>Endereços salvos</h2>
            @forelse ($customer->addresses as $address)
                <article style="background:white;border:1px solid var(--line);border-radius:12px;padding:17px;margin-bottom:12px">
                    <strong>{{ $address->street }}, {{ $address->number }}</strong> @if ($address->is_default)<span style="color:var(--green);font-size:12px">· Padrão</span>@endif
                    <div style="font-size:13px;color:var(--muted)">{{ $address->neighborhood }}, {{ $address->city }} - {{ $address->state }} · CEP {{ $address->zipcode }}</div>
                    @if ($address->complement)<div style="font-size:13px;color:var(--muted)">{{ $address->complement }}</div>@endif
                    <div style="display:flex;gap:10px;margin-top:12px">
                        @unless ($address->is_default)<form method="POST" action="{{ route('customer.addresses.default', $address) }}">@csrf @method('PATCH')<button type="submit" class="button" style="padding:7px 10px;font-size:12px">Tornar padrão</button></form>@endunless
                        <form method="POST" action="{{ route('customer.addresses.destroy', $address) }}" onsubmit="return confirm('Remover este endereço?')">@csrf @method('DELETE')<button type="submit" style="border:0;background:none;color:#9a413b;cursor:pointer">Remover</button></form>
                    </div>
                    <details style="margin-top:10px"><summary style="cursor:pointer;color:var(--wine);font-size:13px;font-weight:700">Editar endereço</summary>
                        <form method="POST" action="{{ route('customer.addresses.update', $address) }}" class="profile-grid" style="margin-top:12px">@csrf @method('PUT')
                            <label>CEP<input required name="zipcode" maxlength="10" value="{{ $address->zipcode }}"></label><label>Estado (UF)<input required name="state" maxlength="2" value="{{ $address->state }}"></label>
                            <label class="full">Rua<input required name="street" value="{{ $address->street }}"></label><label>Número<input required name="number" maxlength="30" value="{{ $address->number }}"></label><label>Complemento<input name="complement" value="{{ $address->complement }}"></label>
                            <label>Bairro<input required name="neighborhood" value="{{ $address->neighborhood }}"></label><label>Cidade<input required name="city" value="{{ $address->city }}"></label>
                            <label class="full" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="is_default" value="1" style="width:auto" @checked($address->is_default)> Endereço padrão</label>
                            <div class="full"><button class="button" type="submit" style="padding:7px 10px;font-size:12px">Salvar endereço</button></div>
                        </form>
                    </details>
                </article>
            @empty
                <p class="empty">Você ainda não cadastrou endereços.</p>
            @endforelse
        </div>
        <div style="background:white;border:1px solid var(--line);border-radius:14px;padding:22px">
            <h2 style="font-size:21px">Adicionar endereço</h2>
            <form method="POST" action="{{ route('customer.addresses.store') }}" class="profile-grid">@csrf
                <label>CEP<input required name="zipcode" maxlength="10" value="{{ old('zipcode') }}"></label>
                <label>Estado (UF)<input required name="state" maxlength="2" value="{{ old('state') }}"></label>
                <label class="full">Rua<input required name="street" value="{{ old('street') }}"></label>
                <label>Número<input required name="number" maxlength="30" value="{{ old('number') }}"></label>
                <label>Complemento<input name="complement" value="{{ old('complement') }}"></label>
                <label>Bairro<input required name="neighborhood" value="{{ old('neighborhood') }}"></label>
                <label>Cidade<input required name="city" value="{{ old('city') }}"></label>
                @if ($customer->addresses->isNotEmpty())<label class="full" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="is_default" value="1" style="width:auto"> Definir como endereço padrão</label>@endif
                <div class="full"><button class="button" type="submit">Salvar endereço</button></div>
            </form>
        </div>
    </section>
</div>
<style>.profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.profile-grid label{display:block;font-size:12px;font-weight:700}.profile-grid input{display:block;width:100%;margin-top:5px;padding:10px;border:1px solid var(--line);border-radius:8px}.profile-grid .full{grid-column:1/-1}@media(max-width:700px){.profile-grid{grid-template-columns:1fr}.profile-grid .full{grid-column:auto}section[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}}</style>
@endsection
