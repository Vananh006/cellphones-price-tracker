<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductLinkController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->q);
        $storeId = $request->store_id;

        $links = DB::table('store_prices as sp')
            ->leftJoin('product_variants as pv', 'sp.variant_id', '=', 'pv.id')
            ->leftJoin('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('stores as s', 'sp.store_id', '=', 's.id')
            ->select(
                'sp.id',
                'sp.variant_id',
                'sp.store_id',
                'sp.current_price',
                'sp.original_price',
                'sp.product_url',
                'sp.affiliate_url',
                'sp.in_stock',
                'sp.updated_at',
                'p.product_name',
                'p.brand',
                'pv.storage',
                'pv.color',
                'pv.sku',
                's.store_name'
            )
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('p.product_name', 'like', "%{$q}%")
                        ->orWhere('p.brand', 'like', "%{$q}%")
                        ->orWhere('pv.storage', 'like', "%{$q}%")
                        ->orWhere('pv.color', 'like', "%{$q}%")
                        ->orWhere('s.store_name', 'like', "%{$q}%");
                });
            })
            ->when($storeId, function ($query) use ($storeId) {
                $query->where('sp.store_id', $storeId);
            })
            ->orderByDesc('sp.id')
            ->get();

        $stores = DB::table('stores')->orderBy('store_name')->get();

        return view('admin.product-links.index', compact('links', 'stores', 'q', 'storeId'));
    }

    public function create()
    {
        $variants = DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->select(
                'pv.id',
                'p.product_name',
                'p.brand',
                'pv.storage',
                'pv.color',
                'pv.sku'
            )
            ->orderBy('p.product_name')
            ->get();

        $stores = DB::table('stores')->orderBy('store_name')->get();

        return view('admin.product-links.create', compact('variants', 'stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'variant_id'     => 'required|integer|exists:product_variants,id',
            'store_id'       => 'required|integer|exists:stores,id',
            'current_price'  => 'nullable|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'product_url'    => 'required|string|max:255',
            'affiliate_url'  => 'nullable|string|max:255',
            'in_stock'       => 'required|boolean',
        ]);

        DB::table('store_prices')->insert([
            'variant_id'     => $validated['variant_id'],
            'store_id'       => $validated['store_id'],
            'current_price'  => $validated['current_price'],
            'original_price' => $validated['original_price'],
            'product_url'    => $validated['product_url'],
            'affiliate_url'  => $validated['affiliate_url'],
            'in_stock'       => $validated['in_stock'],
            'updated_at'     => now(),
        ]);

        return redirect()
            ->route('admin.product-links.index')
            ->with('success', 'Thêm link sản phẩm thành công.');
    }

    public function show(string $id)
    {
        $link = DB::table('store_prices as sp')
            ->leftJoin('product_variants as pv', 'sp.variant_id', '=', 'pv.id')
            ->leftJoin('products as p', 'pv.product_id', '=', 'p.id')
            ->leftJoin('stores as s', 'sp.store_id', '=', 's.id')
            ->select(
                'sp.id as link_id',
                'sp.variant_id',
                'sp.store_id',
                'sp.current_price',
                'sp.original_price',
                'sp.product_url',
                'sp.affiliate_url',
                'sp.in_stock',
                'sp.updated_at',

                'pv.id as variant_id_real',
                'pv.product_id',
                'pv.storage',
                'pv.color',
                'pv.sku',

                'p.id as product_id_real',
                'p.product_name',
                'p.brand',
                'p.thumbnail_url',
                'p.created_at as product_created_at',
                'p.updated_at as product_updated_at',

                's.store_name',
                's.base_url'
            )
            ->where('sp.id', $id)
            ->first();

        abort_if(!$link, 404);

        return view('admin.product-links.show', compact('link'));
    }
    public function edit(string $id)
    {
        $link = DB::table('store_prices')->where('id', $id)->first();
        abort_if(!$link, 404);

        $variants = DB::table('product_variants as pv')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->select(
                'pv.id',
                'p.product_name',
                'p.brand',
                'pv.storage',
                'pv.color',
                'pv.sku'
            )
            ->orderBy('p.product_name')
            ->get();

        $stores = DB::table('stores')->orderBy('store_name')->get();

        return view('admin.product-links.edit', compact('link', 'variants', 'stores'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'variant_id'     => 'required|integer|exists:product_variants,id',
            'store_id'       => 'required|integer|exists:stores,id',
            'current_price'  => 'nullable|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'product_url'    => 'required|string|max:255',
            'affiliate_url'  => 'nullable|string|max:255',
            'in_stock'       => 'required|boolean',
        ]);

        DB::table('store_prices')
            ->where('id', $id)
            ->update([
                'variant_id'     => $validated['variant_id'],
                'store_id'       => $validated['store_id'],
                'current_price'  => $validated['current_price'],
                'original_price' => $validated['original_price'],
                'product_url'    => $validated['product_url'],
                'affiliate_url'  => $validated['affiliate_url'],
                'in_stock'       => $validated['in_stock'],
                'updated_at'     => now(),
            ]);

        return redirect()
            ->route('admin.product-links.index')
            ->with('success', 'Cập nhật link sản phẩm thành công.');
    }

    public function destroy(string $id)
    {
        DB::table('store_prices')->where('id', $id)->delete();

        return redirect()
            ->route('admin.product-links.index')
            ->with('success', 'Xóa link sản phẩm thành công.');
    }
}