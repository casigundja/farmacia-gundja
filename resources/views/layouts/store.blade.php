<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Farmácia Gundja') · Farmácia Gundja Angola</title>
    <style>
        :root{
            --wine:#5c0e2c;
            --wine-dark:#3b0a1e;
            --rose:#f7e9ee;
            --peach:#eea876;
            --ink:#241b1e;
            --muted:#786d70;
            --line:#eadfe2;
            --paper:#fffdfb;
            --green:#2f7253;
        }
        *{box-sizing:border-box}
        body{margin:0;background:var(--paper);color:var(--ink);font:15px/1.5 Arial,Helvetica,sans-serif}
        a{color:inherit;text-decoration:none}
        button,input,select,textarea{font:inherit}
        .wrap{width:min(1160px,calc(100% - 40px));margin:auto}
        .topline{background:var(--wine-dark);color:#fff;padding:8px 0;text-align:center;font-size:12px;letter-spacing:.04em}
        .header{background:#fff;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:90}
        .nav{height:82px;display:flex;align-items:center;gap:24px}
        .logo{display:flex;align-items:center;gap:12px;color:var(--wine);font-weight:800;font-size:19px;letter-spacing:-.04em}
        .logo img{height:46px;width:auto;object-fit:contain}
        .navlinks{display:flex;align-items:center;gap:20px;margin-left:auto;color:#584d50;font-size:14px;font-weight:500}
        .navlinks a:hover{color:var(--wine)}
        .nav-action{border:1px solid var(--line);border-radius:999px;padding:9px 16px;color:var(--wine);font-weight:700;display:inline-flex;align-items:center;gap:6px}
        .nav-action:hover{border-color:var(--wine);background:var(--rose)}
        .button{display:inline-flex;justify-content:center;align-items:center;border:0;border-radius:9px;padding:11px 18px;background:var(--wine);color:white;font-weight:700;cursor:pointer;text-align:center}
        .button:hover{background:var(--wine-dark)}
        .button-light{background:white;color:var(--wine)}
        .eyebrow{text-transform:uppercase;letter-spacing:.15em;font-size:11px;font-weight:700;color:var(--peach)}
        .section{margin:0 auto 56px}
        .section-head{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:20px}
        .section-head h2{margin:0;font-size:27px;letter-spacing:-.04em}
        .section-head p{margin:4px 0 0;color:var(--muted);font-size:14px}
        .text-link{color:var(--wine);font-size:13px;font-weight:700}
        .category-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
        .category-tile{padding:19px;border:1px solid var(--line);border-radius:14px;background:#fff;transition:.2s}
        .category-tile:hover{border-color:#d4a9b8;transform:translateY(-2px)}
        .category-symbol{width:38px;height:38px;border-radius:12px;background:var(--rose);display:grid;place-items:center;color:var(--wine);font-size:18px;margin-bottom:13px}
        .category-tile strong{display:block}
        .category-tile span{font-size:12px;color:var(--muted)}
        .product-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
        .product-card{background:white;border:1px solid var(--line);border-radius:15px;overflow:hidden;transition:transform .2s,border-color .2s;display:flex;flex-direction:column}
        .product-card:hover{transform:translateY(-3px);border-color:#d4a9b8}
        .product-visual{height:180px;background:linear-gradient(140deg,#f7e9ee,#fff4e8);display:grid;place-items:center;color:var(--wine);font-size:48px;overflow:hidden}
        .product-visual img{width:100%;height:100%;object-fit:cover}
        .product-body{padding:15px;display:flex;flex-direction:column;flex:1}
        .product-brand{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}
        .product-body h3{font-size:15px;line-height:1.35;min-height:40px;margin:5px 0 8px}
        .price{font-size:20px;letter-spacing:-.04em;font-weight:800;color:var(--wine)}
        .availability{font-size:12px;color:var(--green);margin-top:4px}
        .availability.out{color:#a14b3f}
        .product-grid .button{margin-top:auto;width:100%;padding:9px;font-size:13px}
        .filters{display:grid;grid-template-columns:2fr repeat(4,1fr) auto;gap:10px;padding:16px;background:white;border:1px solid var(--line);border-radius:14px;margin-bottom:24px}
        .filters input,.filters select{width:100%;border:1px solid var(--line);border-radius:8px;padding:10px;background:white}
        .filters label{font-size:11px;color:var(--muted);display:block;margin-bottom:4px}
        .breadcrumbs{font-size:12px;color:var(--muted);margin:24px 0}
        .product-detail{display:grid;grid-template-columns:1fr 1fr;gap:50px;margin:25px 0 70px}
        .detail-visual{min-height:420px;border-radius:22px;background:linear-gradient(140deg,#f7e9ee,#fff4e8);display:grid;place-items:center;color:var(--wine);font-size:100px;overflow:hidden}
        .detail-visual img{width:100%;height:100%;max-height:560px;object-fit:cover}
        .detail-copy h1{font-size:42px}
        .detail-copy .price{font-size:30px;margin:18px 0}
        .notice{padding:13px 15px;background:#fff5eb;border:1px solid #f3ddc6;border-radius:10px;color:#774820;font-size:13px}
        .empty{padding:40px;text-align:center;color:var(--muted);border:1px dashed var(--line);border-radius:14px}
        .footer{margin-top:65px;background:var(--wine-dark);color:#fff;padding:50px 0 25px}
        .footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1.5fr;gap:36px;margin-bottom:36px}
        .footer h3{font-size:16px;margin:0 0 14px;color:#fff}
        .footer p{color:#e3cdd5;font-size:13px;line-height:1.6;margin:0 0 10px}
        .footer ul{list-style:none;padding:0;margin:0;font-size:13px;line-height:2}
        .footer ul a{color:#e3cdd5}
        .footer ul a:hover{color:#fff;text-decoration:underline}
        .footer-bottom{border-top:1px solid rgba(255,255,255,0.12);padding-top:20px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#d8bdc6;flex-wrap:wrap;gap:12px}
        @media(max-width:850px){
            .product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
            .filters{grid-template-columns:repeat(2,1fr)}
            .filters>div:first-child{grid-column:span 2}
            .category-grid{grid-template-columns:repeat(2,1fr)}
            .footer-grid{grid-template-columns:1fr 1fr;gap:24px}
            .navlinks{gap:12px}
            .nav{gap:14px}
        }
        @media(max-width:600px){
            .nav{height:auto;min-height:74px;flex-wrap:wrap;padding:12px 0}
            .navlinks{order:3;width:100%;overflow-x:auto;padding-bottom:5px;font-size:13px}
            .footer-grid{grid-template-columns:1fr;gap:24px}
            .footer-bottom{flex-direction:column;text-align:center}
        }
    </style>
</head>
<body>
    <div class="topline">
        <div class="wrap" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
            <span>✨ <strong>Farmácia Gundja</strong> · Saúde, confiança e cuidado perto de si</span>
            <span>📍 Entregas ao domicílio em Luanda · WhatsApp: <strong>+244 923 000 000</strong> · Moeda: <strong>Kz (AOA)</strong></span>
        </div>
    </div>

    <header class="header">
        <div class="wrap nav">
            <a class="logo" href="{{ route('home') }}">
                @if (file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Farmácia Gundja">
                @else
                    <span style="height:38px;width:38px;display:grid;place-items:center;border-radius:50%;background:var(--wine);color:white;font-size:23px">+</span>
                @endif
                <span style="line-height:1.15;display:flex;flex-direction:column">
                    <span>Farmácia Gundja</span>
                    <span style="font-size:11px;font-weight:400;color:var(--muted);letter-spacing:0">Angola</span>
                </span>
            </a>

            <nav class="navlinks" aria-label="Navegação principal">
                <a href="{{ route('products.index') }}">Medicamentos & Produtos</a>
                <a href="{{ route('pages.services') }}">Serviços</a>
                <a href="{{ route('pages.branches') }}">Nossas Farmácias</a>
                <a href="{{ route('pages.about') }}">Sobre Nós</a>
                <a href="{{ route('pages.contact') }}">Contactos</a>
                @auth
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('customer.profile') }}">Meu Perfil</a>
                        <a href="{{ route('customer.orders.index') }}">Meus Pedidos</a>
                    @endif
                    @if (auth()->user()->isAdmin() || auth()->user()->isEmployee())
                        @if (auth()->user()->hasAnyAdminPermission(array_keys(\App\Models\Employee::availablePermissions())))
                            <a href="{{ route('admin.entry') }}" style="color:var(--wine);font-weight:700">Painel de Gestão ↗</a>
                        @endif
                    @endif
                @endauth
            </nav>

            <div style="display:flex;align-items:center;gap:10px">
                @auth
                    @if (auth()->user()->isCustomer())
                        <a class="nav-action" href="{{ route('cart') }}">
                            🛒 Sacola
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="margin:0">
                        @csrf
                        <button class="nav-action" type="submit" style="cursor:pointer;background:transparent">Sair</button>
                    </form>
                @else
                    <a class="nav-action" href="{{ route('cart') }}">🛒 Sacola</a>
                    <a class="nav-action" href="{{ route('login') }}" style="background:var(--wine);color:#fff;border-color:var(--wine)">Entrar</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="wrap" style="margin-top:18px">
                <div class="notice" style="background:#eef6f0;border-color:#d3e7d9;color:#2f7253">
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="wrap" style="margin-top:18px">
                <div class="notice" style="background:#fff0ed;border-color:#f0cfc8;color:#a23d2c">
                    {{ session('error') }}
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="footer">
        <div class="wrap">
            <div class="footer-grid">
                <div>
                    <h3 style="display:flex;align-items:center;gap:10px">
                        @if (file_exists(public_path('images/symbol.png')))
                            <img src="{{ asset('images/symbol.png') }}" alt="Gundja" style="height:32px;filter:brightness(0) invert(1)">
                        @endif
                        Farmácia Gundja
                    </h3>
                    <p style="font-style:italic;color:var(--peach)">"Saúde, confiança e cuidado perto de si."</p>
                    <p>
                        A sua farmácia de referência em Angola. Oferecemos medicamentos originais, atendimento farmacêutico humanizado, dermocosméticos e produtos de saúde infantil com entrega rápida em Luanda.
                    </p>
                    <div style="margin-top:14px;color:#e3cdd5;font-size:13px">
                        <div>📞 +244 923 000 000 / +244 931 000 000</div>
                        <div>✉️ atendimento@farmaciagundja.ao</div>
                    </div>
                </div>

                <div>
                    <h3>Institucional</h3>
                    <ul>
                        <li><a href="{{ route('pages.about') }}">Sobre Nós</a></li>
                        <li><a href="{{ route('pages.services') }}">Nossos Serviços</a></li>
                        <li><a href="{{ route('pages.branches') }}">Nossas Farmácias</a></li>
                        <li><a href="{{ route('pages.contact') }}">Fale Connosco</a></li>
                        <li><a href="{{ route('pages.faq') }}">Perguntas Frequentes</a></li>
                    </ul>
                </div>

                <div>
                    <h3>Ajuda & Legal</h3>
                    <ul>
                        <li><a href="{{ route('pages.terms') }}">Termos de Utilização</a></li>
                        <li><a href="{{ route('pages.privacy') }}">Política de Privacidade</a></li>
                        <li><a href="{{ route('products.index') }}">Catálogo Completo</a></li>
                        <li><a href="{{ route('cart') }}">Carrinho de Compras</a></li>
                        <li><a href="{{ route('login') }}">Área do Cliente</a></li>
                    </ul>
                </div>

                <div>
                    <h3>Pagamento & Entrega</h3>
                    <p>Aceitamos as principais formas de pagamento em Angola:</p>
                    <ul style="line-height:1.7">
                        <li>💳 <strong>Multicaixa Express (MCX)</strong></li>
                        <li>🏦 <strong>Transferência Bancária</strong> (BAI, BFA, BIC)</li>
                        <li>🏧 <strong>TPA / Cartão Débito</strong> (na entrega ou balcão)</li>
                        <li>💵 <strong>Numerário na Entrega</strong> (Kz)</li>
                    </ul>
                    <div style="margin-top:12px;padding:8px 12px;background:rgba(255,255,255,0.08);border-radius:8px;font-size:12px">
                        🚚 Entregas em Luanda: Talatona, Centro, Viana, Belas, Maianga, Kilamba e arredores.
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    © 2026 <strong>Farmácia Gundja</strong> · Todos os direitos reservados · República de Angola.
                </div>
                <div>
                    Preços e disponibilidade expressos em Kwanzas (Kz / AOA).
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
