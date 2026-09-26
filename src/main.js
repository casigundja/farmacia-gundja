import './style.css';
import { initialBranches, initialCategories, initialBrands } from './data/initialData.js';
import { supabaseService } from './supabase.js';
import { cartStore, renderCartDrawer } from './components/Cart.js';
import { renderProductCard } from './components/ProductCard.js';
import { renderProductModal, openProductModal, closeProductModal } from './components/ProductModal.js';
import { renderCheckoutModal, initCheckoutEvents, openCheckout, closeCheckout } from './components/CheckoutModal.js';
import { renderAdminModal, initAdminEvents, openAdminModal } from './components/AdminModal.js';
import { Toast } from './components/Toast.js';

let state = {
  products: [],
  categories: initialCategories,
  brands: initialBrands,
  currentBranch: localStorage.getItem('fg_branch') || 'luanda',
  selectedCategory: 'todos',
  selectedBrand: 'todas',
  searchQuery: '',
  sortBy: 'featured',
  isLoading: true
};

async function init() {
  const app = document.getElementById('app');
  app.innerHTML = renderAppShell();

  // Attach persistent modals
  renderModals();

  // Load products & categories
  try {
    const [prodResult, catResult] = await Promise.all([
      supabaseService.getProducts(),
      supabaseService.getCategories()
    ]);
    state.products = prodResult.data;
    state.categories = catResult;
  } catch (err) {
    console.error('Erro ao carregar dados:', err);
  } finally {
    state.isLoading = false;
  }

  // Initial renders
  renderCategories();
  renderProducts();
  updateCartBadge();

  // Set up event listeners
  bindEvents();
  initCheckoutEvents();
  initAdminEvents();

  // Cart store listener
  cartStore.subscribe(() => {
    updateCartBadge();
    const drawerContainer = document.getElementById('cart-drawer-container');
    if (drawerContainer) {
      drawerContainer.innerHTML = renderCartDrawer();
      bindCartDrawerEvents();
    }
  });
}

