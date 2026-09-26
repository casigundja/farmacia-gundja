import { createClient } from '@supabase/supabase-js';
import { initialProducts, initialCategories, initialBranches } from './data/initialData.js';

const DEFAULT_URL = 'https://ffptcomlhcigzdargsre.supabase.co';

class SupabaseService {
  constructor() {
    this.url = import.meta.env.VITE_SUPABASE_URL || localStorage.getItem('fg_supabase_url') || DEFAULT_URL;
    this.anonKey = import.meta.env.VITE_SUPABASE_ANON_KEY || localStorage.getItem('fg_supabase_anon_key') || '';
    this.client = null;
    this.initClient();
  }

  initClient() {
    if (this.url && this.anonKey) {
      try {
        this.client = createClient(this.url, this.anonKey);
      } catch (err) {
        console.warn('Falha ao inicializar cliente Supabase:', err);
        this.client = null;
      }
    } else {
      this.client = null;
    }
  }

  isConfigured() {
    return !!(this.client && this.anonKey);
  }

  setCredentials(url, anonKey) {
    this.url = url || DEFAULT_URL;
    this.anonKey = anonKey ? anonKey.trim() : '';
    localStorage.setItem('fg_supabase_url', this.url);
    if (this.anonKey) {
      localStorage.setItem('fg_supabase_anon_key', this.anonKey);
    } else {
      localStorage.removeItem('fg_supabase_anon_key');
    }
    this.initClient();
  }

  async testConnection() {
    if (!this.client) {
      return { success: false, message: 'Chave Anon do Supabase não configurada ainda.' };
    }
    try {
      const { data, error } = await this.client.from('categories').select('id').limit(1);
      if (error) throw error;
      return { success: true, message: 'Ligação ao Supabase estabelecida com sucesso!' };
    } catch (err) {
      return { success: false, message: err.message || 'Erro ao conectar ao Supabase.' };
    }
  }

  async getProducts() {
    if (!this.isConfigured()) {
      return { data: initialProducts, fromCache: true };
    }

    try {
      const { data, error } = await this.client
        .from('products')
        .select(`
          id,
          name,
          slug,
          sku,
          barcode,
          short_description,
          description,
          price,
          sale_price,
          unit,
          featured,
          active,
          categories (id, name, slug),
          brands (id, name, slug),
          inventories (branch_id, quantity)
        `)
        .eq('segment_id', 1)
        .eq('active', true);

      if (error) throw error;

      if (!data || data.length === 0) {
        return { data: initialProducts, fromCache: true };
      }

      const formatted = data.map(p => {
        const fallback = initialProducts.find(ip => ip.slug === p.slug || ip.sku === p.sku) || {};
        const luandaInv = p.inventories?.find(i => i.branch_id === 1)?.quantity || fallback.stock?.luanda || 50;
        const talatonaInv = p.inventories?.find(i => i.branch_id === 2)?.quantity || fallback.stock?.talatona || 30;
        const vianaInv = p.inventories?.find(i => i.branch_id === 3)?.quantity || fallback.stock?.viana || 20;

        return {
          id: p.id,
          name: p.name,
          slug: p.slug,
          sku: p.sku || fallback.sku,
          barcode: p.barcode,
          category: p.categories?.slug || fallback.category || 'medicamentos-saude',
          categoryName: p.categories?.name || fallback.categoryName || 'Medicamentos & Saúde',
          brand: p.brands?.name || fallback.brand || 'Gundja Essencial',
          price: parseFloat(p.price) || fallback.price,
          sale_price: parseFloat(p.sale_price) || fallback.sale_price || fallback.price,
          short_desc: p.short_description || fallback.short_desc,
          desc: p.description || fallback.desc,
          unit: p.unit || fallback.unit || 'unidade',
          featured: p.featured,
          requires_prescription: fallback.requires_prescription || false,
          stock: { luanda: luandaInv, talatona: talatonaInv, viana: vianaInv },
          badge: fallback.badge || (p.featured ? 'Destaque' : null),
          image: fallback.image || 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80'
        };
      });

      return { data: formatted, fromCache: false };
    } catch (err) {
      console.warn('Erro ao consultar Supabase, usando catálogo local:', err);
      return { data: initialProducts, fromCache: true };
    }
  }

  async getCategories() {
    if (!this.isConfigured()) {
      return initialCategories;
    }
    try {
      const { data, error } = await this.client
        .from('categories')
        .select('id, name, slug, description')
        .eq('segment_id', 1)
        .eq('active', true)
        .order('sort_order', { ascending: true });

      if (error || !data || data.length === 0) {
        return initialCategories;
      }

      const list = [{ id: 0, name: 'Todos', slug: 'todos', icon: 'layout-grid' }];
      data.forEach(c => {
        const fallback = initialCategories.find(ic => ic.slug === c.slug) || {};
        list.push({
          id: c.id,
          name: c.name,
          slug: c.slug,
          icon: fallback.icon || 'pill',
          description: c.description || fallback.description
        });
      });
      return list;
    } catch (err) {
      return initialCategories;
    }
  }

  async createOrder(orderPayload) {
    const publicId = 'FG-' + Math.floor(100000 + Math.random() * 900000);
    const orderData = {
      ...orderPayload,
      publicId,
      createdAt: new Date().toISOString()
    };

    // Always record locally so orders persist across sessions
    const localOrders = JSON.parse(localStorage.getItem('fg_orders') || '[]');
    localOrders.unshift(orderData);
    localStorage.setItem('fg_orders', JSON.stringify(localOrders));

    if (this.isConfigured()) {
      try {
        const { data: orderRow, error: orderErr } = await this.client
          .from('orders')
          .insert({
            public_id: publicId,
            segment_id: 1,
            branch_id: orderPayload.branchId || 1,
            status: 'PENDING',
            source: 'web_cloudflare',
            currency: 'AOA',
            total: orderPayload.total,
            notes: orderPayload.notes || '',
            customer_snapshot: {
              name: orderPayload.customerName,
              phone: orderPayload.customerPhone,
              address: orderPayload.deliveryType === 'delivery' ? orderPayload.address : 'Levantamento em Loja',
              paymentMethod: orderPayload.paymentMethod
            }
          })
          .select()
          .single();

        if (orderErr) throw orderErr;

        if (orderRow && orderPayload.items && orderPayload.items.length > 0) {
          const itemsToInsert = orderPayload.items.map(item => ({
            order_id: orderRow.id,
            product_id: item.id,
            quantity: item.quantity,
            unit_price: item.sale_price || item.price,
            subtotal: (item.sale_price || item.price) * item.quantity
          }));

          await this.client.from('order_items').insert(itemsToInsert);
        }

        return { success: true, publicId, synced: true };
      } catch (err) {
        console.warn('Erro ao sincronizar pedido com Supabase:', err);
        return { success: true, publicId, synced: false, warning: 'Salvo localmente e pronto para envio por WhatsApp.' };
      }
    }

    return { success: true, publicId, synced: false };
  }

  getRecentOrders() {
    return JSON.parse(localStorage.getItem('fg_orders') || '[]');
  }
}

export const supabaseService = new SupabaseService();
