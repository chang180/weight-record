<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * 顯示用戶資料編輯頁面
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => Auth::user()
        ]);
    }

    /**
     * 更新用戶資料
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'height' => ['nullable', 'numeric', 'min:100', 'max:250'],
        ], [
            'name.required' => '姓名為必填項目',
            'name.max' => '姓名不能超過 255 個字符',
            'email.required' => '電子郵件為必填項目',
            'email.email' => '請輸入有效的電子郵件地址',
            'email.unique' => '此電子郵件已被使用',
            'height.numeric' => '身高必須為數字',
            'height.min' => '身高不能少於 100 公分',
            'height.max' => '身高不能超過 250 公分',
        ]);

        $user->update($request->only(['name', 'email', 'height']));

        return redirect()->route('profile.edit')
            ->with('success', '個人資料更新成功！');
    }
}