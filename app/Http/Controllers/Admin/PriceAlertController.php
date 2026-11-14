<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceAlertController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->q);
        $status = trim((string) $request->status);

        $alerts = DB::table('price_alerts as pa')
            ->leftJoin('users as u', 'pa.user_id', '=', 'u.id')
            ->leftJoin('product_variants as pv', 'pa.variant_id', '=', 'pv.id')
            ->leftJoin('products as p', 'pv.product_id', '=', 'p.id')
            ->select(
                'pa.id',
                'pa.user_id',
                'pa.variant_id',
                'pa.submitted_url',
                'pa.target_price',
                'pa.alert_status',
                'pa.created_at',
                'pa.updated_at',
                'u.user_name',
                'u.email',
                'p.product_name',
                'p.brand',
                'pv.storage',
                'pv.color',
                'pv.sku'
            )
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('u.user_name', 'like', "%{$q}%")
                        ->orWhere('u.email', 'like', "%{$q}%")
                        ->orWhere('p.product_name', 'like', "%{$q}%")
                        ->orWhere('p.brand', 'like', "%{$q}%")
                        ->orWhere('pv.storage', 'like', "%{$q}%")
                        ->orWhere('pv.color', 'like', "%{$q}%")
                        ->orWhere('pa.alert_status', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('pa.alert_status', $status);
            })
            ->orderByDesc('pa.id')
            ->get();

        $users = DB::table('users')->orderBy('email')->get();

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

        $statuses = DB::table('price_alerts')
            ->select('alert_status')
            ->distinct()
            ->whereNotNull('alert_status')
            ->pluck('alert_status');

        return view('admin.price-alerts.index', compact(
            'alerts',
            'q',
            'status',
            'users',
            'variants',
            'statuses'
        ));
    }

    public function create()
    {
        $users = DB::table('users')->orderBy('email')->get();

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

        return view('admin.price-alerts.create', compact('users', 'variants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'variant_id'    => 'required|integer|exists:product_variants,id',
            'submitted_url' => 'nullable|string|max:255',
            'target_price'  => 'required|numeric|min:0',
            'alert_status'  => 'required|in:pending,triggered,canceled',
        ]);

        DB::table('price_alerts')->insert([
            'user_id'       => $validated['user_id'],
            'variant_id'    => $validated['variant_id'],
            'submitted_url' => $validated['submitted_url'],
            'target_price'  => $validated['target_price'],
            'alert_status'  => $validated['alert_status'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()
            ->route('admin.price-alerts.index')
            ->with('success', 'Thêm giá mong muốn thành công.');
    }

    public function show(string $id)
    {
        $alert = DB::table('price_alerts as pa')
            ->leftJoin('users as u', 'pa.user_id', '=', 'u.id')
            ->leftJoin('product_variants as pv', 'pa.variant_id', '=', 'pv.id')
            ->leftJoin('products as p', 'pv.product_id', '=', 'p.id')
            ->select(
                'pa.*',
                'u.user_name',
                'u.email',
                'p.product_name',
                'p.brand',
                'p.thumbnail_url',
                'pv.storage',
                'pv.color',
                'pv.sku'
            )
            ->where('pa.id', $id)
            ->first();

        abort_if(!$alert, 404);

        return view('admin.price-alerts.show', compact('alert'));
    }

    public function edit(string $id)
    {
        $alert = DB::table('price_alerts')->where('id', $id)->first();
        abort_if(!$alert, 404);

        $users = DB::table('users')->orderBy('email')->get();

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

        return view('admin.price-alerts.edit', compact('alert', 'users', 'variants'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'user_id'       => 'required|integer|exists:users,id',
            'variant_id'    => 'required|integer|exists:product_variants,id',
            'submitted_url' => 'nullable|string|max:255',
            'target_price'  => 'required|numeric|min:0',
            'alert_status'  => 'required|string|max:50',
        ]);

        DB::table('price_alerts')
            ->where('id', $id)
            ->update([
                'user_id'       => $validated['user_id'],
                'variant_id'    => $validated['variant_id'],
                'submitted_url' => $validated['submitted_url'],
                'target_price'  => $validated['target_price'],
                'alert_status'  => $validated['alert_status'],
                'updated_at'    => now(),
            ]);

        return redirect()
            ->route('admin.price-alerts.index')
            ->with('success', 'Cập nhật giá mong muốn thành công.');
    }

    public function destroy(string $id)
    {
        DB::table('price_alerts')->where('id', $id)->delete();

        return redirect()
            ->route('admin.price-alerts.index')
            ->with('success', 'Xóa giá mong muốn thành công.');
    }
}