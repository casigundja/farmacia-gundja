# Farmácia Gundja

Sistema web de gestão e e-commerce de farmácia, desenvolvido em Laravel, PHP e MySQL. A interface usa Blade e está em português.

## Executar localmente

Requisitos: Docker Desktop e Docker Compose.

```powershell
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

A loja fica em <http://localhost:8000>. O MySQL local fica disponível na porta `3307`.

O seeder cria categorias, marcas, seis produtos de demonstração com estoque, e o administrador configurado pelas variáveis `ADMIN_EMAIL` e `ADMIN_PASSWORD` do `.env`. O endereço padrão do administrador é `admin@farmacia.local`. Configure `ADMIN_PASSWORD` localmente antes de semear; a senha não é armazenada no código-fonte. `AdminSeeder` exige pelo menos oito caracteres.

Para abrir novamente a sessão de seed após uma instalação limpa:

```powershell
docker compose exec app php artisan db:seed --force
```

## Funcionalidades do MVP

- Catálogo, busca, filtros, detalhes do produto e imagens.
- Cadastro, login, perfil e endereços de clientes.
- Carrinho e checkout com verificação e reserva transacional de estoque.
- Pedidos, acompanhamento do status, confirmação, separação, entrega e cancelamento com devolução ao estoque.
- Cadastro e gestão de produtos, categorias e marcas.
- Lotes, validade, entradas e ajustes de estoque com histórico de movimentações.
- Vendas presenciais, baixa de estoque, cancelamento, devolução de estoque e registro de pagamento cancelado.
- Equipe com permissões por módulo, relatórios, dashboard e trilha de auditoria.

## Testes e verificações

```powershell
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose exec app php artisan view:cache
```

Os testes usam banco isolado e cobrem permissões, clientes, imagens, pedidos, vendas e estoque. A suíte atual passa com 27 testes e 112 verificações.

## Limites antes de produção

- PIX e cartões ainda não estão ligados a um gateway. O checkout registra o método e deixa o pagamento pendente; a confirmação administrativa muda o pagamento para pago. Não são coletados nem armazenados dados de cartão.
- No cancelamento de venda presencial, o sistema devolve o estoque e marca o pagamento como cancelado; o reembolso financeiro deve ser processado fora do sistema.
- Emissão fiscal, integrações de delivery e notificações externas não estão configuradas.
- Produtos sujeitos a receita ou controle especial precisam de requisitos operacionais e regulatórios definidos antes de habilitar sua venda real.
- Antes do deploy, configure domínio e HTTPS, segredos próprios de produção, banco gerenciado, backups e monitoramento. O `docker-compose.yml` fornecido é para desenvolvimento local.

Não use os dados ou credenciais locais de demonstração em produção.