function renderAppShell() {
  const currentBranchObj = initialBranches.find(b => b.slug === `farmacia-${state.currentBranch}`) || initialBranches[0];

  return `
    <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800">
      <!-- Top announcement bar -->
      <div class="bg-[#3b0a1e] text-white text-[11px] py-1.5 px-4 border-b border-[#5c0e2c]">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-1">
          <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-[#eea876] animate-pulse"></span>
            <span>Farmácia Gundja · <strong>Saúde, confiança e cuidado perto de si</strong> · Luanda (Sede Centro, Talatona e Viana)</span>
          </div>
          <div class="flex items-center gap-4 text-[#f7e9ee]">
            <span>WhatsApp Apoio: <strong>+244 923 000 001</strong></span>
            <button id="header-admin-trigger" class="hover:text-white transition-colors underline font-medium cursor-pointer">Área de Gestão</button>
          </div>
        </div>
      </div>

      <!-- Main Header / Nav -->
      <header class="sticky top-0 z-40 bg-white/95 glass-nav border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
          <!-- Logo & Brand -->
          <div class="flex items-center gap-3">
            <a href="#" class="flex items-center gap-3 group">
              <img src="/images/logo.png" alt="Farmácia Gundja" class="h-12 w-auto object-contain rounded-lg drop-shadow-sm group-hover:scale-105 transition-transform" onerror="this.style.display='none'">
              <div>
                <span class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-1">
                  Farmácia <span class="text-[#5c0e2c]">Gundja</span>
                </span>
                <span class="block text-[10px] uppercase font-bold text-[#5c0e2c] tracking-widest">Saúde, Confiança & Cuidado</span>
              </div>
            </a>
          </div>

          <!-- Search Bar -->
          <div class="flex-1 max-w-md hidden md:block">
            <div class="relative">
              <input 
                type="text" 
                id="search-input" 
                placeholder="Pesquisar medicamento, pomada, vitamina, marca..." 
                class="w-full text-xs pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/80 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#5c0e2c]/20 focus:border-[#5c0e2c] transition-all"
              >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3.5 top-3 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
          </div>

          <!-- Actions: Branch Selector & Cart -->
          <div class="flex items-center gap-3">
            <!-- Branch Selector -->
            <div class="flex items-center bg-slate-100 p-1 rounded-2xl border border-slate-200/60 text-xs font-semibold">
              <button class="branch-btn px-2.5 py-1.5 rounded-xl transition-all ${state.currentBranch === 'luanda' ? 'bg-[#5c0e2c] text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'}" data-branch="luanda">
                Sede Centro
              </button>
              <button class="branch-btn px-2.5 py-1.5 rounded-xl transition-all ${state.currentBranch === 'talatona' ? 'bg-[#5c0e2c] text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'}" data-branch="talatona">
                Talatona
              </button>
              <button class="branch-btn px-2.5 py-1.5 rounded-xl transition-all ${state.currentBranch === 'viana' ? 'bg-[#5c0e2c] text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'}" data-branch="viana">
                Viana
              </button>
            </div>

            <!-- WhatsApp Direct CTA -->
            <a href="https://wa.me/244923000001?text=Ol%C3%A1%20Farm%C3%A1cia%20Gundja,%20gostaria%20de%20informa%C3%A7%C3%B5es." target="_blank" class="hidden lg:flex items-center gap-1.5 bg-[#25D366]/10 text-[#128C7E] hover:bg-[#25D366]/20 font-bold text-xs px-3.5 py-2.5 rounded-2xl transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
              <span>WhatsApp Farmácia</span>
            </a>

            <!-- Cart Trigger Button -->
            <button id="open-cart-btn" class="relative flex items-center justify-center w-11 h-11 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white shadow-md hover:shadow-lg transition-all">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
              <span id="header-cart-badge" class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-500 text-white font-bold text-[10px] flex items-center justify-center border-2 border-white scale-0 transition-transform">0</span>
            </button>
          </div>
        </div>

        <!-- Mobile search bar -->
        <div class="px-4 pb-3 md:hidden">
          <div class="relative">
            <input 
              type="text" 
              id="search-input-mobile" 
              placeholder="Pesquisar medicamento, pomada, marca..." 
              class="w-full text-xs pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
            >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3.5 top-3 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1">
        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-[#3b0a1e] via-[#5c0e2c] to-[#762542] text-white overflow-hidden py-12 lg:py-16 px-4 sm:px-6 lg:px-8">
          <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
          <div class="max-w-7xl mx-auto relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-7 space-y-6">
              <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-[#eea876] text-xs font-semibold px-3 py-1.5 rounded-full backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-[#eea876] animate-ping"></span>
                <span>Farmácia Gundja · Angola (Kz)</span>
              </div>
              <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                Saúde, confiança e cuidado perto de si.
              </h1>
              <p class="text-sm sm:text-base text-rose-100 max-w-xl leading-relaxed">
                Medicamentos 100% autênticos, cuidados infantis, dermocosmética e apoio farmacêutico de referência em Luanda com entregas rápidas ao domicílio.
              </p>
              
              <div class="flex flex-wrap gap-3 pt-2">
                <a href="#catalogo" class="px-6 py-3.5 rounded-2xl bg-white text-[#5c0e2c] font-bold text-xs hover:bg-[#f7e9ee] active:scale-95 transition-all shadow-lg flex items-center gap-2">
                  <span>Consultar Catálogo de Produtos</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
                <a href="https://wa.me/244923000001?text=Ol%C3%A1%20Farm%C3%A1cia%20Gundja,%20tenho%20uma%20receita%20m%C3%A9dica%20para%20enviar." target="_blank" class="px-5 py-3.5 rounded-2xl bg-[#3b0a1e]/80 hover:bg-[#3b0a1e] border border-white/20 text-white font-bold text-xs transition-all flex items-center gap-2">
                  <span>WhatsApp Apoio Farmacêutico</span>
                </a>
              </div>

              <!-- Badges -->
              <div class="grid grid-cols-3 gap-3 pt-4 border-t border-white/10 text-rose-100 text-xs">
                <div>
                  <div class="font-bold text-white text-sm">100% Genuíno</div>
                  <div class="text-[11px] text-[#eea876]">Medicamentos certificados</div>
                </div>
                <div>
                  <div class="font-bold text-white text-sm">3 Filiais</div>
                  <div class="text-[11px] text-[#eea876]">Sede, Talatona & Viana</div>
                </div>
                <div>
                  <div class="font-bold text-white text-sm">Entregas Rápidas</div>
                  <div class="text-[11px] text-[#eea876]">Ao domicílio em Luanda</div>
                </div>
              </div>
            </div>

            <!-- Prescription quick box on Hero right -->
            <div class="lg:col-span-5">
              <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-6 text-white shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-2xl bg-emerald-500/30 flex items-center justify-center text-2xl font-bold">
                    📋
                  </div>
                  <div>
                    <h3 class="font-bold text-base">Tem uma Receita Médica?</h3>
                    <p class="text-xs text-emerald-200">Envie a fotografia para cotação e preparação imediata.</p>
                  </div>
                </div>

                <div class="space-y-2 text-xs text-emerald-100">
                  <div class="flex items-center gap-2">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>Verificação rigorosa por farmacêutico credenciado.</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>Confirmação de stock e preço em menos de 10 minutos.</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="text-emerald-400 font-bold">✓</span>
                    <span>Opção de levantamento expresso ou entrega ao domicílio.</span>
                  </div>
                </div>

                <a href="https://wa.me/244923111222?text=Ol%C3%A1%20Farmac%C3%AAutico%20Gundja,%20estou%20a%20enviar%20a%20fotografia%20da%20minha%20receita%20m%C3%A9dica%20para%20prepara%C3%A7%C3%A3o." target="_blank" class="w-full py-3.5 bg-[#25D366] hover:bg-[#20ba59] active:scale-[0.98] text-white font-bold text-xs rounded-2xl shadow flex items-center justify-center gap-2 transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                  <span>Fotografar e Enviar por WhatsApp</span>
                </a>
              </div>
            </div>
          </div>
        </section>

        <!-- Catalog Container -->
        <section id="catalogo" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
          <!-- Categories Bar -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-bold text-slate-900 tracking-tight">Categorias em Destaque</h2>
              <span id="active-branch-indicator" class="text-xs text-slate-500 font-medium">Filial actual: <strong>${currentBranchObj.name}</strong></span>
            </div>
            <div id="category-pills" class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
              <!-- Dynamically populated -->
            </div>
          </div>

          <!-- Brand and Sort Filter Bar -->
          <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
              <span class="text-xs font-bold text-slate-600 shrink-0">Marca:</span>
              <div id="brand-filters" class="flex gap-1.5">
                <button class="brand-filter-btn text-xs font-medium px-3 py-1.5 rounded-xl border transition-colors ${state.selectedBrand === 'todas' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'}" data-brand="todas">
                  Todas
                </button>
                ${state.brands.map(b => `
                  <button class="brand-filter-btn text-xs font-medium px-3 py-1.5 rounded-xl border transition-colors ${state.selectedBrand === b ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'}" data-brand="${b}">
                    ${b}
                  </button>
                `).join('')}
              </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
              <span id="results-count" class="text-xs text-slate-500 font-medium">0 produtos</span>
              <div class="flex items-center gap-2">
                <label for="sort-select" class="text-xs text-slate-500 font-semibold shrink-0">Ordenar:</label>
                <select id="sort-select" class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 bg-white font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                  <option value="featured">Destaques</option>
                  <option value="price-asc">Menor Preço</option>
                  <option value="price-desc">Maior Preço</option>
                  <option value="name-asc">Nome (A-Z)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Product Grid -->
          <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Dynamically populated -->
          </div>
        </section>

        <!-- Our Pharmacy Branches Section -->
        <section id="filiais" class="bg-white border-t border-slate-200/80 py-12 px-4 sm:px-6 lg:px-8">
          <div class="max-w-7xl mx-auto space-y-8">
            <div class="text-center max-w-2xl mx-auto space-y-2">
              <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Localização e Atendimento</span>
              <h2 class="text-2xl font-bold text-slate-900">Visite as Nossas Farmácias</h2>
              <p class="text-xs sm:text-sm text-slate-500">Estamos presentes em Luanda com 3 unidades modernas e farmacêuticos dedicados à sua saúde e bem-estar.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              ${initialBranches.map(b => `
                <div class="p-6 rounded-3xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition-all space-y-4">
                  <div class="flex items-start justify-between">
                    <div>
                      <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">${b.province}</span>
                      <h3 class="text-base font-bold text-slate-900">${b.name}</h3>
                    </div>
                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2.5 py-1 rounded-full">● Aberto</span>
                  </div>

                  <div class="space-y-2 text-xs text-slate-600">
                    <p class="flex items-center gap-2">
                      <span class="text-slate-400">📍</span>
                      <span>${b.address}</span>
                    </p>
                    <p class="flex items-center gap-2">
                      <span class="text-slate-400">⏰</span>
                      <span>${b.hours}</span>
                    </p>
                    <p class="flex items-center gap-2">
                      <span class="text-slate-400">📞</span>
                      <span>Contacto: <strong>${b.phone}</strong></span>
                    </p>
                  </div>

                  <div class="pt-2 flex gap-3">
                    <a href="https://wa.me/${b.whatsapp}?text=Ol%C3%A1%20Farm%C3%A1cia%20Gundja%20de%20${encodeURIComponent(b.city)},%20gostaria%20de%20falar%20com%20o%20farmac%C3%AAutico." target="_blank" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl text-center shadow-sm transition-colors">
                      WhatsApp Filial ${b.city}
                    </a>
                  </div>
                </div>
              `).join('')}
            </div>
          </div>
        </section>
      </main>

      <!-- Footer -->
      <footer class="bg-slate-900 text-slate-400 text-xs py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 pb-8 border-b border-slate-800">
          <div class="space-y-3">
            <div class="flex items-center gap-2">
              <img src="/images/symbol.png" alt="Símbolo Farmácia Gundja" class="w-8 h-8 rounded-lg object-contain bg-white/10 p-1" onerror="this.style.display='none'">
              <span class="text-base font-bold text-white tracking-tight">Farmácia Gundja</span>
            </div>
            <p class="text-[#eea876] font-medium text-xs italic">
              "Saúde, confiança e cuidado perto de si."
            </p>
            <p class="text-slate-400 leading-relaxed text-[11px]">
              Plataforma oficial de gestão farmacêutica, medicamentos e dermocosméticos em Angola. Atendimento presencial nas nossas farmácias e entregas ao domicílio em Luanda.
            </p>
            <p class="text-[11px] text-emerald-400 font-semibold">
              ● Multicaixa Express & Transferência Bancária (Kz / AOA)
            </p>
          </div>

          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Unidades Luanda</h4>
            <ul class="space-y-2 text-[11px]">
              <li>Sede Luanda Centro (Ingombota)</li>
              <li>Filial Talatona (Shopping Talatona)</li>
              <li>Filial Viana (Estrada de Catete, Km 14)</li>
              <li>Entregas ao domicílio em Luanda</li>
            </ul>
          </div>

          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Categorias</h4>
            <ul class="space-y-2 text-[11px]">
              <li>Medicamentos & Saúde</li>
              <li>Higiene & Cuidados</li>
              <li>Dermocosmética & Beleza</li>
              <li>Mamã & Bebé</li>
              <li>Suplementos & Vitaminas</li>
            </ul>
          </div>

          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Avisos Legais & Sanitários</h4>
            <p class="text-[11px] text-slate-400 leading-relaxed">
              Medicamentos sujeitos a receita médica só podem ser dispensados mediante apresentação de receita médica válida e validação profissional farmacêutica, em estrita conformidade com a regulamentação sanitária de Angola.
            </p>
          </div>
        </div>

        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px]">
          <div>
            © 2026 Farmácia Gundja. Todos os direitos reservados. Moeda oficial: Kwanza (Kz / AOA).
          </div>
          <div class="flex gap-4">
            <a href="#" class="hover:text-white transition-colors">Termos de Uso</a>
            <a href="#" class="hover:text-white transition-colors">Privacidade</a>
            <a href="#" class="hover:text-white transition-colors">Regulamentação Sanitária</a>
          </div>
        </div>
      </footer>

      <!-- Modals Container -->
      <div id="cart-drawer-container"></div>
      <div id="checkout-modal-container"></div>
      <div id="product-modal-container"></div>
      <div id="admin-modal-container"></div>
    </div>
  `;
}

