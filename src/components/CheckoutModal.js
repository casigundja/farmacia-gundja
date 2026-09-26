import { cartStore } from './Cart.js';
import { formatKz, initialBranches } from '../data/initialData.js';
import { supabaseService } from '../supabase.js';
import { Toast } from './Toast.js';

export function renderCheckoutModal() {
  const items = cartStore.items;
  const subtotal = cartStore.getSubtotal();
  const deliveryFee = 2000;

  return `
    <div id="checkout-modal-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity duration-300 opacity-0 pointer-events-none">
      <div id="checkout-modal-panel" class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300">
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur-sm z-10">
          <div>
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Finalização Segura</span>
            <h2 class="text-xl font-bold text-slate-800">Concluir o Seu Pedido</h2>
          </div>
          <button id="close-checkout-btn" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
          </button>
        </div>

        <form id="checkout-form" class="p-6 space-y-6">
          <!-- Step 1: Customer Info -->
          <div>
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-3">
              <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">1</span>
              Os Seus Dados
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nome Completo *</label>
                <input type="text" name="customerName" required placeholder="Ex: Casimiro Gundja" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Telefone / WhatsApp *</label>
                <input type="tel" name="customerPhone" required placeholder="Ex: 923 000 000" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
              </div>
            </div>
          </div>

          <!-- Step 2: Delivery Option -->
          <div>
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-3">
              <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">2</span>
              Método de Entrega
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label class="border-2 border-emerald-600 bg-emerald-50/30 p-3.5 rounded-2xl cursor-pointer flex items-start gap-3 transition-colors delivery-option" data-type="pickup">
                <input type="radio" name="deliveryType" value="pickup" checked class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                <div>
                  <div class="text-xs font-bold text-slate-800">Levantamento na Farmácia</div>
                  <div class="text-[11px] text-slate-500 mt-0.5">Levante numa das unidades da Farmácia Gundja em Luanda sem custos adicionais.</div>
                  <span class="inline-block mt-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">Grátis</span>
                </div>
              </label>

              <label class="border-2 border-slate-200 hover:border-slate-300 p-3.5 rounded-2xl cursor-pointer flex items-start gap-3 transition-colors delivery-option" data-type="delivery">
                <input type="radio" name="deliveryType" value="delivery" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                <div>
                  <div class="text-xs font-bold text-slate-800">Entrega ao Domicílio em Luanda</div>
                  <div class="text-[11px] text-slate-500 mt-0.5">Talatona, Viana, Belas, Maianga, Cazenga e arredores.</div>
                  <span class="inline-block mt-1 text-[10px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">+2.000 Kz</span>
                </div>
              </label>
            </div>

            <!-- Branch selector for pickup -->
            <div id="pickup-branch-group" class="mt-3">
              <label class="block text-xs font-semibold text-slate-600 mb-1">Escolha a Filial de Levantamento:</label>
              <select name="branchId" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white">
                <option value="1">Farmácia Gundja — Sede Luanda Centro (Rua Rainha Ginga)</option>
                <option value="2">Farmácia Gundja — Talatona (Shopping Talatona)</option>
                <option value="3">Farmácia Gundja — Viana (Estrada de Catete)</option>
              </select>
            </div>

            <!-- Address fields for home delivery -->
            <div id="delivery-address-group" class="mt-3 hidden space-y-2">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Endereço de Entrega (Rua, Bairro, Município) *</label>
                <input type="text" name="address" placeholder="Ex: Bairro Talatona, Rua dos Farmacêuticos, Casa 12" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Ponto de Referência</label>
                <input type="text" name="referencePoint" placeholder="Ex: Próximo à Escola Primária ou Banco BIC" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
              </div>
            </div>
          </div>

          <!-- Step 3: Payment Method -->
          <div>
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-3">
              <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">3</span>
              Forma de Pagamento
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
              <label class="border border-slate-200 p-3 rounded-xl cursor-pointer flex flex-col items-center text-center gap-1 hover:border-emerald-500 transition-colors">
                <input type="radio" name="paymentMethod" value="MCX" checked class="text-emerald-600">
                <span class="text-xs font-bold text-slate-800 mt-1">Multicaixa Express</span>
                <span class="text-[10px] text-slate-400">Referência MCX</span>
              </label>

              <label class="border border-slate-200 p-3 rounded-xl cursor-pointer flex flex-col items-center text-center gap-1 hover:border-emerald-500 transition-colors">
                <input type="radio" name="paymentMethod" value="TRANSFER" class="text-emerald-600">
                <span class="text-xs font-bold text-slate-800 mt-1">Transferência / IBAN</span>
                <span class="text-[10px] text-slate-400">Comprovativo directo</span>
              </label>

              <label class="border border-slate-200 p-3 rounded-xl cursor-pointer flex flex-col items-center text-center gap-1 hover:border-emerald-500 transition-colors">
                <input type="radio" name="paymentMethod" value="CASH_TPA" class="text-emerald-600">
                <span class="text-xs font-bold text-slate-800 mt-1">TPA / Dinheiro</span>
                <span class="text-[10px] text-slate-400">No acto da entrega</span>
              </label>
            </div>
          </div>

          <!-- Notes / Prescription -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Observações ou Receita Médica (opcional)</label>
            <textarea name="notes" rows="2" placeholder="Instruções para o farmacêutico, dosagem, ou mencione se enviará foto da receita por WhatsApp..." class="w-full text-xs px-3.5 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
          </div>

          <!-- Order Summary Box -->
          <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
            <div class="flex justify-between text-xs text-slate-600">
              <span>Subtotal (${items.length} itens)</span>
              <span class="font-semibold">${formatKz(subtotal)}</span>
            </div>
            <div id="delivery-cost-row" class="flex justify-between text-xs text-slate-600 hidden">
              <span>Taxa de Entrega</span>
              <span class="font-semibold">${formatKz(deliveryFee)}</span>
            </div>
            <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-bold text-slate-900">
              <span>Total a Pagar</span>
              <span id="checkout-total-val" class="text-emerald-700 font-extrabold text-base">${formatKz(subtotal)}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" id="submit-order-btn" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-2xl font-bold text-sm shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
            <span>Confirmar e Finalizar Pedido</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </button>
        </form>

        <!-- Success Container (Hidden by default) -->
        <div id="checkout-success-view" class="p-8 text-center hidden space-y-5">
          <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-3xl font-bold">
            ✓
          </div>
          <div>
            <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full uppercase">Pedido Recebido com Sucesso</span>
            <h3 class="text-2xl font-bold text-slate-800 mt-2">Obrigado pela sua preferência!</h3>
            <p class="text-xs text-slate-500 mt-1">O seu pedido foi registado na Farmácia Gundja com o código:</p>
            <div id="order-public-code" class="text-xl font-mono font-bold text-emerald-700 mt-2 bg-emerald-50 border border-emerald-200 py-2 px-4 rounded-xl inline-block tracking-wider">
              FG-000000
            </div>
          </div>

          <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 text-left text-xs space-y-2 max-w-md mx-auto">
            <div class="font-bold text-slate-700 border-b border-slate-200 pb-1.5 flex items-center justify-between">
              <span>Instruções de Atendimento Rápido</span>
              <span class="text-emerald-600">● Aberto</span>
            </div>
            <p class="text-slate-600">
              Para separação prioritária e acompanhamento em tempo real, clique no botão abaixo para enviar o resumo directamente ao farmacêutico de serviço pelo WhatsApp.
            </p>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 justify-center max-w-md mx-auto">
            <a id="whatsapp-order-link" href="#" target="_blank" class="flex-1 py-3.5 px-4 bg-[#25D366] hover:bg-[#20ba59] text-white font-bold text-xs rounded-xl shadow-md flex items-center justify-center gap-2 transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
              <span>Enviar Resumo pelo WhatsApp</span>
            </a>
            <button id="success-done-btn" class="py-3 px-5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">
              Continuar a Comprar
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
}

export function initCheckoutEvents() {
  const overlay = document.getElementById('checkout-modal-overlay');
  const panel = document.getElementById('checkout-modal-panel');
  const closeBtn = document.getElementById('close-checkout-btn');
  const form = document.getElementById('checkout-form');
  const successView = document.getElementById('checkout-success-view');

  const pickupRadio = document.querySelector('input[name="deliveryType"][value="pickup"]');
  const deliveryRadio = document.querySelector('input[name="deliveryType"][value="delivery"]');
  const pickupGroup = document.getElementById('pickup-branch-group');
  const deliveryGroup = document.getElementById('delivery-address-group');
  const deliveryRow = document.getElementById('delivery-cost-row');
  const totalVal = document.getElementById('checkout-total-val');

  function updateTotals() {
    const isDelivery = deliveryRadio && deliveryRadio.checked;
    const subtotal = cartStore.getSubtotal();
    const total = isDelivery ? subtotal + 2000 : subtotal;

    if (isDelivery) {
      pickupGroup?.classList.add('hidden');
      deliveryGroup?.classList.remove('hidden');
      deliveryRow?.classList.remove('hidden');
    } else {
      pickupGroup?.classList.remove('hidden');
      deliveryGroup?.classList.add('hidden');
      deliveryRow?.classList.add('hidden');
    }

    if (totalVal) totalVal.textContent = formatKz(total);
  }

  pickupRadio?.addEventListener('change', updateTotals);
  deliveryRadio?.addEventListener('change', updateTotals);

  closeBtn?.addEventListener('click', closeCheckout);
  document.getElementById('success-done-btn')?.addEventListener('click', closeCheckout);

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submit-order-btn');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = `<span class="animate-spin inline-block mr-2">◌</span> Processando Pedido...`;
    }

    const formData = new FormData(form);
    const isDelivery = formData.get('deliveryType') === 'delivery';
    const subtotal = cartStore.getSubtotal();
    const total = isDelivery ? subtotal + 2000 : subtotal;
    const branchId = parseInt(formData.get('branchId') || '1');

    const payload = {
      customerName: formData.get('customerName'),
      customerPhone: formData.get('customerPhone'),
      deliveryType: formData.get('deliveryType'),
      branchId,
      address: isDelivery ? `${formData.get('address')} (${formData.get('referencePoint') || ''})` : 'Levantamento em Loja',
      paymentMethod: formData.get('paymentMethod'),
      notes: formData.get('notes'),
      subtotal,
      total,
      items: cartStore.items.map(i => ({
        id: i.product.id,
        name: i.product.name,
        quantity: i.quantity,
        sale_price: i.product.sale_price
      }))
    };

    const res = await supabaseService.createOrder(payload);

    // Format WhatsApp message
    const targetBranch = initialBranches.find(b => b.id === branchId) || initialBranches[0];
    const itemsList = cartStore.items.map(i => `• ${i.quantity}x ${i.product.name} - ${formatKz(i.product.sale_price * i.quantity)}`).join('\n');
    
    const waText = encodeURIComponent(
      `*PEDIDO FARMÁCIA GUNDJA* (${res.publicId})\n` +
      `---------------------------\n` +
      `*Cliente:* ${payload.customerName}\n` +
      `*Telefone:* ${payload.customerPhone}\n` +
      `*Modalidade:* ${isDelivery ? 'Entrega ao Domicílio' : `Levantamento (${targetBranch.name})`}\n` +
      (isDelivery ? `*Endereço:* ${payload.address}\n` : '') +
      `*Pagamento:* ${payload.paymentMethod}\n` +
      (payload.notes ? `*Observações:* ${payload.notes}\n` : '') +
      `---------------------------\n` +
      `*ITENS DO PEDIDO:*\n${itemsList}\n` +
      `---------------------------\n` +
      `*TOTAL:* ${formatKz(total)}\n\n` +
      `_Por favor confirmem a disponibilidade e dados para pagamento._`
    );

    const waLink = `https://wa.me/${targetBranch.whatsapp}?text=${waText}`;
    const waBtn = document.getElementById('whatsapp-order-link');
    if (waBtn) waBtn.href = waLink;

    const codeEl = document.getElementById('order-public-code');
    if (codeEl) codeEl.textContent = res.publicId;

    form.classList.add('hidden');
    successView?.classList.remove('hidden');

    cartStore.clear();
    Toast.show('Pedido registado com sucesso!', 'success');
  });
}

export function openCheckout() {
  const overlay = document.getElementById('checkout-modal-overlay');
  const panel = document.getElementById('checkout-modal-panel');
  const form = document.getElementById('checkout-form');
  const successView = document.getElementById('checkout-success-view');

  if (cartStore.items.length === 0) {
    Toast.show('Adicione pelo menos um produto ao carrinho para prosseguir.', 'error');
    return;
  }

  form?.classList.remove('hidden');
  successView?.classList.add('hidden');

  overlay?.classList.remove('opacity-0', 'pointer-events-none');
  panel?.classList.remove('scale-95');
}

export function closeCheckout() {
  const overlay = document.getElementById('checkout-modal-overlay');
  const panel = document.getElementById('checkout-modal-panel');
  overlay?.classList.add('opacity-0', 'pointer-events-none');
  panel?.classList.add('scale-95');
}
