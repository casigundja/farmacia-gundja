@extends('layouts.store')

@section('title', 'Política de Privacidade · Farmácia Gundja')

@section('content')
<div class="wrap" style="padding:40px 0 70px;max-width:880px">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Início</a> / Política de Privacidade</div>

    <div class="section-head">
        <div>
            <span class="eyebrow" style="color:var(--wine)">Segurança e Proteção de Dados</span>
            <h1 style="font-size:38px;margin:6px 0">Política de Privacidade</h1>
            <p>Tratamento responsável de dados pessoais e de saúde de acordo com a legislação angolana.</p>
        </div>
    </div>

    <div style="background:white;border:1px solid var(--line);border-radius:16px;padding:34px;line-height:1.7;font-size:14px;color:var(--ink)">
        <h2 style="font-size:20px;color:var(--wine)">1. Informações Gerais</h2>
        <p>A <strong>Farmácia Gundja</strong>, sociedade comercial com sede em Luanda, Angola, assume o compromisso rigoroso de proteger a privacidade, a confidencialidade e a segurança de todos os dados pessoais e sensíveis dos seus clientes, visitantes e utilizadores da sua plataforma online.</p>

        <h2 style="font-size:20px;color:var(--wine);margin-top:24px">2. Tratamento de Dados de Saúde e Receitas Médicas</h2>
        <p>Em virtude da nossa atividade farmacêutica, procedemos ao tratamento de dados de saúde, designadamente prescrições médicas anexadas para aquisição de medicamentos regulamentados. Estes dados são tratados exclusivamente por profissionais de saúde e farmacêuticos habilitados, sob dever de sigilo profissional, com a finalidade exclusiva de validação sanitária, segurança do paciente e cumprimento da regulamentação farmacêutica da República de Angola.</p>

        <h2 style="font-size:20px;color:var(--wine);margin-top:24px">3. Dados Recolhidos para Entrega</h2>
        <p>Para garantir a logística de entrega em Angola, recolhemos dados territoriais específicos como Província, Município, Comuna, Bairro, Rua e Ponto de Referência, além de número de contacto telefónico. Estes dados são utilizados apenas pelos colaboradores encarregados da expedição e entrega.</p>

        <h2 style="font-size:20px;color:var(--wine);margin-top:24px">4. Segurança da Informação</h2>
        <p>Implementamos medidas técnicas e organizativas adequadas, incluindo encriptação de palavras-passe, controlo rigoroso de acessos por perfil de funcionário e registos de auditoria interna para prevenir acessos não autorizados, perda ou alteração de dados.</p>

        <h2 style="font-size:20px;color:var(--wine);margin-top:24px">5. Contacto do Responsável pelo Tratamento</h2>
        <p>Para qualquer questão relativa ao tratamento dos seus dados pessoais, pode contactar-nos através do e-mail: <strong>privacidade@farmaciagundja.ao</strong> ou na nossa sede em Luanda.</p>
    </div>
</div>
@endsection