function renderModals() {
  document.getElementById('cart-drawer-container').innerHTML = renderCartDrawer();
  document.getElementById('checkout-modal-container').innerHTML = renderCheckoutModal();
  document.getElementById('product-modal-container').innerHTML = renderProductModal();
  document.getElementById('admin-modal-container').innerHTML = renderAdminModal();
}

function renderCategories() {
  const container = document.getElementById('category-pills');
  if (!container) return;

  container.innerHTML = state.categories.map(cat => `
    <button 
      class="cat-pill-btn shrink-0 text-xs font-semibold px-4 py-2 rounded-2xl border transition-all ${
        state.selectedCategory === cat.slug 
          ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' 
          : 'bg-white text-slate-600 border-slate-200/80 hover:border-slate-300 hover:bg-slate-50'
      }"
      data-category="${cat.slug}"
    >
      ${cat.name}
    </button>
  `).join('');
}

function renderProducts() {
  const grid = document.getElementById('product-grid');
  const countEl = document.getElementById('results-count');
  if (!grid) return;

  if (state.isLoading) {
    grid.innerHTML = Array(6).fill(0).map(() => `
      <div class="bg-white rounded-2xl border border-slate-100 p-4 animate-pulse space-y-3">
        <div class="w-full aspect-square bg-slate-200 rounded-xl"></div>
        <div class="h-4 bg-slate-200 rounded w-3/4"></div>
        <div class="h-3 bg-slate-100 rounded w-1/2"></div>
        <div class="h-6 bg-slate-200 rounded w-1/3"></div>
      </div>
    `).join('');
    return;
  }

  let filtered = [...state.products];

  // Category filter
  if (state.selectedCategory && state.selectedCategory !== 'todos') {
    filtered = filtered.filter(p => p.category === state.selectedCategory);
  }

  // Brand filter
  if (state.selectedBrand && state.selectedBrand !== 'todas') {
    filtered = filtered.filter(p => p.brand === state.selectedBrand);
  }

  // Search filter
  if (state.searchQuery.trim()) {
    const q = state.searchQuery.toLowerCase();
    filtered = filtered.filter(p => 
      p.name.toLowerCase().includes(q) ||
      (p.short_desc && p.short_desc.toLowerCase().includes(q)) ||
      (p.brand && p.brand.toLowerCase().includes(q)) ||
      (p.sku && p.sku.toLowerCase().includes(q))
    );
  }

  // Sorting
  if (state.sortBy === 'price-asc') {
    filtered.sort((a, b) => a.sale_price - b.sale_price);
  } else if (state.sortBy === 'price-desc') {
    filtered.sort((a, b) => b.sale_price - a.sale_price);
  } else if (state.sortBy === 'name-asc') {
    filtered.sort((a, b) => a.name.localeCompare(b.name));
  }

  if (countEl) {
    countEl.textContent = `${filtered.length} produto${filtered.length === 1 ? '' : 's'}`;
  }

  if (filtered.length === 0) {
    grid.innerHTML = `
      <div class="col-span-full py-16 text-center text-slate-400 space-y-3">
        <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-2xl">🔍</div>
        <h3 class="font-bold text-slate-700 text-sm">Nenhum produto encontrado</h3>
        <p class="text-xs text-slate-400">Tente ajustar a busca ou seleccionar outra categoria.</p>
        <button id="reset-filters-btn" class="text-xs font-semibold text-emerald-600 hover:underline">Limpar filtros</button>
      </div>
    `;
    document.getElementById('reset-filters-btn')?.addEventListener('click', () => {
      state.selectedCategory = 'todos';
      state.selectedBrand = 'todas';
      state.searchQuery = '';
      const s1 = document.getElementById('search-input');
      const s2 = document.getElementById('search-input-mobile');
      if (s1) s1.value = '';
      if (s2) s2.value = '';
      renderCategories();
      renderProducts();
    });
    return;
  }

  grid.innerHTML = filtered.map(p => renderProductCard(p, state.currentBranch)).join('');
  bindProductCardEvents();
}

