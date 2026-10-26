<?php

namespace Encore\Admin;

use Closure;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Controllers\AuthController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Traits\HasAssets;
use Encore\Admin\Widgets\Navbar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/**
 * Class Admin.
 */
class Admin
{
    use HasAssets;

    /**
     * The Laravel admin version.
     *
     * @var string
     */
    const VERSION = '2.0.0';

    /**
     * @var Navbar
     */
    protected $navbar;

    /**
     * @var array
     */
    protected $menu = [];

    /**
     * @var string
     */
    public static $metaTitle;

    /**
     * @var string
     */
    public static $favicon;

    /**
     * @var array
     */
    public static $extensions = [];

    /**
     * @var []Closure
     */
    protected static $bootingCallbacks = [];

    /**
     * @var []Closure
     */
    protected static $bootedCallbacks = [];

    /**
     * Returns the long version of Laravel-admin.
     *
     * @return string The long application version
     */
    public static function getLongVersion()
    {
        return sprintf('Laravel-admin <comment>version</comment> <info>%s</info>', self::VERSION);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \Encore\Admin\Grid
     *
     * @deprecated since v1.6.1
     */
    public function grid($model, Closure $callable)
    {
        return new Grid($this->getModel($model), $callable);
    }

    /**
     * @param $model
     * @param Closure $callable
     *
     * @return \Encore\Admin\Form
     *
     *  @deprecated since v1.6.1
     */
    public function form($model, Closure $callable)
    {
        return new Form($this->getModel($model), $callable);
    }

    /**
     * Build a tree.
     *
     * @param $model
     * @param Closure|null $callable
     *
     * @return \Encore\Admin\Tree
     */
    public function tree($model, ?Closure $callable = null)
    {
        return new Tree($this->getModel($model), $callable);
    }

    /**
     * Build show page.
     *
     * @param $model
     * @param mixed $callable
     *
     * @return Show
     *
     * @deprecated since v1.6.1
     */
    public function show($model, $callable = null)
    {
        return new Show($this->getModel($model), $callable);
    }

    /**
     * @param Closure $callable
     *
     * @return \Encore\Admin\Layout\Content
     *
     * @deprecated since v1.6.1
     */
    public function content(?Closure $callable = null)
    {
        return new Content($callable);
    }

    /**
     * @param $model
     *
     * @return mixed
     */
    public function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model) && class_exists($model)) {
            return $this->getModel(new $model());
        }

        throw new InvalidArgumentException("$model is not a valid model");
    }

    /**
     * Left sider-bar menu.
     *
     * @return array
     */
    public function menu()
    {
        if (!empty($this->menu)) {
            return $this->menu;
        }

        $menuClass = config('admin.database.menu_model');

        /** @var Menu $menuModel */
        $menuModel = new $menuClass();

        return $this->menu = $menuModel->toTree();
    }

    /**
     * @param array $menu
     *
     * @return array
     */
    public function menuLinks($menu = [])
    {
        if (empty($menu)) {
            $menu = $this->menu();
        }

        $links = [];

        foreach ($menu as $item) {
            if (!empty($item['children'])) {
                $links = array_merge($links, $this->menuLinks($item['children']));
            } else {
                $links[] = Arr::only($item, ['title', 'uri', 'icon']);
            }
        }

        return $links;
    }

    /**
     * Set admin title.
     *
     * @param string $title
     *
     * @return void
     */
    public static function setTitle($title)
    {
        self::$metaTitle = $title;
    }

    /**
     * Get admin title.
     *
     * @return string
     */
    public function title()
    {
        return self::$metaTitle ? self::$metaTitle : config('admin.title');
    }

    /**
     * @param null|string $favicon
     *
     * @return string|void
     */
    public static function favicon($favicon = null)
    {
        if (is_null($favicon)) {
            return static::$favicon;
        }

        static::$favicon = $favicon;
    }

    /**
     * Parse `admin.skin` into an accent color and a sidebar color mode.
     *
     * @return array{color: string, sidebar: string}
     */
    public function skin()
    {
        $skin = preg_replace('/^skin-/', '', (string) config('admin.skin', 'blue-light'));

        $light = str_ends_with($skin, '-light');

        $color = $light ? substr($skin, 0, -6) : $skin;

        if (!in_array($color, ['blue', 'yellow', 'green', 'purple', 'red', 'black'], true)) {
            $color = 'blue';
        }

        return ['color' => $color, 'sidebar' => $light ? 'light' : 'dark'];
    }

    /**
     * CSS classes of the <body> element, derived from `admin.layout`.
     *
     * @return string
     */
    public function bodyClass()
    {
        $map = [
            'fixed'            => 'layout-fixed fixed-header',
            'fixed-footer'     => 'fixed-footer',
            'sidebar-mini'     => 'sidebar-mini',
            'sidebar-collapse' => 'sidebar-collapse',
        ];

        $classes = ['sidebar-expand-lg', 'bg-body-tertiary', 'skin-'.$this->skin()['color']];

        foreach ((array) config('admin.layout', []) as $layout) {
            $classes[] = $map[$layout] ?? $layout;
        }

        return implode(' ', array_unique($classes));
    }

    /**
     * Initial Bootstrap color mode: light, dark or auto.
     *
     * @return string
     */
    public function theme()
    {
        $theme = config('admin.theme', 'light');

        return in_array($theme, ['light', 'dark', 'auto'], true) ? $theme : 'light';
    }

    /**
     * Locales that use a right-to-left layout.
     *
     * @var string[]
     */
    public const RTL_LOCALES = ['ar', 'fa', 'he', 'ur', 'ku', 'ps'];

    /**
     * Application locale captured before the admin switcher overrides it.
     *
     * @var string|null
     */
    protected static $defaultLocale;

    /**
     * Apply the locale stored by the navbar language switcher.
     */
    public function applyLocale()
    {
        if (is_null(static::$defaultLocale)) {
            static::$defaultLocale = (string) config('app.locale');
        }

        if (!$this->localeSwitcherEnabled()) {
            return;
        }

        $locale = session('admin.locale');

        if (is_string($locale) && ($resolved = $this->resolveLocale($locale))) {
            app()->setLocale($resolved);
        }
    }

    /**
     * Whether the navbar should show a language switcher.
     *
     * Shown when `admin.rtl` is `true` or a locale code such as `"ar"`.
     *
     * @return bool
     */
    public function localeSwitcherEnabled()
    {
        $rtl = config('admin.rtl');

        if ($rtl === false || $rtl === null || $rtl === '') {
            return false;
        }

        return count($this->locales()) > 1;
    }

    /**
     * Locales offered by the navbar switcher: app language + RTL language.
     *
     * @return array<string, string>
     */
    public function locales()
    {
        $rtl = config('admin.rtl');
        $locales = [];

        $push = function ($code) use (&$locales) {
            $code = str_replace('_', '-', trim((string) $code));

            if ($code !== '') {
                $locales[$code] = static::localeName($code);
            }
        };

        $push(static::$defaultLocale ?: config('app.locale'));
        $push(config('app.fallback_locale', 'en'));

        if (is_string($rtl) && $rtl !== '') {
            $push($rtl);
        } elseif ($rtl === true) {
            $push('ar');
        }

        return $locales;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function resolveLocale($locale)
    {
        foreach (array_keys($this->locales()) as $code) {
            if (static::localeCodesMatch($locale, $code)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Native display name of a locale.
     *
     * @param string $locale
     *
     * @return string
     */
    public static function localeName($locale)
    {
        $names = [
            'ar'    => 'العربية',
            'az'    => 'Azərbaycan',
            'bn'    => 'বাংলা',
            'de'    => 'Deutsch',
            'en'    => 'English',
            'es'    => 'Español',
            'fa'    => 'فارسی',
            'fr'    => 'Français',
            'he'    => 'עברית',
            'id'    => 'Indonesia',
            'ja'    => '日本語',
            'ko'    => '한국어',
            'ms'    => 'Bahasa Melayu',
            'nl'    => 'Nederlands',
            'pl'    => 'Polski',
            'pt'    => 'Português',
            'pt-BR' => 'Português (Brasil)',
            'ru'    => 'Русский',
            'tr'    => 'Türkçe',
            'uk'    => 'Українська',
            'ur'    => 'اردو',
            'zh-CN' => '简体中文',
            'zh-TW' => '繁體中文',
        ];

        $locale = str_replace('_', '-', (string) $locale);

        if (isset($names[$locale])) {
            return $names[$locale];
        }

        $short = strtolower(strstr($locale, '-', true) ?: $locale);

        return $names[$short] ?? strtoupper($short);
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public static function localeIsRtl($locale)
    {
        return in_array(strtolower(substr((string) $locale, 0, 2)), self::RTL_LOCALES, true);
    }

    /**
     * @param string $a
     * @param string $b
     *
     * @return bool
     */
    public static function localeCodesMatch($a, $b)
    {
        $norm = function ($value) {
            return strtolower(str_replace('_', '-', (string) $value));
        };

        $a = $norm($a);
        $b = $norm($b);

        return $a === $b || str_starts_with($a, $b.'-') || str_starts_with($b, $a.'-');
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->guard()->user();
    }

    /**
     * Attempt to get the guard from the local cache.
     *
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function guard()
    {
        $guard = config('admin.auth.guard') ?: 'admin';

        return Auth::guard($guard);
    }

    /**
     * Set navbar.
     *
     * @param Closure|null $builder
     *
     * @return Navbar
     */
    public function navbar(?Closure $builder = null)
    {
        if (is_null($builder)) {
            return $this->getNavbar();
        }

        call_user_func($builder, $this->getNavbar());
    }

    /**
     * Get navbar object.
     *
     * @return \Encore\Admin\Widgets\Navbar
     */
    public function getNavbar()
    {
        if (is_null($this->navbar)) {
            $this->navbar = new Navbar();
        }

        return $this->navbar;
    }

    /**
     * Register the laravel-admin builtin routes.
     *
     * @return void
     *
     * @deprecated Use Admin::routes() instead();
     */
    public function registerAuthRoutes()
    {
        $this->routes();
    }

    /**
     * Register the laravel-admin builtin routes.
     *
     * @return void
     */
    public function routes()
    {
        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];

        app('router')->group($attributes, function ($router) {

            /* @var \Illuminate\Routing\Router $router */
            $router->resource('auth/users', Controllers\UserController::class)->names('admin.auth.users');
            $router->resource('auth/roles', Controllers\RoleController::class)->names('admin.auth.roles');
            $router->resource('auth/permissions', Controllers\PermissionController::class)->names('admin.auth.permissions');
            $router->resource('auth/menu', Controllers\MenuController::class, ['except' => ['create']])->names('admin.auth.menu');
            $router->resource('auth/logs', Controllers\LogController::class, ['only' => ['index', 'destroy']])->names('admin.auth.logs');

            $router->post('_handle_form_', [Controllers\HandleController::class, 'handleForm'])->name('admin.handle-form');
            $router->post('_handle_action_', [Controllers\HandleController::class, 'handleAction'])->name('admin.handle-action');
            $router->get('_handle_selectable_', [Controllers\HandleController::class, 'handleSelectable'])->name('admin.handle-selectable');
            $router->get('_handle_renderable_', [Controllers\HandleController::class, 'handleRenderable'])->name('admin.handle-renderable');

            $authController = config('admin.auth.controller', AuthController::class);

            $router->get('auth/login', [$authController, 'getLogin'])->name('admin.login');
            $router->post('auth/login', [$authController, 'postLogin']);
            $router->get('auth/logout', [$authController, 'getLogout'])->name('admin.logout');
            $router->get('auth/setting', [$authController, 'getSetting'])->name('admin.setting');
            $router->put('auth/setting', [$authController, 'putSetting']);
            $router->get('locale/{locale}', [Controllers\LocaleController::class, 'update'])
                ->name('admin.locale')
                ->where('locale', '[A-Za-z0-9_-]+');
        });
    }

    /**
     * Extend a extension.
     *
     * @param string $name
     * @param string $class
     *
     * @return void
     */
    public static function extend($name, $class)
    {
        static::$extensions[$name] = $class;
    }

    /**
     * @param callable $callback
     */
    public static function booting(callable $callback)
    {
        static::$bootingCallbacks[] = $callback;
    }

    /**
     * @param callable $callback
     */
    public static function booted(callable $callback)
    {
        static::$bootedCallbacks[] = $callback;
    }

    /**
     * Bootstrap the admin application.
     */
    public function bootstrap()
    {
        $this->applyLocale();

        $this->fireBootingCallbacks();

        require config('admin.bootstrap', admin_path('bootstrap.php'));

        $this->addAdminAssets();

        $this->fireBootedCallbacks();
    }

    /**
     * Add JS & CSS assets to pages.
     */
    protected function addAdminAssets()
    {
        $assets = Form::collectFieldAssets();

        self::css($assets['css']);
        self::js($assets['js']);
        self::js($this->localeAssets());
        self::js('vendor/laravel-admin/laravel-admin/plugins.js');
    }

    /**
     * Locale files of flatpickr and select2 that match the application locale.
     *
     * @return array
     */
    protected function localeAssets()
    {
        $locale = str_replace('_', '-', (string) config('app.locale'));
        $short = strtolower(strstr($locale, '-', true) ?: $locale);
        $assets = __DIR__.'/../resources/assets';
        $js = [];

        $candidates = [
            'flatpickr/dist/l10n/' => [strtolower($locale), $short],
            'select2/js/i18n/'     => [$locale, $short],
        ];

        foreach ($candidates as $dir => $names) {
            foreach (array_unique($names) as $name) {
                if ($name !== 'en' && is_file("{$assets}/{$dir}{$name}.js")) {
                    $js[] = "vendor/laravel-admin/{$dir}{$name}.js";
                    break;
                }
            }
        }

        return $js;
    }

    /**
     * Call the booting callbacks for the admin application.
     */
    protected function fireBootingCallbacks()
    {
        foreach (static::$bootingCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    /**
     * Call the booted callbacks for the admin application.
     */
    protected function fireBootedCallbacks()
    {
        foreach (static::$bootedCallbacks as $callable) {
            call_user_func($callable);
        }
    }

    /*
     * Disable Pjax for current Request
     *
     * @return void
     */
    public function disablePjax()
    {
        if (request()->pjax()) {
            request()->headers->set('X-PJAX', false);
        }
    }
}
