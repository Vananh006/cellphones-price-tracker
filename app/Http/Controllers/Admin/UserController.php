<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->q);

        $users = User::query()
            ->when($q, function ($query) use ($q) {
                $query->where('email', 'like', "%{$q}%")
                    ->orWhere('user_name', 'like', "%{$q}%")
                    ->orWhere('role', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->get();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_name' => 'nullable|string|max:100',
            'email'     => 'required|email|max:150|unique:users,email',
            'password'  => 'required|string|min:6',
            'role'      => ['required', Rule::in(['admin', 'user'])],
        ]);

        User::create([
            'user_name' => $validated['user_name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Thêm user thành công.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'user_name' => 'nullable|string|max:100',
            'email'     => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => 'nullable|string|min:6',
            'role'      => ['required', Rule::in(['admin', 'user'])],
        ]);

        $data = [
            'user_name' => $validated['user_name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Cập nhật user thành công.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Xóa user thành công.');
    }
}