function bindEvents() {
  // Branch switcher
  document.querySelectorAll('.branch-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const branch = e.currentTarget.dataset.branch;
      state.currentBranch = branch;
      localStorage.setItem('fg_branch', branch);

      document.querySelectorAll('.branch-btn').forEach(b => {
        const isActive = b.dataset.branch === branch;
        b.className = `branch-btn px-3 py-1.5 rounded-xl transition-all ${isActive ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-800'}`;
      });

      const branchObj = initialBranches.find(b => b.slug === `farmacia-${branch}`) || initialBranches[0];
      const indicator = document.getElementById('active-branch-indicator');
      if (indicator) indicator.innerHTML = `Filial actual: <strong>${branchObj.name}</strong>`;

      renderProducts();
      Toast.show(`Filial alterada para ${branchObj.name}`, 'info');
    });
  });

  // Category filter
  document.getElementById('category-pills')?.addEventListener('click', (e) => {
    const btn = e.target.closest('.cat-pill-btn');
    if (!btn) return;
    state.selectedCategory = btn.dataset.category;
    renderCategories();
    renderProducts();
  });

  // Brand filter
  document.getElementById('brand-filters')?.addEventListener('click', (e) => {
    const btn = e.target.closest('.brand-filter-btn');
    if (!btn) return;
    state.selectedBrand = btn.dataset.brand;
    document.querySelectorAll('.brand-filter-btn').forEach(b => {
      const active = b.dataset.brand === state.selectedBrand;
      b.className = `brand-filter-btn text-xs font-medium px-3 py-1.5 rounded-xl border transition-colors ${active ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'}`;
    });
    renderProducts();
  });

  // Search input with instant filtering
  const handleSearch = (e) => {
    state.searchQuery = e.target.value;
    renderProducts();
  };

  document.getElementById('search-input')?.addEventListener('input', handleSearch);
  document.getElementById('search-input-mobile')?.addEventListener('input', handleSearch);

  // Sort select
  document.getElementById('sort-select')?.addEventListener('change', (e) => {
    state.sortBy = e.target.value;
    renderProducts();
  });

  // Open Cart Drawer
  document.getElementById('open-cart-btn')?.addEventListener('click', openCartDrawer);

  // Open Admin Modal
  document.getElementById('header-admin-trigger')?.addEventListener('click', openAdminModal);
}

