@extends('layouts.store')
@section('title', 'Finalizar pedido')
@section('content')
<div class="wrap" style="max-width:880px;padding:32px 0 65px">
    <div class="section-head">
        <div>
            <span class="eyebrow" style="color:var(--wine)">Checkout Seguro · Farmácia Gundja</span>
            <h1 style="font-size:36px;margin:5px 0">Finalizar Encomenda</h1>
            <p>Revise os detalhes de entrega ou retirada e selecione a forma de pagamento em Kwanzas (Kz).</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="notice" style="margin-bottom:18px;background:#fff0ed;color:#a23d2c;border-color:#f0cfc8">
            <strong>Por favor, corrija os seguintes pontos:</strong>
            <ul style="margin:6px 0 0;padding-left:20px">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" style="display:grid;gap:22px">
        @csrf

        {{-- 1. Modalidade de Entrega / Retirada --}}
        <section style="background:white;border:1px solid var(--line);border-radius:14px;padding:24px">
            <h2 style="font-size:20px;margin-bottom:12px">1. Como deseja receber o seu pedido?</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px">
                <label style="display:flex;align-items:center;gap:12px;padding:14px;border:2px solid var(--wine);border-radius:10px;cursor:pointer;background:#faf5f7">
                    <input type="radio" name="delivery_type" value="DELIVERY" id="mode_delivery" checked onchange="toggleDeliveryMode()">
                    <div>
                        <strong>Entrega ao Domicílio</strong>
                        <div style="font-size:12px;color:var(--muted)">Receba comodamente na sua residência ou trabalho</div>
                    </div>
                </label>
                <label style="display:flex;align-items:center;gap:12px;padding:14px;border:1px solid var(--line);border-radius:10px;cursor:pointer">
                    <input type="radio" name="delivery_type" value="PICKUP" id="mode_pickup" onchange="toggleDeliveryMode()">
                    <div>
                        <strong>Retirada na Farmácia</strong>
                        <div style="font-size:12px;color:var(--muted)">Levantamento gratuito em uma unidade Gundja</div>
                    </div>
                </label>
            </div>

            {{-- Painel de Retirada na Loja --}}
            <div id="pickup_section" style="display:none;padding:16px;background:#f9f9fb;border-radius:10px;border:1px solid var(--line);margin-bottom:15px">
                <label for="branch_id" style="font-weight:bold;font-size:14px;display:block;margin-bottom:6px">Selecione a Filial para Retirada:</label>
                <select id="branch_id" name="branch_id" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px;background:#fff">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }} — {{ $branch->address }} ({{ $branch->phone }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Painel de Entrega ao Domicílio --}}
            <div id="delivery_section">
                @if ($addresses->isNotEmpty())
                    <p style="font-weight:bold;font-size:14px;margin-bottom:8px">Seus endereços guardados:</p>
                    @foreach ($addresses as $address)
                        <label style="display:flex;gap:10px;padding:12px;border:1px solid var(--line);border-radius:9px;margin:8px 0;cursor:pointer">
                            <input type="radio" name="address_id" value="{{ $address->id }}" @checked(old('address_id', $addresses->firstWhere('is_default', true)?->id ?? $addresses->first()?->id) == $address->id)>
                            <span>
                                <strong>{{ $address->street }}, {{ $address->number }}</strong> · Bairro {{ $address->neighborhood ?? 'Centro' }}
                                @if ($address->reference_point) <br><small style="color:var(--muted)">Ponto de Ref.: {{ $address->reference_point }}</small> @endif
                                <br><small style="color:var(--muted)">Município: {{ $address->municipality ?? $address->city }} · Província: {{ $address->province ?? 'Luanda' }}</small>
                            </span>
                        </label>
                    @endforeach
                    <label style="display:flex;gap:10px;padding:10px;border:1px dashed var(--line);border-radius:9px;margin:10px 0;cursor:pointer">
                        <input type="radio" name="address_id" value="" @checked(old('address_id') === '')>
                        <span>Cadastrar um novo endereço para esta entrega</span>
                    </label>
                @endif

                <details style="margin-top:14px" @if ($addresses->isEmpty() || old('address_id') === '') open @endif>
                    <summary style="color:var(--wine);font-weight:700;cursor:pointer">Dados do novo endereço em Angola</summary>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px">
                        <div>
                            <label style="font-size:12px;display:block;margin-bottom:4px">Província *</label>
                            <input name="province" value="{{ old('province', 'Luanda') }}" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                        </div>
                        <div>
                            <label style="font-size:12px;display:block;margin-bottom:4px">Município *</label>
                            <select name="municipality" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px;background:white">
                                <option value="Luanda">Luanda (Ingombota / Maianga / Sambizanga)</option>
                                <option value="Talatona">Talatona / Morro Bento / Benfica</option>
                                <option value="Belas">Belas / Kilamba</option>
                                <option value="Kilamba Kiaxi">Kilamba Kiaxi / Golfe</option>
                                <option value="Viana">Viana / Zango / Estalagem</option>
                                <option value="Cazenga">Cazenga</option>
                                <option value="Cacuaco">Cacuaco</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px;display:block;margin-bottom:4px">Bairro *</label>
                            <input name="neighborhood" value="{{ old('neighborhood') }}" placeholder="Ex: Talatona, Alvalade, Maculusso" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                        </div>
                        <div>
                            <label style="font-size:12px;display:block;margin-bottom:4px">Comuna / Distrito Urbano</label>
                            <input name="commune" value="{{ old('commune') }}" placeholder="Ex: Talatona, Benfica, Maianga" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                        </div>
                        <div>
                            <label style="font-size:12px;display:block;margin-bottom:4px">Rua / Avenida *</label>
                            <input name="street" value="{{ old('street') }}" placeholder="Ex: Rua Rainha Ginga, Av. Samora Machel" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                        </div>
                        <div>
                            <label style="font-size:12px;display:block;margin-bottom:4px">Nº da Residência / Lote</label>
                            <input name="number" value="{{ old('number', 'S/N') }}" placeholder="Ex: Casa 14B ou S/N" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                        </div>
                        <div style="grid-column:1/-1">
                            <label style="font-size:12px;display:block;margin-bottom:4px">Ponto de Referência (Essencial para a entrega em Angola) *</label>
                            <input name="reference_point" value="{{ old('reference_point') }}" placeholder="Ex: Próximo ao Belas Shopping / Ao lado do Supermercado X / Bairro Talatona" style="width:100%;padding:10px;border:1px solid var(--line);border-radius:8px">
                        </div>
                    </div>
                </details>
            </div>
        </section>

        {{-- 2. Receita Médica (se aplicável) --}}
        @if ($requiresPrescription)
            <section style="background:#fff1f2;border:1px solid #fecdd3;border-radius:14px;padding:24px">
                <div style="display:flex;align-items:center;gap:10px;color:#9f1239">
                    <span style="font-size:24px">📋</span>
                    <div>
                        <h2 style="font-size:19px;margin:0;color:#9f1239">2. Receita Médica Obrigatória</h2>
                        <p style="margin:4px 0 0;font-size:13px">A sua encomenda contém medicamento(s) sujeito(s) a receita médica de acordo com a legislação farmacêutica angolana.</p>
                    </div>
                </div>
                <div style="margin-top:16px;background:white;padding:16px;border-radius:10px;border:1px solid #fecdd3">
                    <label for="prescription_file" style="font-weight:bold;font-size:13px;display:block;margin-bottom:4px">Anexar Fotografia ou PDF da Receita Médica *</label>
                    <input type="file" id="prescription_file" name="prescription_file" accept=".jpg,.jpeg,.png,.pdf" required style="width:100%;padding:8px;border:1px solid var(--line);border-radius:6px">
                    <small style="color:var(--muted);display:block;margin-top:4px">Formatos aceites: JPG, PNG ou PDF (máx. 5MB). O documento deve ser legível com assinatura/carimbo médico.</small>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px">
                        <div>
                            <label style="font-size:12px;display:block;margin-bottom:2px">Nome do Paciente</label>
                            <input name="patient_name" value="{{ old('patient_name') }}" placeholder="Nome na receita" style="width:100%;padding:8px;border:1px solid var(--line);border-radius:6px">
                        </div>
                        <div>
                            <label style="font-size:12px;display:block;margin-bottom:2px">Médico Prescritor / Nº da Ordem (opcional)</label>
                            <input name="doctor_name" value="{{ old('doctor_name') }}" placeholder="Ex: Dr. Silva / ORM 1234" style="width:100%;padding:8px;border:1px solid var(--line);border-radius:6px">
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- 3. Forma de Pagamento em Angola --}}
        <section style="background:white;border:1px solid var(--line);border-radius:14px;padding:24px">
            <h2 style="font-size:20px;margin-bottom:12px">{{ $requiresPrescription ? '3' : '2' }}. Forma de Pagamento (Kwanzas - Kz)</h2>
            <div style="display:grid;gap:12px">
                {{-- Multicaixa Express --}}
                <label style="padding:14px;border:1px solid var(--line);border-radius:10px;display:block;cursor:pointer">
                    <div style="display:flex;align-items:center;gap:10px">
                        <input type="radio" name="payment_method" value="MULTICAIXA_EXPRESS" checked onchange="togglePaymentProof(false, true)">
                        <strong>Multicaixa Express (MCX)</strong>
                    </div>
                    <div id="mcx_input_block" style="margin-top:10px;padding-left:26px">
                        <small style="color:var(--muted);display:block;margin-bottom:4px">Introduza o número de telemóvel associado ao seu Multicaixa Express:</small>
                        <input type="tel" name="mcx_phone" placeholder="Ex: 923 000 000" style="padding:8px 12px;border:1px solid var(--line);border-radius:6px;width:240px">
                    </div>
                </label>

                {{-- Transferência Bancária --}}
                <label style="padding:14px;border:1px solid var(--line);border-radius:10px;display:block;cursor:pointer">
                    <div style="display:flex;align-items:center;gap:10px">
                        <input type="radio" name="payment_method" value="BANK_TRANSFER" onchange="togglePaymentProof(true, false)">
                        <strong>Transferência Bancária / Depósito</strong>
                    </div>
                    <div id="bank_details_block" style="display:none;margin-top:12px;padding:14px;background:#f8f9fa;border-radius:8px;border:1px solid var(--line)">
                        <strong style="font-size:13px;display:block;margin-bottom:6px">Contas Bancárias da Farmácia Gundja:</strong>
                        <ul style="font-size:12px;color:var(--ink);margin:0 0 10px;padding-left:18px;line-height:1.6">
                            <li><strong>Banco BAI:</strong> AO06 0040.0000.1234.5678.1012.3 (Farmácia Gundja Lda)</li>
                            <li><strong>Banco BFA:</strong> AO06 0006.0000.8765.4321.1019.8 (Farmácia Gundja Lda)</li>
                            <li><strong>Banco BIC:</strong> AO06 0051.0000.9988.7766.1014.5 (Farmácia Gundja Lda)</li>
                        </ul>
                        <label style="font-size:12px;font-weight:bold;display:block;margin-bottom:4px">Anexar Comprovativo de Pagamento (opcional agora, poderá enviar depois):</label>
                        <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" style="padding:6px;border:1px solid var(--line);border-radius:6px;background:white">
                    </div>
                </label>

                {{-- Referência Multicaixa --}}
                <label style="padding:14px;border:1px solid var(--line);border-radius:10px;display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="radio" name="payment_method" value="PAYMENT_REFERENCE" onchange="togglePaymentProof(false, false)">
                    <div>
                        <strong>Pagamento por Referência Multicaixa</strong>
                        <div style="font-size:12px;color:var(--muted)">Geração de Entidade e Referência para pagamento no ATM ou App Bancária.</div>
                    </div>
                </label>

                {{-- Pagamento na Entrega --}}
                <label style="padding:14px;border:1px solid var(--line);border-radius:10px;display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="radio" name="payment_method" value="CASH_ON_DELIVERY" onchange="togglePaymentProof(false, false)">
                    <div>
                        <strong>Pagamento no Ato da Entrega / Levantamento</strong>
                        <div style="font-size:12px;color:var(--muted)">Pague via TPA móvel (Multicaixa) ou em numerário quando receber os produtos.</div>
                    </div>
                </label>
            </div>
        </section>

        {{-- 4. Resumo dos Itens e Valores --}}
        <section style="background:white;border:1px solid var(--line);border-radius:14px;padding:24px">
            <h2 style="font-size:20px;margin-bottom:12px">{{ $requiresPrescription ? '4' : '3' }}. Resumo da Encomenda</h2>
            @foreach ($cart->items as $item)
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line)">
                    <div>
                        <strong>{{ $item->quantity }} × {{ $item->product->name }}</strong>
                        @if ($item->product->requires_prescription)
                            <span style="background:#fff1f2;color:#be123c;font-size:10px;font-weight:bold;padding:1px 5px;border-radius:3px">Receita</span>
                        @endif
                    </div>
                    <strong>{{ number_format($item->quantity * (float) $item->product->effectivePrice(), 2, ',', '.') }} Kz</strong>
                </div>
            @endforeach
            <div style="margin-top:14px;display:grid;gap:6px;font-size:14px">
                <div style="display:flex;justify-content:space-between;color:var(--muted)">
                    <span>Subtotal de Produtos</span>
                    <span>{{ number_format((float) $subtotal, 2, ',', '.') }} Kz</span>
                </div>
                <div style="display:flex;justify-content:space-between;color:var(--muted)" id="shipping_row">
                    <span>Taxa de Entrega estimada</span>
                    <span id="shipping_label">Calculada na entrega (grátis para retirada)</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-weight:800;font-size:20px;border-top:1px solid var(--line);padding-top:12px;margin-top:6px;color:var(--wine)">
                    <span>Total da Encomenda</span>
                    <span>{{ number_format((float) $subtotal, 2, ',', '.') }} Kz*</span>
                </div>
                <small style="color:var(--muted);font-size:11px">* Valor sem taxa de entrega (se retirada) ou acrescido da taxa do seu município.</small>
            </div>
        </section>

        <div style="display:flex;justify-content:space-between;align-items:center">
            <a class="text-link" href="{{ route('cart') }}">← Voltar para a sacola</a>
            <button class="button" style="padding:14px 28px;font-size:16px" type="submit">Confirmar e Finalizar Pedido</button>
        </div>
    </form>
</div>

<script>
function toggleDeliveryMode() {
    var isPickup = document.getElementById('mode_pickup').checked;
    document.getElementById('pickup_section').style.display = isPickup ? 'block' : 'none';
    document.getElementById('delivery_section').style.display = isPickup ? 'none' : 'block';
    document.getElementById('shipping_label').innerText = isPickup ? '0,00 Kz (Retirada Grátis)' : 'Conforme município de Luanda';
}
function togglePaymentProof(showBank, showMcx) {
    var bank = document.getElementById('bank_details_block');
    var mcx = document.getElementById('mcx_input_block');
    if (bank) bank.style.display = showBank ? 'block' : 'none';
    if (mcx) mcx.style.display = showMcx ? 'block' : 'none';
}
</script>
@endsection
