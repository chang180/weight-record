<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * 重定向到 Google OAuth 提供者
     */
    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * 處理 Google OAuth 回調
     */
    public function handleProviderCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['error' => '無法從 Google 取得用戶資訊，請稍後再試。']);
        }

        // 檢查是否已有使用相同 provider_id 的用戶
        $user = User::where('provider', 'google')
            ->where('provider_id', $googleUser->getId())
            ->first();

        if ($user) {
            // 用戶已存在，直接登入
            Auth::login($user);
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // 檢查 email 是否已被其他用戶使用
        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            // 如果該用戶已有 provider，表示是其他社群帳號
            if ($existingUser->provider && $existingUser->provider !== 'google') {
                return redirect()->route('login')
                    ->withErrors(['email' => '此電子郵件已被其他登入方式使用，請使用原本的登入方式。']);
            }

            // 如果該用戶沒有 provider，表示是傳統註冊的用戶，自動連結帳號
            if (!$existingUser->provider) {
                // 更新現有帳號，連結 Google 帳號
                $existingUser->update([
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    // 如果 email 尚未驗證，標記為已驗證（Google 已驗證）
                    'email_verified_at' => $existingUser->email_verified_at ?? now(),
                ]);

                Auth::login($existingUser);

                return redirect(RouteServiceProvider::HOME)
                    ->with('status', '您的 Google 帳號已成功連結到現有帳戶！');
            }
        }

        // 建立新用戶
        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'provider' => 'google',
            'provider_id' => $googleUser->getId(),
            'email_verified_at' => now(), // Google 已驗證 email
            'password' => null, // 社群登入不需要密碼
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