function bindProductCardEvents() {
  // Add to cart buttons
  document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const id = parseInt(e.currentTarget.dataset.id);
      const product = state.products.find(p => p.id === id);
      if (product) {
        cartStore.addItem(product, 1);
      }
    });
  });

  // Product detail triggers
  document.querySelectorAll('.product-detail-trigger').forEach(el => {
    el.addEventListener('click', (e) => {
      const id = parseInt(e.currentTarget.dataset.id);
      const product = state.products.find(p => p.id === id);
      if (product) {
        openProductModal(product);
      }
    });
  });
}

function bindCartDrawerEvents() {
  const overlay = document.getElementById('cart-drawer-overlay');
  const panel = document.getElementById('cart-drawer-panel');
  const closeBtn = document.getElementById('close-cart-btn');
  const checkoutBtn = document.getElementById('open-checkout-btn');
  const startShopBtn = document.getElementById('cart-start-shopping');

  closeBtn?.addEventListener('click', closeCartDrawer);
  overlay?.addEventListener('click', (e) => {
    if (e.target === overlay) closeCartDrawer();
  });

  checkoutBtn?.addEventListener('click', () => {
    closeCartDrawer();
    openCheckout();
  });

  startShopBtn?.addEventListener('click', () => {
    closeCartDrawer();
    document.getElementById('catalogo')?.scrollIntoView({ behavior: 'smooth' });
  });

  // Quantity adjustments
  document.querySelectorAll('.cart-qty-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = parseInt(e.currentTarget.dataset.id);
      const action = e.currentTarget.dataset.action;
      const item = cartStore.items.find(i => i.product.id === id);
      if (item) {
        const newQty = action === 'inc' ? item.quantity + 1 : item.quantity - 1;
        cartStore.updateQuantity(id, newQty);
      }
    });
  });

  // Remove buttons
  document.querySelectorAll('.cart-remove-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = parseInt(e.currentTarget.dataset.id);
      cartStore.removeItem(id);
    });
  });
}

function openCartDrawer() {
  const overlay = document.getElementById('cart-drawer-overlay');
  const panel = document.getElementById('cart-drawer-panel');
  overlay?.classList.remove('opacity-0', 'pointer-events-none');
  panel?.classList.remove('translate-x-full');
}

function closeCartDrawer() {
  const overlay = document.getElementById('cart-drawer-overlay');
  const panel = document.getElementById('cart-drawer-panel');
  overlay?.classList.add('opacity-0', 'pointer-events-none');
  panel?.classList.add('translate-x-full');
}

function updateCartBadge() {
  const badge = document.getElementById('header-cart-badge');
  const count = cartStore.getCount();
  if (badge) {
    badge.textContent = count;
    if (count > 0) {
      badge.classList.remove('scale-0');
    } else {
      badge.classList.add('scale-0');
    }
  }
}

// Start app
window.addEventListener('DOMContentLoaded', init);
