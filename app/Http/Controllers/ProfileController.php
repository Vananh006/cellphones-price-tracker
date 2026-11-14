<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $validated = $request->validate([
            'user_name' => ['required', 'string', 'max:150'],
        ], [
            'user_name.required' => 'Vui lòng nhập tên hiển thị.',
            'user_name.string' => 'Tên hiển thị không hợp lệ.',
            'user_name.max' => 'Tên hiển thị không được vượt quá 150 ký tự.',
        ]);

        $user->user_name = $validated['user_name'];
        $user->save();

        return redirect()
            ->route('profile.index')
            ->with('success', 'Cập nhật hồ sơ thành công.');
    }

    public function changePassword(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $validated = $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors([
                    'current_password' => 'Mật khẩu hiện tại không đúng.',
                ])
                ->withInput();
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return redirect()
            ->route('profile.index')
            ->with('success', 'Đổi mật khẩu thành công.');
    }
}
