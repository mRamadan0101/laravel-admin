<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Auth\TwoFactor\Totp;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * @var string
     */
    protected $loginView = 'admin::login';

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view($this->loginView);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $this->loginValidator($request->all())->validate();

        $throttleKey = $this->throttleKey($request);

        if ($seconds = $this->lockedOutFor($throttleKey)) {
            return $this->sendLockoutResponse($request, $seconds);
        }

        $credentials = $request->only([$this->username(), 'password']);
        $remember = (bool) $request->get('remember', false);

        $guard = $this->guard();

        if (!$guard->validate($credentials)) {
            $this->hitThrottle($throttleKey);

            return back()->withInput($request->only($this->username(), 'remember'))->withErrors([
                $this->username() => $this->getFailedLoginMessage(),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user = $guard->getLastAttempted();

        if ($this->twoFactorRequired($user)) {
            $request->session()->put('admin.two_factor', [
                'id'       => $user->getAuthIdentifier(),
                'remember' => $remember,
                'expires'  => time() + 300,
            ]);

            return redirect(admin_url('auth/two-factor-challenge'));
        }

        $guard->login($user, $remember);

        return $this->sendLoginResponse($request);
    }

    /**
     * Show the two-factor challenge after a successful password check.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function getTwoFactorChallenge(Request $request)
    {
        if (!$this->pendingTwoFactorUser($request)) {
            return redirect(admin_url('auth/login'));
        }

        return view('admin::two-factor.challenge');
    }

    /**
     * Verify the one-time code or a recovery code and finish logging in.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTwoFactorChallenge(Request $request)
    {
        if (!$user = $this->pendingTwoFactorUser($request)) {
            return redirect(admin_url('auth/login'));
        }

        $throttleKey = 'admin-two-factor|'.$user->getAuthIdentifier().'|'.$request->ip();

        if ($seconds = $this->lockedOutFor($throttleKey)) {
            return back()->withErrors(['code' => $this->getLockoutMessage($seconds)]);
        }

        $valid = $request->filled('recovery_code')
            ? $user->useRecoveryCode($request->input('recovery_code'))
            : Totp::verify($user->two_factor_secret, $request->input('code'));

        if (!$valid) {
            $this->hitThrottle($throttleKey);

            return back()->withErrors(['code' => trans('admin.two_factor.invalid_code')]);
        }

        RateLimiter::clear($throttleKey);

        $remember = $request->session()->get('admin.two_factor.remember', false);

        $request->session()->forget('admin.two_factor');

        $this->guard()->login($user, $remember);

        return $this->sendLoginResponse($request);
    }

    /**
     * The user that passed the password step and still owes a 2FA code.
     *
     * @param Request $request
     *
     * @return \Encore\Admin\Auth\Database\Administrator|null
     */
    protected function pendingTwoFactorUser(Request $request)
    {
        $pending = $request->session()->get('admin.two_factor');

        if (!$pending || $pending['expires'] < time()) {
            $request->session()->forget('admin.two_factor');

            return null;
        }

        $user = $this->guard()->getProvider()->retrieveById($pending['id']);

        return $this->twoFactorRequired($user) ? $user : null;
    }

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     *
     * @return bool
     */
    protected function twoFactorRequired($user)
    {
        return config('admin.auth.two_factor.enable', true)
            && $user
            && method_exists($user, 'hasTwoFactorEnabled')
            && $user->hasTwoFactorEnabled();
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return 'admin-login|'.Str::transliterate(Str::lower((string) $request->input($this->username()))).'|'.$request->ip();
    }

    /**
     * Seconds left until the key may try again, 0 when it is not locked.
     *
     * @param string $key
     *
     * @return int
     */
    protected function lockedOutFor($key)
    {
        if (!config('admin.auth.throttle.enable', true)) {
            return 0;
        }

        if (!RateLimiter::tooManyAttempts($key, config('admin.auth.throttle.max_attempts', 5))) {
            return 0;
        }

        return max(1, RateLimiter::availableIn($key));
    }

    /**
     * @param string $key
     *
     * @return void
     */
    protected function hitThrottle($key)
    {
        if (config('admin.auth.throttle.enable', true)) {
            RateLimiter::hit($key, config('admin.auth.throttle.decay_seconds', 60));
        }
    }

    /**
     * @param Request $request
     * @param int     $seconds
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLockoutResponse(Request $request, $seconds)
    {
        return back()->withInput($request->only($this->username(), 'remember'))->withErrors([
            $this->username() => $this->getLockoutMessage($seconds),
        ]);
    }

    /**
     * @param int $seconds
     *
     * @return string
     */
    protected function getLockoutMessage($seconds)
    {
        return trans('admin.login_throttled', ['seconds' => $seconds]);
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->disableView();
            }
        );

        $content
            ->title(trans('admin.user_setting'))
            ->body($form->edit(Admin::user()->id));

        if (config('admin.auth.two_factor.enable', true)) {
            $content->row(new Box(
                trans('admin.two_factor.title'),
                view('admin::two-factor.summary', ['user' => Admin::user()])
            ));
        }

        return $content;
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->display('username', trans('admin.username'));
        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('confirmed|required');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->setAction(admin_url('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_url('auth/setting'));
        });

        return $form;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }
}
