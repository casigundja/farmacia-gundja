<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['user_id', 'cpf', 'phone', 'position', 'active', 'permissions'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'permissions' => 'array'];
    }

    /** @return array<string, string> */
    public static function availablePermissions(): array
    {
        return [
            'dashboard.view' => 'Visualizar painel',
            'products.view' => 'Consultar produtos',
            'products.manage' => 'Gerenciar produtos e imagens',
            'categories.view' => 'Consultar categorias',
            'categories.manage' => 'Gerenciar categorias',
            'brands.view' => 'Consultar marcas',
            'brands.manage' => 'Gerenciar marcas',
            'stock.view' => 'Consultar estoque',
            'stock.manage' => 'Movimentar estoque',
            'orders.view' => 'Consultar pedidos',
            'orders.manage' => 'Atualizar pedidos',
            'sales.view' => 'Consultar vendas',
            'sales.manage' => 'Registrar vendas presenciais',
            'customers.view' => 'Consultar clientes',
            'reports.view' => 'Visualizar relatórios',
            'audit.view' => 'Consultar auditoria',
        ];
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->active) {
            return false;
        }

        $permissions = $this->permissions ?? [];
        if (in_array($permission, $permissions, true)) {
            return true;
        }

        [$module, $action] = explode('.', $permission, 2);

        return $action === 'view' && in_array($module.'.manage', $permissions, true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
