import { formatKz } from '../data/initialData.js';
import { Toast } from './Toast.js';

class CartStore {
  constructor() {
    this.items = JSON.parse(localStorage.getItem('fg_cart') || '[]');
    this.listeners = [];
  }

  save() {
    localStorage.setItem('fg_cart', JSON.stringify(this.items));
    this.notify();
  }

  subscribe(callback) {
    this.listeners.push(callback);
    callback(this.items);
  }

  notify() {
    this.listeners.forEach(fn => fn(this.items));
  }

  addItem(product, quantity = 1) {
    const existing = this.items.find(i => i.product.id === product.id);
    if (existing) {
      existing.quantity += quantity;
    } else {
      this.items.push({ product, quantity });
    }
    this.save();
    Toast.show(`"${product.name}" adicionado ao carrinho!`, 'success');
  }

  updateQuantity(productId, quantity) {
    const item = this.items.find(i => i.product.id === productId);
    if (!item) return;

    if (quantity <= 0) {
      this.removeItem(productId);
    } else {
      item.quantity = quantity;
      this.save();
    }
  }

  removeItem(productId) {
    this.items = this.items.filter(i => i.product.id !== productId);
    this.save();
    Toast.show('Item removido do carrinho.', 'info');
  }

  clear() {
    this.items = [];
    this.save();
  }

  getCount() {
    return this.items.reduce((sum, i) => sum + i.quantity, 0);
  }

  getSubtotal() {
    return this.items.reduce((sum, i) => sum + (i.product.sale_price * i.quantity), 0);
  }
}

export const cartStore = new CartStore();

export function renderCartDrawer() {
  const items = cartStore.items;
  const subtotal = cartStore.getSubtotal();
  const count = cartStore.getCount();

  return `
    <div id="cart-drawer-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 transition-opacity duration-300 opacity-0 pointer-events-none">
      <div id="cart-drawer-panel" class="absolute top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col transform translate-x-full transition-transform duration-300 ease-out">
        <!-- Drawer Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
              ${count}
            </div>
            <div>
              <h2 class="font-bold text-slate-800 text-base">O Seu Carrinho</h2>
              <p class="text-xs text-slate-500">Farmácia Gundja Online</p>
            </div>
          </div>
          <button id="close-cart-btn" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
          </button>
        </div>

        <!-- Items Container -->
        <div class="flex-1 overflow-y-auto p-5 divide-y divide-slate-100">
          ${items.length === 0 ? `
            <div class="h-full flex flex-col items-center justify-center text-center p-8 text-slate-400">
              <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-3 text-slate-300">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
              </div>
              <p class="font-semibold text-slate-700 mb-1">O seu carrinho está vazio</p>
              <p class="text-xs text-slate-400 max-w-xs mb-4">Explore os nossos medicamentos, dermocosméticos e produtos de higiene e adicione ao carrinho.</p>
              <button id="cart-start-shopping" class="text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl transition-colors">
                Ver Catálogo de Medicamentos
              </button>
            </div>
          ` : items.map(item => `
            <div class="py-4 flex gap-3 items-center group">
              <img src="${item.product.image}" alt="${item.product.name}" class="w-16 h-16 object-cover rounded-xl bg-slate-100 border border-slate-100 shrink-0">
              <div class="flex-1 min-w-0">
                <h4 class="text-xs font-semibold text-slate-800 truncate">${item.product.name}</h4>
                <p class="text-[11px] text-slate-400">${item.product.brand || 'Gundja'}</p>
                <div class="text-xs font-bold text-emerald-700 mt-1">${formatKz(item.product.sale_price)}</div>
                
                <div class="flex items-center justify-between mt-2">
                  <div class="flex items-center border border-slate-200 rounded-lg bg-slate-50">
                    <button class="cart-qty-btn px-2 py-0.5 text-xs text-slate-600 hover:bg-slate-200 rounded-l-md transition-colors" data-id="${item.product.id}" data-action="dec">-</button>
                    <span class="px-2.5 py-0.5 text-xs font-bold text-slate-800">${item.quantity}</span>
                    <button class="cart-qty-btn px-2 py-0.5 text-xs text-slate-600 hover:bg-slate-200 rounded-r-md transition-colors" data-id="${item.product.id}" data-action="inc">+</button>
                  </div>
                  <button class="cart-remove-btn text-xs text-slate-400 hover:text-rose-500 transition-colors p-1" data-id="${item.product.id}" title="Remover item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                  </button>
                </div>
              </div>
            </div>
          `).join('')}
        </div>

        <!-- Drawer Footer -->
        ${items.length > 0 ? `
          <div class="p-5 border-t border-slate-100 bg-slate-50/80 flex flex-col gap-3">
            <div class="flex items-center justify-between">
              <span class="text-xs text-slate-500 font-medium">Subtotal</span>
              <span class="text-base font-bold text-slate-900">${formatKz(subtotal)}</span>
            </div>
            <div class="text-[11px] text-slate-400 flex items-center gap-1">
              <span>ⓘ</span>
              <span>Entrega rápida ou levantamento nas farmácias de Luanda e Bailundo.</span>
            </div>
            <button id="open-checkout-btn" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl font-bold text-sm shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
              <span>Finalizar Pedido</span>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </button>
          </div>
        ` : ''}
      </div>
    </div>
  `;
}
