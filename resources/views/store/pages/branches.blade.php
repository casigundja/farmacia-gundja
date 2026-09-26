@extends('layouts.store')

@section('title', 'Nossas Farmácias · Farmácia Gundja')

@section('content')
<div class="wrap" style="padding:40px 0 70px;max-width:1040px">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Início</a> / Nossas Farmácias</div>

    <div class="section-head">
        <div>
            <span class="eyebrow" style="color:var(--wine)">Rede de Atendimento em Angola</span>
            <h1 style="font-size:38px;margin:6px 0">Farmácias e Localizações</h1>
            <p>Conheça as nossas unidades em Luanda preparadas para atendê-lo com rapidez, conforto e total segurança.</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:24px;margin-bottom:40px">
        @forelse ($branches as $branch)
            <article style="background:white;border:1px solid var(--line);border-radius:18px;padding:26px;display:flex;flex-direction:column;justify-content:space-between">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <span style="background:var(--rose);color:var(--wine);font-size:11px;font-weight:bold;padding:3px 8px;border-radius:6px;letter-spacing:0.05em">UNIDADE {{ $branch->code }}</span>
                        <span style="color:var(--green);font-size:12px;font-weight:600">● Aberta ao Público</span>
                    </div>
                    <h2 style="font-size:20px;margin:0 0 12px;color:var(--wine)">{{ $branch->name }}</h2>
                    
                    <p style="font-size:14px;color:var(--ink);line-height:1.6;margin-bottom:12px">
                        📍 <strong>Endereço:</strong><br>
                        {{ $branch->address }}<br>
                        <small style="color:var(--muted)">Município de {{ $branch->municipality }}, Província de {{ $branch->province }}</small>
                    </p>

                    <p style="font-size:14px;color:var(--ink);line-height:1.6;margin-bottom:12px">
                        📞 <strong>Telefone de Contacto:</strong><br>
                        {{ $branch->phone }}
                    </p>

                    @if ($branch->opening_hours)
                        <p style="font-size:13px;color:var(--muted);line-height:1.5;background:#f8f9fa;padding:10px;border-radius:8px">
                            🕒 <strong>Horário de Funcionamento:</strong><br>
                            {{ $branch->opening_hours }}
                        </p>
                    @endif
                </div>

                <div style="margin-top:20px;border-top:1px solid var(--line);padding-top:16px;display:flex;gap:10px">
                    <a class="button" style="width:100%;font-size:13px;padding:10px" href="https://wa.me/244923100200?text=Ol%C3%A1%20Farm%C3%A1cia%20Gundja,%20gostaria%20de%20informa%C3%A7%C3%B5es%20sobre%20a%20unidade%20{{ urlencode($branch->name) }}" target="_blank">Falar no WhatsApp</a>
                </div>
            </article>
        @empty
            <div class="empty" style="grid-column:1/-1">Nenhuma unidade cadastrada no momento.</div>
        @endforelse
    </div>

    <div style="background:linear-gradient(135deg, var(--wine), var(--wine-dark));color:white;border-radius:18px;padding:34px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px">
        <div>
            <h3 style="font-size:22px;color:white;margin-top:0">Prefere receber no conforto de sua casa?</h3>
            <p style="margin:4px 0 0;color:#fce7ec">Fazemos entregas em todos os municípios de Luanda com taxas acessíveis e rapidez.</p>
        </div>
        <a class="button button-light" href="{{ route('products.index') }}">Fazer Encomenda Online</a>
    </div>
</div>
@endsection
