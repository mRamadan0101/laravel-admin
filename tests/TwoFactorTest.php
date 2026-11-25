<?php

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\TwoFactor\Totp;

class TwoFactorTest extends TestCase
{
    public function testTotpMatchesRfc6238Vectors()
    {
        $secret = Totp::base32Encode('12345678901234567890');

        $this->assertSame('287082', Totp::code($secret, 59));
        $this->assertSame('081804', Totp::code($secret, 1111111109));
        $this->assertSame('005924', Totp::code($secret, 1234567890));
        $this->assertTrue(Totp::verify($secret, '287 082', 1, 89));
        $this->assertFalse(Totp::verify($secret, '287082', 1, 200));
        $this->assertFalse(Totp::verify($secret, 'abcdef'));
    }

    public function testEnableFromSettings()
    {
        $this->be(Administrator::first(), 'admin');

        $this->visit('admin/auth/setting')
            ->see('Two-factor authentication')
            ->visit('admin/auth/two-factor')
            ->see('Set up an authenticator app');

        $secret = session('admin.two_factor_setup');
        $this->assertNotEmpty($secret);

        $this->submitForm('Enable', ['code' => '000000'])
            ->see('The provided code is invalid.');

        $this->submitForm('Enable', ['code' => Totp::code($secret)])
            ->seePageIs('admin/auth/two-factor')
            ->see('Recovery codes');

        $user = Administrator::first();

        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertSame($secret, $user->two_factor_secret);
        $this->assertCount(8, $user->two_factor_recovery_codes);
        $this->assertNotSame($secret, DB::table('admin_users')->where('id', $user->id)->value('two_factor_secret'));
    }

    public function testLoginRequiresCodeWhenEnabled()
    {
        $secret = $this->enableTwoFactor();

        $this->visit('admin/auth/login')
            ->submitForm('Login', ['username' => 'admin', 'password' => 'admin'])
            ->seePageIs('admin/auth/two-factor-challenge')
            ->dontSeeIsAuthenticated('admin');

        $this->visit('admin')->seePageIs('admin/auth/login');

        $this->visit('admin/auth/login')
            ->submitForm('Login', ['username' => 'admin', 'password' => 'admin'])
            ->submitForm('Verify', ['code' => '000000'])
            ->seePageIs('admin/auth/two-factor-challenge')
            ->see('The provided code is invalid.')
            ->dontSeeIsAuthenticated('admin');

        $this->submitForm('Verify', ['code' => Totp::code($secret)])
            ->seePageIs('admin')
            ->seeIsAuthenticated('admin');
    }

    public function testRecoveryCodeWorksOnce()
    {
        $this->enableTwoFactor(['AAAAA-BBBBB', 'CCCCC-DDDDD']);

        $this->visit('admin/auth/login')
            ->submitForm('Login', ['username' => 'admin', 'password' => 'admin']);

        $this->post('admin/auth/two-factor-challenge', ['recovery_code' => 'aaaaa-bbbbb'])
            ->seeIsAuthenticated('admin');

        $this->assertSame(['CCCCC-DDDDD'], Administrator::first()->two_factor_recovery_codes);
    }

    public function testChallengeWithoutPendingLoginRedirects()
    {
        $this->visit('admin/auth/two-factor-challenge')
            ->seePageIs('admin/auth/login');
    }

    public function testDisableRequiresPassword()
    {
        $this->enableTwoFactor();

        $this->be(Administrator::first(), 'admin');

        $this->visit('admin/auth/two-factor')
            ->see('Disable two-factor authentication')
            ->submitForm('Disable two-factor authentication', ['password' => 'wrong'])
            ->see('The password is incorrect.');

        $this->assertTrue(Administrator::first()->hasTwoFactorEnabled());

        $this->submitForm('Disable two-factor authentication', ['password' => 'admin'])
            ->see('Set up an authenticator app');

        $this->assertFalse(Administrator::first()->hasTwoFactorEnabled());
    }

    protected function enableTwoFactor(array $codes = ['AAAAA-BBBBB'])
    {
        $secret = Totp::generateSecret();

        Administrator::first()->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at'   => now(),
        ])->save();

        return $secret;
    }
}
