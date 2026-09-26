@extends('layouts.store')

@section('title', 'Sobre Nós · Farmácia Gundja')

@section('content')
<div class="wrap" style="padding:40px 0 70px;max-width:960px">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Início</a> / Sobre Nós</div>
    
    <div class="section-head">
        <div>
            <span class="eyebrow" style="color:var(--wine)">Quem Somos</span>
            <h1 style="font-size:38px;margin:6px 0">Farmácia Gundja</h1>
            <p style="font-size:18px;color:var(--wine);font-weight:600">Saúde, confiança e cuidado perto de si.</p>
        </div>
    </div>

    <div style="background:white;border:1px solid var(--line);border-radius:18px;padding:36px;margin-bottom:30px;line-height:1.7">
        <h2 style="font-size:24px;color:var(--wine);margin-top:0">A Nossa História e Propósito em Angola</h2>
        <p>A <strong>Farmácia Gundja</strong> nasceu com a missão de transformar o acesso à saúde e aos medicamentos em Angola, unindo a excelência do atendimento farmacêutico tradicional à inovação do comércio digital. Em um país dinâmico e em constante crescimento, compreendemos a necessidade urgente de oferecer produtos de alta qualidade, procedência garantida e atendimento humano especializado.</p>
        
        <p>Desde a nossa fundação em Luanda, estruturamos uma rede sólida focada em fornecer não apenas medicamentos essenciais e de prescrição rigorosa, mas também soluções completas de higiene, nutrição infantil, dermocosmética e primeiros socorros.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:20px;margin-bottom:35px">
        <div style="background:white;border:1px solid var(--line);border-radius:16px;padding:26px">
            <div style="font-size:32px;margin-bottom:12px;color:var(--wine)">🎯</div>
            <h3 style="font-size:18px;margin-bottom:8px">A Nossa Missão</h3>
            <p style="color:var(--muted);font-size:14px;line-height:1.6">Proporcionar saúde, bem-estar e medicamentos seguros à população angolana, com conveniência, rigor profissional e preços justos em Kwanzas.</p>
        </div>
        <div style="background:white;border:1px solid var(--line);border-radius:16px;padding:26px">
            <div style="font-size:32px;margin-bottom:12px;color:var(--wine)">👁️</div>
            <h3 style="font-size:18px;margin-bottom:8px">A Nossa Visão</h3>
            <p style="color:var(--muted);font-size:14px;line-height:1.6">Ser a rede de farmácias de referência em Angola, reconhecida pela integridade técnica, atendimento caloroso e pela plataforma digital mais confiável do país.</p>
        </div>
        <div style="background:white;border:1px solid var(--line);border-radius:16px;padding:26px">
            <div style="font-size:32px;margin-bottom:12px;color:var(--wine)">⭐</div>
            <h3 style="font-size:18px;margin-bottom:8px">Os Nossos Valores</h3>
            <p style="color:var(--muted);font-size:14px;line-height:1.6">Ética farmacêutica, respeito à legislação sanitária, proximidade com a comunidade e compromisso permanente com a vida de cada paciente.</p>
        </div>
    </div>

    <div style="background:#fdf2f4;border:1px solid #fce7ec;border-radius:18px;padding:32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px">
        <div>
            <h3 style="font-size:22px;color:var(--wine);margin-top:0">Precisa de aconselhamento farmacêutico?</h3>
            <p style="margin:4px 0 0;color:#584d50">A nossa equipe de farmacêuticos habilitados está disponível para esclarecer as suas dúvidas.</p>
        </div>
        <a class="button" href="{{ route('pages.contact') }}">Fale Conosco</a>
    </div>
</div>
@endsection
