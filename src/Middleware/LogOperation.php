<?php

namespace Encore\Admin\Middleware;

use Encore\Admin\Auth\Database\OperationLog as OperationLogModel;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogOperation
{
    /**
     * Input keys that are never written to the log, in any nesting level.
     *
     * @var array
     */
    public static $hiddenFields = [
        'password',
        'password_confirmation',
        'current_password',
        'old_password',
        'new_password',
        'two_factor_code',
        'two_factor_secret',
        'recovery_code',
        '_token',
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if ($this->shouldLogOperation($request)) {
            $log = [
                'user_id' => Admin::user()->id,
                'path'    => substr($request->path(), 0, 255),
                'method'  => $request->method(),
                'ip'      => $request->getClientIp(),
                'input'   => json_encode($this->maskInput($request->input())),
            ];

            try {
                OperationLogModel::create($log);
            } catch (\Exception $exception) {
                // pass
            }
        }

        return $next($request);
    }

    /**
     * Replace the values of sensitive keys with a placeholder.
     *
     * @param array $input
     *
     * @return array
     */
    protected function maskInput(array $input)
    {
        $hidden = array_map('strtolower', array_merge(
            static::$hiddenFields,
            (array) config('admin.operation_log.hidden_fields', [])
        ));

        foreach ($input as $key => $value) {
            if (in_array(strtolower((string) $key), $hidden, true)) {
                $input[$key] = '******';
            } elseif (is_array($value)) {
                $input[$key] = $this->maskInput($value);
            }
        }

        return $input;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function shouldLogOperation(Request $request)
    {
        return config('admin.operation_log.enable')
            && !$this->inExceptArray($request)
            && $this->inAllowedMethods($request->method())
            && Admin::user();
    }

    /**
     * Whether requests using this method are allowed to be logged.
     *
     * @param string $method
     *
     * @return bool
     */
    protected function inAllowedMethods($method)
    {
        $allowedMethods = collect(config('admin.operation_log.allowed_methods'))->filter();

        if ($allowedMethods->isEmpty()) {
            return true;
        }

        return $allowedMethods->map(function ($method) {
            return strtoupper($method);
        })->contains($method);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function inExceptArray($request)
    {
        $excepts = array_merge((array) config('admin.operation_log.except'), [
            admin_base_path('_global_search_'),
        ]);

        foreach ($excepts as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            $methods = [];

            if (Str::contains($except, ':')) {
                list($methods, $except) = explode(':', $except);
                $methods = explode(',', $methods);
            }

            $methods = array_map('strtoupper', $methods);

            if ($request->is($except) &&
                (empty($methods) || in_array($request->method(), $methods))) {
                return true;
            }
        }

        return false;
    }
}
