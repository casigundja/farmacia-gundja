import { supabaseService } from '../supabase.js';
import { formatKz } from '../data/initialData.js';
import { Toast } from './Toast.js';

export function renderAdminModal() {
  const orders = supabaseService.getRecentOrders();
  const isConfigured = supabaseService.isConfigured();

  return `
    <div id="admin-modal-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity duration-300 opacity-0 pointer-events-none">
      <div id="admin-modal-panel" class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform scale-95 transition-transform duration-300">
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur-sm z-10">
          <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-800 flex items-center justify-center text-sm font-bold">⚙</span>
            <div>
              <h2 class="text-lg font-bold text-slate-800">Painel de Gestão & Supabase</h2>
              <p class="text-xs text-slate-500">Farmácia Gundja • Cloudflare Static SPA</p>
            </div>
          </div>
          <button id="close-admin-btn" class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-full transition-colors">
            ✕
          </button>
        </div>

        <div class="p-6 space-y-6">
          <!-- Supabase Status Card -->
          <div class="p-4 rounded-2xl border ${isConfigured ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/40'}">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full ${isConfigured ? 'bg-emerald-500' : 'bg-amber-500'}"></span>
                <span class="text-xs font-bold text-slate-800">Estado da Ligação Supabase</span>
              </div>
              <span class="text-[11px] font-semibold ${isConfigured ? 'text-emerald-700' : 'text-amber-700'}">
                ${isConfigured ? 'Conectado Live' : 'Modo Standalone / Cache'}
              </span>
            </div>
            
            <p class="text-xs text-slate-600 mb-3">
              O catálogo está sincronizado com o banco PostgreSQL no Supabase (<strong>ffptcomlhcigzdargsre</strong>). 
              ${!isConfigured ? 'Para activar operações em tempo real via navegador, insira a Chave Anon pública do Supabase abaixo.' : ''}
            </p>

            <form id="supabase-config-form" class="space-y-3">
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Supabase Project URL</label>
                <input type="text" id="cfg-supabase-url" value="${supabaseService.url}" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white">
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Supabase Anon Public Key</label>
                <input type="password" id="cfg-supabase-key" value="${supabaseService.anonKey}" placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white font-mono">
              </div>
              <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-colors">
                  Guardar Chave
                </button>
                <button type="button" id="test-supabase-btn" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors">
                  Testar Ligação
                </button>
              </div>
            </form>
          </div>

          <!-- Recent Orders in this Browser -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-sm font-bold text-slate-800">Últimos Pedidos Registados</h3>
              <span class="text-xs text-slate-400">${orders.length} pedidos</span>
            </div>

            ${orders.length === 0 ? `
              <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-slate-100">
                Nenhum pedido registado nesta sessão ainda.
              </div>
            ` : `
              <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                ${orders.map(o => `
                  <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl flex items-center justify-between text-xs">
                    <div>
                      <div class="font-bold text-slate-800 flex items-center gap-2">
                        <span>${o.publicId}</span>
                        <span class="text-[10px] font-semibold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">${o.paymentMethod}</span>
                      </div>
                      <div class="text-slate-500 mt-0.5">
                        ${o.customerName} • ${o.customerPhone} • ${o.items?.length || 0} itens
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="font-bold text-emerald-700">${formatKz(o.total)}</div>
                      <div class="text-[10px] text-slate-400">${new Date(o.createdAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                    </div>
                  </div>
                `).join('')}
              </div>
            `}
          </div>

          <!-- Pharmacy branches info -->
          <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs text-slate-600">
            <h4 class="font-bold text-slate-800 mb-1">Filiais Farmácia Gundja Activas:</h4>
            <ul class="list-disc list-inside space-y-1 text-slate-500">
              <li><strong>Sede Luanda Centro:</strong> Rua Rainha Ginga, Ingombota</li>
              <li><strong>Talatona:</strong> Av. Luanda Sul, Shopping Talatona</li>
              <li><strong>Viana:</strong> Estrada de Catete, Km 14</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  `;
}

export function initAdminEvents() {
  const overlay = document.getElementById('admin-modal-overlay');
  const panel = document.getElementById('admin-modal-panel');
  const closeBtn = document.getElementById('close-admin-btn');
  const form = document.getElementById('supabase-config-form');
  const testBtn = document.getElementById('test-supabase-btn');

  closeBtn?.addEventListener('click', closeAdminModal);

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    const url = document.getElementById('cfg-supabase-url').value;
    const key = document.getElementById('cfg-supabase-key').value;
    supabaseService.setCredentials(url, key);
    Toast.show('Configuração do Supabase actualizada!', 'success');
  });

  testBtn?.addEventListener('click', async () => {
    testBtn.textContent = 'Testando...';
    const res = await supabaseService.testConnection();
    testBtn.textContent = 'Testar Ligação';
    Toast.show(res.message, res.success ? 'success' : 'error');
  });
}

export function openAdminModal() {
  const overlay = document.getElementById('admin-modal-overlay');
  const panel = document.getElementById('admin-modal-panel');
  overlay?.classList.remove('opacity-0', 'pointer-events-none');
  panel?.classList.remove('scale-95');
}

export function closeAdminModal() {
  const overlay = document.getElementById('admin-modal-overlay');
  const panel = document.getElementById('admin-modal-panel');
  overlay?.classList.add('opacity-0', 'pointer-events-none');
  panel?.classList.add('scale-95');
}
