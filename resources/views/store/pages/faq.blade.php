@extends('layouts.store')

@section('title', 'Perguntas Frequentes (FAQ) · Farmácia Gundja')

@section('content')
<div class="wrap" style="padding:40px 0 70px;max-width:960px">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Início</a> / Perguntas Frequentes</div>

    <div class="section-head">
        <div>
            <span class="eyebrow" style="color:var(--wine)">Ajuda & Informações</span>
            <h1 style="font-size:38px;margin:6px 0">Perguntas Frequentes</h1>
            <p>Tire as suas dúvidas sobre compras, validação de receitas médicas, pagamentos e entregas em Angola.</p>
        </div>
    </div>

    <div style="display:grid;gap:16px;margin-bottom:40px">
        <details style="background:white;border:1px solid var(--line);border-radius:12px;padding:18px" open>
            <summary style="font-size:16px;font-weight:bold;color:var(--wine);cursor:pointer">1. Como funciona a compra de medicamentos com receita médica?</summary>
            <p style="margin:12px 0 0;font-size:14px;color:var(--ink);line-height:1.6">
                Para medicamentos sujeitos a prescrição médica obrigatória (como antibióticos, antimaláricos e medicamentos controlados), o sistema solicita que anexe uma fotografia legível ou ficheiro PDF da receita médica durante o checkout. O nosso farmacêutico habilitado analisa o documento e, após validação, a sua encomenda é preparada e expedida.
            </p>
        </details>

        <details style="background:white;border:1px solid var(--line);border-radius:12px;padding:18px">
            <summary style="font-size:16px;font-weight:bold;color:var(--wine);cursor:pointer">2. Quais são as formas de pagamento disponíveis em Kwanzas?</summary>
            <p style="margin:12px 0 0;font-size:14px;color:var(--ink);line-height:1.6">
                Aceitamos os meios mais práticos e seguros do mercado angolano:<br>
                • <strong>Multicaixa Express (MCX)</strong>: através do seu número de telemóvel associado ao serviço;<br>
                • <strong>Transferência Bancária / Depósito</strong>: para as nossas contas oficiais nos bancos BAI, BFA e BIC;<br>
                • <strong>Referência Multicaixa</strong>: pagamento em qualquer caixa eletrónico (ATM) ou internet banking;<br>
                • <strong>Pagamento na Entrega</strong>: através de TPA móvel (Multicaixa) ou em numerário quando receber o pedido.
            </p>
        </details>

        <details style="background:white;border:1px solid var(--line);border-radius:12px;padding:18px">
            <summary style="font-size:16px;font-weight:bold;color:var(--wine);cursor:pointer">3. Como funcionam as entregas ao domicílio em Luanda?</summary>
            <p style="margin:12px 0 0;font-size:14px;color:var(--ink);line-height:1.6">
                Realizamos entregas diárias nos principais municípios de Luanda (Luanda Centro, Maianga, Ingombota, Talatona, Belas, Kilamba Kiaxi, Viana, Cazenga e Cacuaco). A taxa de entrega varia conforme o município (a partir de 1.000 Kz) e é <strong>gratuita para encomendas acima de 50.000 Kz</strong>.
            </p>
        </details>

        <details style="background:white;border:1px solid var(--line);border-radius:12px;padding:18px">
            <summary style="font-size:16px;font-weight:bold;color:var(--wine);cursor:pointer">4. Posso fazer o pedido online e retirar na farmácia física?</summary>
            <p style="margin:12px 0 0;font-size:14px;color:var(--ink);line-height:1.6">
                Sim! No momento do checkout, selecione a modalidade <strong>"Retirada na Farmácia"</strong> e escolha a filial Gundja mais conveniente (Sede Luanda, Talatona ou Viana). O levantamento é totalmente gratuito e os produtos são separados previamente para poupar o seu tempo.
            </p>
        </details>

        <details style="background:white;border:1px solid var(--line);border-radius:12px;padding:18px">
            <summary style="font-size:16px;font-weight:bold;color:var(--wine);cursor:pointer">5. Como posso acompanhar o estado da minha encomenda?</summary>
            <p style="margin:12px 0 0;font-size:14px;color:var(--ink);line-height:1.6">
                Após concluir a encomenda, receberá um código único no formato <code>FG-2026-XXXXXX</code>. Pode acompanhar todas as etapas (Validação de receita, Pagamento pendente, Confirmado, Em separação, Em rota de entrega e Entregue) entrando na sua <strong>Área de Cliente</strong> no menu "Meus Pedidos".
            </p>
        </details>

        <details style="background:white;border:1px solid var(--line);border-radius:12px;padding:18px">
            <summary style="font-size:16px;font-weight:bold;color:var(--wine);cursor:pointer">6. Os medicamentos comercializados são certificados em Angola?</summary>
            <p style="margin:12px 0 0;font-size:14px;color:var(--ink);line-height:1.6">
                Completamente. Todos os produtos e medicamentos disponibilizados pela Farmácia Gundja são fornecidos por distribuidores farmacêuticos licenciados pela Direção Nacional de Medicamentos e Equipamentos da República de Angola, com controle estrito de lotes, validades e condições térmicas de armazenamento.
            </p>
        </details>
    </div>

    <div style="background:#f8f9fa;border:1px solid var(--line);border-radius:14px;padding:24px;text-align:center">
        <h3 style="margin-top:0;font-size:18px">Ainda tem alguma dúvida?</h3>
        <p style="color:var(--muted);font-size:13px;margin:4px 0 16px">Entre em contacto com o nosso apoio farmacêutico via WhatsApp.</p>
        <a class="button" href="https://wa.me/244923100200" target="_blank">Falar no WhatsApp: (+244) 923 100 200</a>
    </div>
</div>
@endsection
