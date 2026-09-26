<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CashRegisterController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PrescriptionController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController as AdminSaleController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\AddressController as CustomerAddressController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\PageController;
use App\Http\Controllers\Store\ProductController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('/produto/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/sobre-nos', [PageController::class, 'about'])->name('pages.about');
Route::get('/servicos', [PageController::class, 'services'])->name('pages.services');
Route::get('/farmacias', [PageController::class, 'branches'])->name('pages.branches');
Route::get('/contactos', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/perguntas-frequentes', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/privacidade', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/termos', [PageController::class, 'terms'])->name('pages.terms');

Route::get('/login', [AuthController::class, 'createLogin'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1'])->name('login.store');
Route::get('/cadastro', [AuthController::class, 'createRegistration'])->middleware('guest')->name('register');
Route::post('/cadastro', [AuthController::class, 'register'])->middleware(['guest', 'throttle:5,1'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'customer'])->group(function (): void {
    Route::get('/cliente/perfil', [CustomerProfileController::class, 'edit'])->name('customer.profile');
    Route::put('/cliente/perfil', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
    Route::post('/cliente/enderecos', [CustomerAddressController::class, 'store'])->name('customer.addresses.store');
    Route::put('/cliente/enderecos/{address}', [CustomerAddressController::class, 'update'])->name('customer.addresses.update');
    Route::delete('/cliente/enderecos/{address}', [CustomerAddressController::class, 'destroy'])->name('customer.addresses.destroy');
    Route::patch('/cliente/enderecos/{address}/padrao', [CustomerAddressController::class, 'makeDefault'])->name('customer.addresses.default');
    Route::get('/carrinho', [CartController::class, 'index'])->name('cart');
    Route::post('/carrinho/adicionar/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/carrinho/item/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/carrinho/item/{item}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/finalizar', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/cliente/pedidos', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/cliente/pedido/{order}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');
});

Route::middleware(['auth', 'employee'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', function (Request $request): RedirectResponse {
        $destinations = [
            'dashboard.view' => 'admin.dashboard',
            'prescriptions.view' => 'admin.prescriptions.index',
            'cash.view' => 'admin.cash.index',
            'branches.manage' => 'admin.branches.index',
            'suppliers.manage' => 'admin.suppliers.index',
            'products.view' => 'admin.products.index',
            'categories.view' => 'admin.categories.index',
            'brands.view' => 'admin.brands.index',
            'stock.view' => 'admin.stock.index',
            'orders.view' => 'admin.orders.index',
            'sales.view' => 'admin.sales.index',
            'customers.view' => 'admin.customers.index',
            'reports.view' => 'admin.reports.index',
            'audit.view' => 'admin.audit.index',
        ];

        foreach ($destinations as $permission => $route) {
            if ($request->user()->hasPermission($permission)) {
                return to_route($route);
            }
        }

        abort(403);
    })->name('entry');
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/relatorios', [ReportController::class, 'index'])->middleware('permission:reports.view')->name('reports.index');
    Route::get('/auditoria', [AuditLogController::class, 'index'])->middleware('permission:audit.view')->name('audit.index');
    Route::get('/clientes', [AdminCustomerController::class, 'index'])->middleware('permission:customers.view')->name('customers.index');
    Route::get('/clientes/{customer}', [AdminCustomerController::class, 'show'])->middleware('permission:customers.view')->name('customers.show');

    // Validação de Receitas Médicas (Farmacêutico)
    Route::get('/receitas', [PrescriptionController::class, 'index'])->middleware('permission:prescriptions.view')->name('prescriptions.index');
    Route::get('/receitas/{prescription}', [PrescriptionController::class, 'show'])->middleware('permission:prescriptions.view')->name('prescriptions.show');
    Route::post('/receitas/{prescription}/avaliar', [PrescriptionController::class, 'review'])->middleware('permission:prescriptions.manage')->name('prescriptions.review');

    // Módulo de Caixa
    Route::get('/caixa', [CashRegisterController::class, 'index'])->middleware('permission:cash.view')->name('cash.index');
    Route::post('/caixa/abrir', [CashRegisterController::class, 'store'])->middleware('permission:cash.manage')->name('cash.open');
    Route::post('/caixa/{cashRegister}/movimento', [CashRegisterController::class, 'movement'])->middleware('permission:cash.manage')->name('cash.movement');
    Route::post('/caixa/{cashRegister}/fechar', [CashRegisterController::class, 'close'])->middleware('permission:cash.manage')->name('cash.close');

    // Filiais / Multiunidades
    Route::get('/filiais', [BranchController::class, 'index'])->middleware('permission:branches.manage')->name('branches.index');
    Route::post('/filiais', [BranchController::class, 'store'])->middleware('permission:branches.manage')->name('branches.store');
    Route::put('/filiais/{branch}', [BranchController::class, 'update'])->middleware('permission:branches.manage')->name('branches.update');

    // Fornecedores
    Route::get('/fornecedores', [SupplierController::class, 'index'])->middleware('permission:suppliers.manage')->name('suppliers.index');
    Route::post('/fornecedores', [SupplierController::class, 'store'])->middleware('permission:suppliers.manage')->name('suppliers.store');
    Route::put('/fornecedores/{supplier}', [SupplierController::class, 'update'])->middleware('permission:suppliers.manage')->name('suppliers.update');

    Route::get('/categorias', [CategoryController::class, 'index'])->middleware('permission:categories.view')->name('categories.index');
    Route::middleware('permission:categories.manage')->group(function (): void {
        Route::resource('categorias', CategoryController::class)->except(['show', 'index'])->parameters(['categorias' => 'category'])->names('categories');
    });
    Route::get('/marcas', [BrandController::class, 'index'])->middleware('permission:brands.view')->name('brands.index');
    Route::middleware('permission:brands.manage')->group(function (): void {
        Route::resource('marcas', BrandController::class)->except(['show', 'index'])->parameters(['marcas' => 'brand'])->names('brands');
    });

    Route::get('/produtos', [AdminProductController::class, 'index'])->middleware('permission:products.view')->name('products.index');
    Route::middleware('permission:products.manage')->group(function (): void {
        Route::resource('produtos', AdminProductController::class)->except(['show', 'index'])->parameters(['produtos' => 'product'])->names('products');
        Route::post('/produtos/{product}/imagens', [AdminProductController::class, 'storeImages'])->name('products.images.store');
        Route::patch('/produtos/{product}/imagens/{image}/principal', [AdminProductController::class, 'setPrimaryImage'])->name('products.images.primary');
        Route::delete('/produtos/{product}/imagens/{image}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');
    });

    Route::get('/estoque', [StockController::class, 'index'])->middleware('permission:stock.view')->name('stock.index');
    Route::post('/estoque/entrada', [StockController::class, 'entry'])->middleware('permission:stock.manage')->name('stock.entry');
    Route::post('/estoque/ajuste', [StockController::class, 'adjust'])->middleware('permission:stock.manage')->name('stock.adjust');
    Route::get('/pedidos', [AdminOrderController::class, 'index'])->middleware('permission:orders.view')->name('orders.index');
    Route::patch('/pedidos/{order}', [AdminOrderController::class, 'update'])->middleware('permission:orders.manage')->name('orders.update');
    Route::get('/vendas', [AdminSaleController::class, 'index'])->middleware('permission:sales.view')->name('sales.index');
    Route::get('/vendas/nova', [AdminSaleController::class, 'create'])->middleware('permission:sales.manage')->name('sales.create');
    Route::post('/vendas', [AdminSaleController::class, 'store'])->middleware('permission:sales.manage')->name('sales.store');
    Route::post('/vendas/{sale}/cancelar', [AdminSaleController::class, 'cancel'])->middleware('permission:sales.manage')->name('sales.cancel');

    Route::middleware('admin')->group(function (): void {
        Route::resource('funcionarios', EmployeeController::class)->except('show')->parameters(['funcionarios' => 'employee'])->names('employees');
    });
});
