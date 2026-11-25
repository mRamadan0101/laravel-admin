<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Auth\TwoFactor\Totp;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    /**
     * Setup page when 2FA is off, status and recovery codes when it is on.
     *
     * @param Content $content
     * @param Request $request
     *
     * @return Content
     */
    public function index(Content $content, Request $request)
    {
        $user = Admin::user();

        $data = ['user' => $user, 'recoveryCodes' => $request->session()->get('admin.two_factor_recovery_codes')];

        if (!$user->hasTwoFactorEnabled()) {
            $secret = $request->session()->get('admin.two_factor_setup');

            if (!$secret) {
                $secret = Totp::generateSecret();
                $request->session()->put('admin.two_factor_setup', $secret);
            }

            $data['secret'] = $secret;
            $data['uri'] = Totp::uri($this->issuer(), $user->username, $secret);
        }

        return $content
            ->title(trans('admin.two_factor.title'))
            ->description(trans('admin.two_factor.description'))
            ->body(view('admin::two-factor.manage', $data));
    }

    /**
     * Confirm the pending secret with a code from the app and turn 2FA on.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enable(Request $request)
    {
        $request->validate(['code' => 'required']);

        $secret = $request->session()->get('admin.two_factor_setup');

        if (!$secret || !Totp::verify($secret, $request->input('code'))) {
            return back()->withErrors(['code' => trans('admin.two_factor.invalid_code')]);
        }

        $codes = $this->generateRecoveryCodes();

        Admin::user()->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at'   => now(),
        ])->save();

        $request->session()->forget('admin.two_factor_setup');
        $request->session()->flash('admin.two_factor_recovery_codes', $codes);

        admin_toastr(trans('admin.two_factor.enabled'));

        return redirect(admin_url('auth/two-factor'));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(Request $request)
    {
        if ($response = $this->confirmPassword($request)) {
            return $response;
        }

        Admin::user()->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ])->save();

        admin_toastr(trans('admin.two_factor.disabled'));

        return redirect(admin_url('auth/two-factor'));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        if ($response = $this->confirmPassword($request)) {
            return $response;
        }

        $codes = $this->generateRecoveryCodes();

        Admin::user()->forceFill(['two_factor_recovery_codes' => $codes])->save();

        $request->session()->flash('admin.two_factor_recovery_codes', $codes);

        return redirect(admin_url('auth/two-factor'));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function confirmPassword(Request $request)
    {
        $request->validate(['password' => 'required']);

        if (!Hash::check($request->input('password'), Admin::user()->password)) {
            return back()->withErrors(['password' => trans('admin.two_factor.wrong_password')]);
        }

        return null;
    }

    /**
     * @return array
     */
    protected function generateRecoveryCodes()
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        $chunk = function () use ($alphabet) {
            $chars = '';

            for ($i = 0; $i < 5; $i++) {
                $chars .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            return $chars;
        };

        return collect(range(1, 8))->map(function () use ($chunk) {
            return $chunk().'-'.$chunk();
        })->all();
    }

    /**
     * Name shown for this account in the authenticator app.
     *
     * @return string
     */
    protected function issuer()
    {
        return config('admin.auth.two_factor.issuer')
            ?: trim(strip_tags(config('admin.name'))) ?: config('app.name', 'Laravel-admin');
    }
}
