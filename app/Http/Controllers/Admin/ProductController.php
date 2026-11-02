<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->q);

        $products = Product::query()
            ->when($q, function ($query) use ($q) {
                $query->where('product_name', 'like', "%{$q}%")
                      ->orWhere('brand', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->get();

        return view('admin.products.index', compact('products', 'q'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name'  => 'required|string|max:150',
            'brand'         => 'nullable|string|max:50',
            'thumbnail_url' => 'nullable|string|max:255',
        ]);

        Product::create($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công.');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name'  => 'required|string|max:150',
            'brand'         => 'nullable|string|max:50',
            'thumbnail_url' => 'nullable|string|max:255',
        ]);

        $product->update($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Xóa sản phẩm thành công.');
    }
}