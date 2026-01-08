<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_google_login_redirect_works()
    {
        // 跳過本機環境測試（需要實際的 Google OAuth 憑證）
        if (env('APP_ENV') === 'local' || empty(env('GOOGLE_CLIENT_ID'))) {
            $this->markTestSkipped('跳過本機環境測試：需要 Google OAuth 憑證');
        }

        $response = $this->get('/auth/google');

        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_new_user_can_register_with_google()
    {
        $googleUser = $this->mockGoogleUser([
            'id' => '123456789',
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->password);
    }

    public function test_existing_user_can_link_google_account()
    {
        // 建立一個傳統註冊的用戶（沒有 provider）
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'provider' => null,
            'provider_id' => null,
            'password' => bcrypt('password'),
        ]);

        $googleUser = $this->mockGoogleUser([
            'id' => '987654321',
            'name' => 'Existing User',
            'email' => 'existing@example.com',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
        $response->assertSessionHas('status', '您的 Google 帳號已成功連結到現有帳戶！');

        // 確認用戶已更新
        $user->refresh();
        $this->assertEquals('google', $user->provider);
        $this->assertEquals('987654321', $user->provider_id);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_existing_google_user_can_login()
    {
        // 建立一個已連結 Google 的用戶
        $user = User::factory()->create([
            'email' => 'google@example.com',
            'name' => 'Google User',
            'provider' => 'google',
            'provider_id' => '111222333',
            'password' => null,
        ]);

        $googleUser = $this->mockGoogleUser([
            'id' => '111222333',
            'name' => 'Google User',
            'email' => 'google@example.com',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);

        // 確認是同一用戶
        $this->assertEquals($user->id, auth()->id());
    }

    public function test_cannot_link_google_to_existing_user_with_different_provider()
    {
        // 建立一個已連結其他社群平台的用戶
        $user = User::factory()->create([
            'email' => 'other@example.com',
            'name' => 'Other Provider User',
            'provider' => 'facebook', // 假設有其他社群平台
            'provider_id' => 'facebook123',
        ]);

        $googleUser = $this->mockGoogleUser([
            'id' => '999888777',
            'name' => 'Other Provider User',
            'email' => 'other@example.com',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_handles_socialite_exception()
    {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andThrow(new \Exception('OAuth error'));

        $response = $this->get('/auth/google/callback');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('error');
    }

    public function test_unverified_user_email_gets_verified_when_linking_google()
    {
        // 建立一個未驗證 email 的用戶
        $user = User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'name' => 'Unverified User',
            'provider' => null,
            'provider_id' => null,
        ]);

        $googleUser = $this->mockGoogleUser([
            'id' => '555666777',
            'name' => 'Unverified User',
            'email' => 'unverified@example.com',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andReturn($googleUser);

        $this->get('/auth/google/callback');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * 建立模擬的 Google 用戶物件
     */
    private function mockGoogleUser(array $attributes): SocialiteUser
    {
        $user = Mockery::mock(SocialiteUser::class);
        $user->id = $attributes['id'];
        $user->name = $attributes['name'];
        $user->email = $attributes['email'];
        $user->avatar = $attributes['avatar'] ?? null;

        $user->shouldReceive('getId')->andReturn($attributes['id']);
        $user->shouldReceive('getName')->andReturn($attributes['name']);
        $user->shouldReceive('getEmail')->andReturn($attributes['email']);
        $user->shouldReceive('getAvatar')->andReturn($attributes['avatar'] ?? null);

        return $user;
    }
}
