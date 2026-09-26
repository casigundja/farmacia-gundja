@extends('layouts.store')

@section('title', 'Contactos · Farmácia Gundja')

@section('content')
<div class="wrap" style="padding:40px 0 70px;max-width:1040px">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Início</a> / Contactos</div>

    <div class="section-head">
        <div>
            <span class="eyebrow" style="color:var(--wine)">Estamos Perto de Si</span>
            <h1 style="font-size:38px;margin:6px 0">Fale com a Farmácia Gundja</h1>
            <p>Dúvidas sobre medicamentos, entregas em Luanda ou parcerias? A nossa equipa está pronta para ajudar.</p>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1.3fr;gap:30px;align-items:start">
        {{-- Informações de Contacto Directo --}}
        <div>
            <div style="background:white;border:1px solid var(--line);border-radius:18px;padding:26px;margin-bottom:20px">
                <h2 style="font-size:20px;color:var(--wine);margin-top:0">Canais Directos</h2>

                <div style="margin-bottom:20px">
                    <strong style="display:block;font-size:14px;color:var(--ink)">💬 WhatsApp Farmácia Gundja</strong>
                    <a href="https://wa.me/244923100200" target="_blank" style="color:var(--green);font-size:16px;font-weight:bold;display:block;margin-top:2px">(+244) 923 100 200</a>
                    <small style="color:var(--muted)">Atendimento rápido para esclarecimento de receitas e pedidos</small>
                </div>

                <div style="margin-bottom:20px">
                    <strong style="display:block;font-size:14px;color:var(--ink)">📞 Apoio Telefónico</strong>
                    <span style="font-size:16px;font-weight:bold;color:var(--wine)">(+244) 923 100 200</span> / 
                    <span style="font-size:16px;font-weight:bold;color:var(--wine)">(+244) 222 000 111</span>
                    <small style="color:var(--muted);display:block">Segunda a Sábado das 07:30 às 21:00</small>
                </div>

                <div style="margin-bottom:20px">
                    <strong style="display:block;font-size:14px;color:var(--ink)">✉️ E-mail Geral</strong>
                    <a href="mailto:contacto@farmaciagundja.ao" style="color:var(--wine);font-weight:600">contacto@farmaciagundja.ao</a>
                </div>

                <div>
                    <strong style="display:block;font-size:14px;color:var(--ink)">📍 Sede Principal</strong>
                    <p style="margin:4px 0 0;font-size:13px;color:var(--muted);line-height:1.5">
                        Rua Rainha Ginga, Edifício Gundja<br>
                        Luanda Centro, Província de Luanda - República de Angola
                    </p>
                </div>
            </div>
        </div>

        {{-- Formulário de Contacto --}}
        <div style="background:white;border:1px solid var(--line);border-radius:18px;padding:30px">
            <h2 style="font-size:20px;color:var(--wine);margin-top:0">Envie-nos uma Mensagem</h2>
            <p style="color:var(--muted);font-size:13px;margin-bottom:20px">Preencha o formulário abaixo e responderemos o mais brevemente possível.</p>

            <form onsubmit="event.preventDefault(); alert('Obrigado pelo seu contacto! A equipa da Farmácia Gundja entrará em contacto brevemente.'); this.reset();" style="display:grid;gap:14px">
                <div>
                    <label style="font-size:12px;font-weight:bold;display:block;margin-bottom:4px">Nome Completo *</label>
                    <input required placeholder="Seu nome" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <label style="font-size:12px;font-weight:bold;display:block;margin-bottom:4px">Telefone / WhatsApp *</label>
                        <input required type="tel" placeholder="Ex: 923 000 000" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:bold;display:block;margin-bottom:4px">E-mail</label>
                        <input type="email" placeholder="seuemail@exemplo.ao" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                    </div>
                </div>

                <div>
                    <label style="font-size:12px;font-weight:bold;display:block;margin-bottom:4px">Assunto</label>
                    <select style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px;background:white">
                        <option>Dúvida sobre Medicamento ou Receita</option>
                        <option>Informações sobre Encomenda / Entrega em Luanda</option>
                        <option>Disponibilidade de Produto</option>
                        <option>Apoio aos Serviços de Saúde</option>
                        <option>Outro Assunto</option>
                    </select>
                </div>

                <div>
                    <label style="font-size:12px;font-weight:bold;display:block;margin-bottom:4px">Sua Mensagem *</label>
                    <textarea required rows="4" placeholder="Escreva aqui a sua mensagem detalhada..." style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px;font-family:inherit"></textarea>
                </div>

                <button class="button" style="padding:12px 24px;font-size:15px;justify-self:start" type="submit">Enviar Mensagem</button>
            </form>
        </div>
    </div>
</div>
@endsection
