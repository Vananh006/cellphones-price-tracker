<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->q);

        $favorites = DB::table('favorites as f')
            ->leftJoin('users as u', 'f.user_id', '=', 'u.id')
            ->leftJoin('products as p', 'f.product_id', '=', 'p.id')
            ->select(
                'f.id',
                'f.user_id',
                'f.product_id',
                'f.created_at',
                'u.user_name',
                'u.email',
                'u.role',
                'p.product_name',
                'p.brand',
                'p.thumbnail_url',
                'p.view_count'
            )
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('u.user_name', 'like', "%{$q}%")
                        ->orWhere('u.email', 'like', "%{$q}%")
                        ->orWhere('p.product_name', 'like', "%{$q}%")
                        ->orWhere('p.brand', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('f.id')
            ->get();

        return view('admin.favorites.index', compact('favorites', 'q'));
    }

    public function create()
    {
        $users = DB::table('users')->orderBy('email')->get();
        $products = DB::table('products')->orderBy('product_name')->get();

        return view('admin.favorites.create', compact('users', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('favorites')->where(function ($query) use ($request) {
                    return $query->where('product_id', $request->product_id);
                }),
            ],
            'product_id' => 'required|integer|exists:products,id',
        ], [
            'user_id.unique' => 'User này đã theo dõi sản phẩm này rồi.',
        ]);

        DB::table('favorites')->insert([
            'user_id' => $validated['user_id'],
            'product_id' => $validated['product_id'],
            'created_at' => now(),
        ]);

        return redirect()
            ->route('admin.favorites.index')
            ->with('success', 'Thêm vào danh sách theo dõi thành công.');
    }

    public function show(string $id)
    {
        $favorite = DB::table('favorites as f')
            ->leftJoin('users as u', 'f.user_id', '=', 'u.id')
            ->leftJoin('products as p', 'f.product_id', '=', 'p.id')
            ->select(
                'f.id',
                'f.user_id',
                'f.product_id',
                'f.created_at',
                'u.user_name',
                'u.email',
                'u.role',
                'p.product_name',
                'p.brand',
                'p.thumbnail_url',
                'p.view_count',
                'p.created_at as product_created_at',
                'p.updated_at as product_updated_at'
            )
            ->where('f.id', $id)
            ->first();

        abort_if(!$favorite, 404);

        return view('admin.favorites.show', compact('favorite'));
    }

    public function edit(string $id)
    {
        $favorite = DB::table('favorites')->where('id', $id)->first();
        abort_if(!$favorite, 404);

        $users = DB::table('users')->orderBy('email')->get();
        $products = DB::table('products')->orderBy('product_name')->get();

        return view('admin.favorites.edit', compact('favorite', 'users', 'products'));
    }

    public function update(Request $request, string $id)
    {
        $favorite = DB::table('favorites')->where('id', $id)->first();
        abort_if(!$favorite, 404);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('favorites')->where(function ($query) use ($request) {
                    return $query->where('product_id', $request->product_id);
                })->ignore($id),
            ],
            'product_id' => 'required|integer|exists:products,id',
        ], [
            'user_id.unique' => 'User này đã theo dõi sản phẩm này rồi.',
        ]);

        DB::table('favorites')
            ->where('id', $id)
            ->update([
                'user_id' => $validated['user_id'],
                'product_id' => $validated['product_id'],
            ]);

        return redirect()
            ->route('admin.favorites.index')
            ->with('success', 'Cập nhật danh sách theo dõi thành công.');
    }

    public function destroy(string $id)
    {
        DB::table('favorites')->where('id', $id)->delete();

        return redirect()
            ->route('admin.favorites.index')
            ->with('success', 'Xóa khỏi danh sách theo dõi thành công.');
    }
}