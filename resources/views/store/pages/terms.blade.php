@extends('layouts.store')

@section('title', 'Termos de Utilização · Farmácia Gundja')

@section('content')
<div class="wrap" style="padding:40px 0 70px;max-width:880px">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Início</a> / Termos de Utilização</div>

    <div class="section-head">
        <div>
            <span class="eyebrow" style="color:var(--wine)">Condições Legais</span>
            <h1 style="font-size:38px;margin:6px 0">Termos de Utilização</h1>
            <p>Regras e diretrizes para utilização da plataforma digital da Farmácia Gundja em Angola.</p>
        </div>
    </div>

    <div style="background:white;border:1px solid var(--line);border-radius:16px;padding:34px;line-height:1.7;font-size:14px;color:var(--ink)">
        <h2 style="font-size:20px;color:var(--wine)">1. Objeto</h2>
        <p>Os presentes Termos de Utilização regulam o acesso e a utilização do sítio na internet e loja online da <strong>Farmácia Gundja</strong> para consulta de catálogo, aquisição de produtos farmacêuticos, agendamento de serviços e acompanhamento de encomendas em Angola.</p>

        <h2 style="font-size:20px;color:var(--wine);margin-top:24px">2. Venda de Medicamentos e Prescrições Médicas</h2>
        <p>A comercialização de medicamentos sujeitos a receita médica é condicionada à apresentação e aprovação prévia de receita médica válida por farmacêutico habilitado. A Farmácia Gundja reserva-se o direito de recusar ou cancelar qualquer pedido cuja receita apresente irregularidades, rasuras, ilegibilidade ou desconformidade com a legislação angolana.</p>

        <h2 style="font-size:20px;color:var(--wine);margin-top:24px">3. Preços e Moeda</h2>
        <p>Todos os preços constantes na plataforma são apresentados em <strong>Kwanzas (Kz / AOA)</strong> e incluem os impostos legais em vigor na República de Angola. As taxas de entrega são calculadas com base no município de destino indicado no momento da compra.</p>

        <h2 style="font-size:20px;color:var(--wine);margin-top:24px">4. Cancelamentos e Devoluções</h2>
        <p>Por motivos de segurança e integridade sanitária, medicamentos expedidos e entregues ao cliente não são suscetíveis de devolução, salvo em casos de defeito de fabrico devidamente comprovado pelo responsável técnico farmacêutico.</p>
    </div>
</div>
@endsection
