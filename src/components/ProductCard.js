import { formatKz } from '../data/initialData.js';

export function renderProductCard(product, currentBranch = 'luanda') {
  const stock = currentBranch === 'luanda' ? product.stock?.luanda : product.stock?.bailundo;
  const isAvailable = (stock || 0) > 0;
  const discountPercent = product.price > product.sale_price 
    ? Math.round(((product.price - product.sale_price) / product.price) * 100) 
    : 0;

  return `
    <article class="group bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden relative" data-id="${product.id}">
      <!-- Badge Section -->
      <div class="absolute top-3 left-3 z-10 flex flex-col gap-1 items-start">
        ${product.badge ? `<span class="bg-emerald-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full shadow-sm">${product.badge}</span>` : ''}
        ${discountPercent > 0 ? `<span class="bg-rose-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">-${discountPercent}%</span>` : ''}
        ${product.requires_prescription ? `<span class="bg-amber-500 text-white text-xs font-medium px-2 py-0.5 rounded-full flex items-center gap-1">⚠ Receita</span>` : ''}
      </div>

      <!-- Image Area -->
      <div class="relative w-full aspect-square bg-slate-50 overflow-hidden cursor-pointer product-detail-trigger" data-id="${product.id}">
        <img 
          src="${product.image}" 
          alt="${product.name}" 
          loading="lazy"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          onerror="this.src='https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80'"
        />
        <div class="absolute inset-0 bg-slate-900/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
          <span class="bg-white/95 text-slate-800 text-xs font-semibold px-3 py-1.5 rounded-full shadow">Ver Detalhes</span>
        </div>
      </div>

      <!-- Content Area -->
      <div class="p-4 flex-1 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between text-xs text-slate-400 font-medium mb-1.5">
            <span>${product.brand || 'Farmácia Gundja'}</span>
            <span>SKU: ${product.sku}</span>
          </div>

          <h3 class="text-sm font-semibold text-slate-800 group-hover:text-emerald-600 transition-colors line-clamp-2 leading-snug cursor-pointer product-detail-trigger" data-id="${product.id}">
            ${product.name}
          </h3>

          <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
            ${product.short_desc}
          </p>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100">
          <div class="flex items-baseline gap-2 mb-2">
            <span class="text-lg font-bold text-emerald-700">${formatKz(product.sale_price)}</span>
            ${product.price > product.sale_price ? `<span class="text-xs text-slate-400 line-through">${formatKz(product.price)}</span>` : ''}
          </div>

          <div class="flex items-center justify-between gap-2">
            <div class="text-[11px] font-medium ${isAvailable ? 'text-emerald-600' : 'text-rose-500'} flex items-center gap-1">
              <span class="w-2 h-2 rounded-full ${isAvailable ? 'bg-emerald-500' : 'bg-rose-500'}"></span>
              ${isAvailable ? `${stock} em stock` : 'Esgotado nesta filial'}
            </div>

            <button 
              class="add-to-cart-btn inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold transition-all duration-200 ${
                isAvailable 
                  ? 'bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white shadow-sm hover:shadow' 
                  : 'bg-slate-100 text-slate-400 cursor-not-allowed'
              }"
              data-id="${product.id}"
              ${!isAvailable ? 'disabled' : ''}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
              Adicionar
            </button>
          </div>
        </div>
      </div>
    </article>
  `;
}
