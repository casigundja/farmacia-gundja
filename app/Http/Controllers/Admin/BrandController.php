<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()->withCount('products')->orderBy('name')->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.form', ['brand' => new Brand(['active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:brands,name'], 'active' => ['sometimes', 'boolean']]);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['active'] = $request->boolean('active');
        Brand::query()->create($data);

        return to_route('admin.brands.index')->with('success', 'Marca cadastrada.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.form', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:brands,name,'.$brand->id], 'active' => ['sometimes', 'boolean']]);
        if ($brand->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $brand->id);
        }
        $data['active'] = $request->boolean('active');
        $brand->update($data);

        return to_route('admin.brands.index')->with('success', 'Marca atualizada.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->update(['active' => false]);

        return to_route('admin.brands.index')->with('success', 'Marca desativada.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;
        while (Brand::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
