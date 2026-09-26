@extends('layouts.store')

@section('title', 'Serviços Farmacêuticos · Farmácia Gundja')

@section('content')
<div class="wrap" style="padding:40px 0 70px;max-width:1040px">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Início</a> / Serviços Farmacêuticos</div>

    <div class="section-head">
        <div>
            <span class="eyebrow" style="color:var(--wine)">Cuidados de Saúde</span>
            <h1 style="font-size:38px;margin:6px 0">Serviços Farmacêuticos</h1>
            <p>Mais do que dispensar medicamentos, cuidamos ativamente da sua saúde e da sua família em Angola.</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(310px, 1fr));gap:24px;margin-bottom:40px">
        {{-- Serviço 1 --}}
        <article style="background:white;border:1px solid var(--line);border-radius:16px;padding:26px">
            <div style="width:52px;height:52px;background:var(--rose);color:var(--wine);border-radius:12px;display:grid;place-items:center;font-size:24px;margin-bottom:16px">🩺</div>
            <h3 style="font-size:19px;margin-bottom:8px">Medição de Tensão Arterial</h3>
            <p style="color:var(--muted);font-size:14px;line-height:1.6">Controle regular da pressão arterial realizado por profissionais com aparelhos calibrados e registo de acompanhamento para o seu médico cardiologista.</p>
            <span style="font-size:12px;font-weight:bold;color:var(--green)">✓ Disponível em todas as filiais</span>
        </article>

        {{-- Serviço 2 --}}
        <article style="background:white;border:1px solid var(--line);border-radius:16px;padding:26px">
            <div style="width:52px;height:52px;background:var(--rose);color:var(--wine);border-radius:12px;display:grid;place-items:center;font-size:24px;margin-bottom:16px">🩸</div>
            <h3 style="font-size:19px;margin-bottom:8px">Teste Rápido de Glicemia</h3>
            <p style="color:var(--muted);font-size:14px;line-height:1.6">Determinação dos níveis de glicose no sangue em jejum ou pós-prandial em apenas alguns segundos, essencial para prevenção e monitoramento de diabetes.</p>
            <span style="font-size:12px;font-weight:bold;color:var(--green)">✓ Resultado Imediato</span>
        </article>

        {{-- Serviço 3 --}}
        <article style="background:white;border:1px solid var(--line);border-radius:16px;padding:26px">
            <div style="width:52px;height:52px;background:var(--rose);color:var(--wine);border-radius:12px;display:grid;place-items:center;font-size:24px;margin-bottom:16px">🦟</div>
            <h3 style="font-size:19px;margin-bottom:8px">Teste Rápido de Malária</h3>
            <p style="color:var(--muted);font-size:14px;line-height:1.6">Triagem e diagnóstico rápido para deteção de antigénios de malária (Plasmodium falciparum), com encaminhamento farmacêutico imediato.</p>
            <span style="font-size:12px;font-weight:bold;color:var(--green)">✓ Triagem Segura</span>
        </article>

        {{-- Serviço 4 --}}
        <article style="background:white;border:1px solid var(--line);border-radius:16px;padding:26px">
            <div style="width:52px;height:52px;background:var(--rose);color:var(--wine);border-radius:12px;display:grid;place-items:center;font-size:24px;margin-bottom:16px">💉</div>
            <h3 style="font-size:19px;margin-bottom:8px">Administração de Injetáveis</h3>
            <p style="color:var(--muted);font-size:14px;line-height:1.6">Aplicação segura de medicamentos injetáveis intramusculares e subcutâneos mediante apresentação de receita médica e sob as mais rigorosas normas de assepsia.</p>
            <span style="font-size:12px;font-weight:bold;color:var(--wine)">* Requer receita médica</span>
        </article>

        {{-- Serviço 5 --}}
        <article style="background:white;border:1px solid var(--line);border-radius:16px;padding:26px">
            <div style="width:52px;height:52px;background:var(--rose);color:var(--wine);border-radius:12px;display:grid;place-items:center;font-size:24px;margin-bottom:16px">💬</div>
            <h3 style="font-size:19px;margin-bottom:8px">Consulta Farmacêutica</h3>
            <p style="color:var(--muted);font-size:14px;line-height:1.6">Esclarecimento de posologias, orientação sobre interações medicamentosas, efeitos secundários e apoio contínuo a doentes crónicos e idosos.</p>
            <span style="font-size:12px;font-weight:bold;color:var(--green)">✓ Atendimento Personalizado</span>
        </article>

        {{-- Serviço 6 --}}
        <article style="background:white;border:1px solid var(--line);border-radius:16px;padding:26px">
            <div style="width:52px;height:52px;background:var(--rose);color:var(--wine);border-radius:12px;display:grid;place-items:center;font-size:24px;margin-bottom:16px">⚖️</div>
            <h3 style="font-size:19px;margin-bottom:8px">Determinação de Peso e IMC</h3>
            <p style="color:var(--muted);font-size:14px;line-height:1.6">Balança de precisão para adultos e pesagem pediátrica de lactentes, com cálculo do Índice de Massa Corporal e aconselhamento de saúde nutricional.</p>
            <span style="font-size:12px;font-weight:bold;color:var(--green)">✓ Gratuito</span>
        </article>
    </div>

    <div style="background:#fdf2f4;border:1px solid #fce7ec;border-radius:18px;padding:32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px">
        <div>
            <h3 style="font-size:20px;color:var(--wine);margin-top:0">Visite uma das nossas farmácias em Luanda</h3>
            <p style="margin:4px 0 0;color:#584d50">Os serviços presenciais estão disponíveis em todas as nossas unidades abertas de segunda a domingo.</p>
        </div>
        <a class="button" href="{{ route('pages.branches') }}">Ver Farmácias e Horários</a>
    </div>
</div>
@endsection
