import { formatKz } from '../data/initialData.js';
import { cartStore } from './Cart.js';

export function renderProductModal() {
  return `
    <div id="product-modal-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity duration-300 opacity-0 pointer-events-none">
      <div id="product-modal-panel" class="bg-white rounded-3xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300">
        <div id="product-modal-content">
          <!-- Dynamic Content -->
        </div>
      </div>
    </div>
  `;
}

export function openProductModal(product) {
  const overlay = document.getElementById('product-modal-overlay');
  const panel = document.getElementById('product-modal-panel');
  const content = document.getElementById('product-modal-content');
  if (!content) return;

  content.innerHTML = `
    <div class="relative">
      <img src="${product.image}" alt="${product.name}" class="w-full aspect-[4/3] object-cover rounded-t-3xl bg-slate-100">
      <button id="close-prod-modal-btn" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/90 text-slate-700 hover:bg-white flex items-center justify-center shadow-lg transition-colors">
        ✕
      </button>
      ${product.badge ? `<span class="absolute bottom-4 left-4 bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">${product.badge}</span>` : ''}
    </div>

    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between text-xs text-slate-400">
        <span>${product.categoryName}</span>
        <span>Marca: <strong class="text-slate-700">${product.brand}</strong></span>
      </div>

      <div>
        <h2 class="text-xl font-bold text-slate-800 leading-tight">${product.name}</h2>
        <div class="flex items-center gap-3 mt-2">
          <span class="text-2xl font-extrabold text-emerald-700">${formatKz(product.sale_price)}</span>
          ${product.price > product.sale_price ? `<span class="text-sm text-slate-400 line-through">${formatKz(product.price)}</span>` : ''}
        </div>
      </div>

      ${product.requires_prescription ? `
        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-start gap-2">
          <span class="text-base leading-none">⚠</span>
          <div>
            <strong>Medicamento de Receita Médica Obrigatória:</strong>
            <p class="mt-0.5 text-amber-700">A dispensa deste medicamento requer a apresentação de receita médica válida no momento do levantamento ou envio da foto pelo WhatsApp.</p>
          </div>
        </div>
      ` : ''}

      <div>
        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Descrição e Posologia</h4>
        <p class="text-xs text-slate-600 leading-relaxed">${product.desc || product.short_desc}</p>
      </div>

      <!-- Branch Availability Box -->
      <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
        <h4 class="text-xs font-bold text-slate-700 mb-2">Disponibilidade nas Farmácias Gundja:</h4>
        <div class="grid grid-cols-3 gap-2 text-xs">
          <div class="p-2 bg-white rounded-xl border border-slate-200/70 flex flex-col justify-between">
            <div>
              <div class="font-semibold text-slate-800 text-[11px]">Sede Centro</div>
              <div class="text-[9px] text-slate-400">Ingombota</div>
            </div>
            <span class="text-xs font-bold mt-1 ${product.stock?.luanda > 0 ? 'text-emerald-600' : 'text-rose-500'}">
              ${product.stock?.luanda > 0 ? `${product.stock.luanda} un` : 'Esgotado'}
            </span>
          </div>

          <div class="p-2 bg-white rounded-xl border border-slate-200/70 flex flex-col justify-between">
            <div>
              <div class="font-semibold text-slate-800 text-[11px]">Talatona</div>
              <div class="text-[9px] text-slate-400">Shopping</div>
            </div>
            <span class="text-xs font-bold mt-1 ${product.stock?.talatona > 0 ? 'text-emerald-600' : 'text-rose-500'}">
              ${product.stock?.talatona > 0 ? `${product.stock.talatona} un` : 'Esgotado'}
            </span>
          </div>

          <div class="p-2 bg-white rounded-xl border border-slate-200/70 flex flex-col justify-between">
            <div>
              <div class="font-semibold text-slate-800 text-[11px]">Viana</div>
              <div class="text-[9px] text-slate-400">Km 14</div>
            </div>
            <span class="text-xs font-bold mt-1 ${product.stock?.viana > 0 ? 'text-emerald-600' : 'text-rose-500'}">
              ${product.stock?.viana > 0 ? `${product.stock.viana} un` : 'Esgotado'}
            </span>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="pt-2 flex items-center gap-3">
        <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-1">
          <button id="modal-dec-qty" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center">-</button>
          <span id="modal-qty-val" class="w-10 text-center text-sm font-bold text-slate-800">1</span>
          <button id="modal-inc-qty" class="w-8 h-8 rounded-lg hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center">+</button>
        </div>

        <button id="modal-add-btn" class="flex-1 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
          Adicionar ao Carrinho
        </button>
      </div>
    </div>
  `;

  let qty = 1;
  const decBtn = document.getElementById('modal-dec-qty');
  const incBtn = document.getElementById('modal-inc-qty');
  const qtyVal = document.getElementById('modal-qty-val');
  const addBtn = document.getElementById('modal-add-btn');

  decBtn?.addEventListener('click', () => {
    if (qty > 1) {
      qty--;
      if (qtyVal) qtyVal.textContent = qty;
    }
  });

  incBtn?.addEventListener('click', () => {
    qty++;
    if (qtyVal) qtyVal.textContent = qty;
  });

  addBtn?.addEventListener('click', () => {
    cartStore.addItem(product, qty);
    closeProductModal();
  });

  document.getElementById('close-prod-modal-btn')?.addEventListener('click', closeProductModal);

  overlay?.classList.remove('opacity-0', 'pointer-events-none');
  panel?.classList.remove('scale-95');
}

export function closeProductModal() {
  const overlay = document.getElementById('product-modal-overlay');
  const panel = document.getElementById('product-modal-panel');
  overlay?.classList.add('opacity-0', 'pointer-events-none');
  panel?.classList.add('scale-95');
}
