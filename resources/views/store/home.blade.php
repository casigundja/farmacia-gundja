@extends('layouts.store')

@section('title', 'Saúde, confiança e cuidado perto de si')

@section('content')
<div class="wrap">
    <!-- Hero Banner Principal -->
    <section class="hero" style="background: linear-gradient(115deg, #3b0a1e 0%, #5c0e2c 55%, #832747 100%);">
        <div class="hero-copy">
            <span class="eyebrow" style="color:var(--peach)">Farmácia Gundja · Angola</span>
            <h1>Saúde, confiança e cuidado perto de si.</h1>
            <p>Medicamentos originais, produtos de saúde, higiene e bem-estar para toda a família. Entregas rápidas ao domicílio em Luanda ou retirada na farmácia.</p>
            
            <form class="search" action="{{ route('products.index') }}" method="GET" style="margin-top:20px">
                <input name="q" type="search" placeholder="Pesquisar por medicamento, marca ou sintoma..." aria-label="Pesquisar produtos">
                <button class="button" type="submit">Buscar</button>
            </form>
        </div>
    </section>

    <!-- Barra de Destaques de Confiança -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin:-35px auto 45px;position:relative;z-index:10">
        <div style="background:white;border:1px solid var(--line);border-radius:14px;padding:16px;box-shadow:0 6px 18px rgba(0,0,0,0.04);display:flex;align-items:center;gap:12px">
            <div style="font-size:24px;background:#f7e9ee;width:44px;height:44px;border-radius:10px;display:grid;place-items:center">🚚</div>
            <div>
                <strong style="font-size:13px;display:block">Entrega ao Domicílio</strong>
                <span class="muted" style="font-size:12px">Talatona, Viana e toda Luanda</span>
            </div>
        </div>
        <div style="background:white;border:1px solid var(--line);border-radius:14px;padding:16px;box-shadow:0 6px 18px rgba(0,0,0,0.04);display:flex;align-items:center;gap:12px">
            <div style="font-size:24px;background:#f7e9ee;width:44px;height:44px;border-radius:10px;display:grid;place-items:center">💊</div>
            <div>
                <strong style="font-size:13px;display:block">Medicamentos Genuínos</strong>
                <span class="muted" style="font-size:12px">100% originais e certificados</span>
            </div>
        </div>
        <div style="background:white;border:1px solid var(--line);border-radius:14px;padding:16px;box-shadow:0 6px 18px rgba(0,0,0,0.04);display:flex;align-items:center;gap:12px">
            <div style="font-size:24px;background:#f7e9ee;width:44px;height:44px;border-radius:10px;display:grid;place-items:center">📑</div>
            <div>
                <strong style="font-size:13px;display:block">Receitas Médicas</strong>
                <span class="muted" style="font-size:12px">Validação por farmacêuticos</span>
            </div>
        </div>
        <div style="background:white;border:1px solid var(--line);border-radius:14px;padding:16px;box-shadow:0 6px 18px rgba(0,0,0,0.04);display:flex;align-items:center;gap:12px">
            <div style="font-size:24px;background:#f7e9ee;width:44px;height:44px;border-radius:10px;display:grid;place-items:center">💳</div>
            <div>
                <strong style="font-size:13px;display:block">Multicaixa Express</strong>
                <span class="muted" style="font-size:12px">Pagamento simples em Kwanzas (Kz)</span>
            </div>
        </div>
    </div>

    <!-- Categorias do Catálogo -->
    <section class="section">
        <div class="section-head">
            <div>
                <span class="eyebrow" style="color:var(--wine)">Catálogo Farmacêutico</span>
                <h2>Explore por Categoria</h2>
                <p>Encontre os produtos essenciais para a sua saúde e bem-estar.</p>
            </div>
            <a class="text-link" href="{{ route('products.index') }}">Ver todas as categorias →</a>
        </div>
        <div class="category-grid">
            @forelse ($categories as $category)
                <a class="category-tile" href="{{ route('products.index', ['category' => $category->id]) }}">
                    <span class="category-symbol">✚</span>
                    <strong>{{ $category->name }}</strong>
                    <span>Consultar produtos</span>
                </a>
            @empty
                <div class="empty" style="grid-column:1/-1">Nenhuma categoria cadastrada no momento.</div>
            @endforelse
        </div>
    </section>

    <!-- Banner Informativo: Medicamentos com Receita Médica -->
    <section style="background:linear-gradient(135deg, #fbf7f8 0%, #f4e9ed 100%);border:1px solid #eadfe2;border-radius:18px;padding:30px 36px;margin-bottom:56px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px">
        <div style="max-width:650px">
            <span class="status" style="background:#5c0e2c;color:white;font-weight:bold;margin-bottom:8px">Norma Sanitária de Angola</span>
            <h3 style="font-size:22px;margin:8px 0;color:var(--wine-dark)">Precisa de medicamentos sujeitos a receita médica?</h3>
            <p style="margin:0;color:var(--ink);font-size:14px;line-height:1.6">
                Para antibióticos, antimaláricos e medicamentos controlados, basta anexar a fotografia ou ficheiro PDF da sua receita médica durante a finalização do pedido. Nossa equipa farmacêutica validará o documento com rigor e rapidez.
            </p>
        </div>
        <a href="{{ route('products.index', ['requires_prescription' => 1]) }}" class="button" style="white-space:nowrap">
            Ver Medicamentos com Receita
        </a>
    </section>

    <!-- Produtos em Promoção (se houver) -->
    @if ($promotionalProducts->isNotEmpty())
        <section class="section">
            <div class="section-head">
                <div>
                    <span class="eyebrow" style="color:var(--wine)">Oportunidades</span>
                    <h2>Promoções em Destaque</h2>
                    <p>Preços especiais com descontos em Kwanzas (Kz).</p>
                </div>
                <a class="text-link" href="{{ route('products.index') }}">Ver todo o catálogo →</a>
            </div>
            <div class="product-grid">
                @foreach ($promotionalProducts as $product)
                    @include('components.store-product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    <!-- Produtos Mais Recentes / Destaque -->
    <section class="section">
        <div class="section-head">
            <div>
                <span class="eyebrow" style="color:var(--wine)">Mais Procurados</span>
                <h2>Produtos Disponíveis</h2>
                <p>Confira os itens com estoque atualizado nas farmácias Gundja.</p>
            </div>
            <a class="text-link" href="{{ route('products.index') }}">Ver catálogo completo →</a>
        </div>
        @if ($products->isNotEmpty())
            <div class="product-grid">
                @foreach ($products as $product)
                    @include('components.store-product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div class="empty">Nenhum produto disponível no momento.</div>
        @endif
    </section>

    <!-- Nossos Serviços Farmacêuticos -->
    <section class="section" style="background:#fff;border:1px solid var(--line);border-radius:20px;padding:36px">
        <div class="section-head">
            <div>
                <span class="eyebrow" style="color:var(--wine)">Cuidados Perto de Si</span>
                <h2>Serviços Farmacêuticos Especializados</h2>
                <p>Profissionais qualificados disponíveis para o orientar.</p>
            </div>
            <a class="text-link" href="{{ route('pages.services') }}">Conhecer todos os serviços →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px">
            <div style="padding:16px;border:1px solid #f4ecef;border-radius:12px">
                <div style="font-size:24px;margin-bottom:8px">🩺</div>
                <h4 style="margin:0 0 6px;font-size:16px">Aferição de Pressão Arterial</h4>
                <p class="muted" style="font-size:13px;margin:0">Monitoramento preventivo e acompanhamento por profissionais habilitados.</p>
            </div>
            <div style="padding:16px;border:1px solid #f4ecef;border-radius:12px">
                <div style="font-size:24px;margin-bottom:8px">🩸</div>
                <h4 style="margin:0 0 6px;font-size:16px">Teste Rápido de Glicemia</h4>
                <p class="muted" style="font-size:13px;margin:0">Controle do nível de açúcar no sangue com resultado imediato e seguro.</p>
            </div>
            <div style="padding:16px;border:1px solid #f4ecef;border-radius:12px">
                <div style="font-size:24px;margin-bottom:8px">🔬</div>
                <h4 style="margin:0 0 6px;font-size:16px">Triagem Rápida de Malária</h4>
                <p class="muted" style="font-size:13px;margin:0">Testes rápidos de diagnóstico para início ágil do tratamento prescrito.</p>
            </div>
            <div style="padding:16px;border:1px solid #f4ecef;border-radius:12px">
                <div style="font-size:24px;margin-bottom:8px">💬</div>
                <h4 style="margin:0 0 6px;font-size:16px">Apoio e Atenção Farmacêutica</h4>
                <p class="muted" style="font-size:13px;margin:0">Esclarecimento de posologias, interações medicamentosas e uso correto.</p>
            </div>
        </div>
    </section>

    <!-- Nossas Farmácias / Unidades -->
    @if ($branches->isNotEmpty())
        <section class="section" style="margin-top:56px">
            <div class="section-head">
                <div>
                    <span class="eyebrow" style="color:var(--wine)">Presença Local</span>
                    <h2>Nossas Farmácias em Luanda</h2>
                    <p>Visite as nossas lojas físicas ou escolha a sua unidade para retirada de pedidos.</p>
                </div>
                <a class="text-link" href="{{ route('pages.branches') }}">Ver localizações detalhadas →</a>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px">
                @foreach ($branches as $branch)
                    <div style="background:white;border:1px solid var(--line);border-radius:16px;padding:22px">
                        <span class="status" style="margin-bottom:8px">{{ $branch->municipality }} · {{ $branch->province }}</span>
                        <h3 style="font-size:18px;margin:6px 0 8px">{{ $branch->name }}</h3>
                        <p class="muted" style="font-size:13px;margin:0 0 12px">{{ $branch->address }}</p>
                        <div style="font-size:13px;line-height:1.7;border-top:1px solid #f4ecef;padding-top:10px">
                            <div>📞 <strong>{{ $branch->phone }}</strong></div>
                            @if ($branch->opening_hours)
                                <div>🕒 {{ $branch->opening_hours }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
