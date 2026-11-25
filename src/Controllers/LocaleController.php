<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LocaleController extends Controller
{
    /**
     * Persist the chosen admin locale and reload so direction/CSS follow it.
     *
     * @param Request $request
     * @param string  $locale
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $locale)
    {
        if (!Admin::localeSwitcherEnabled() || !($resolved = Admin::resolveLocale($locale))) {
            abort(404);
        }

        $request->session()->put('admin.locale', $resolved);

        $previous = $request->headers->get('referer');

        if (!$previous || str_contains($previous, '/locale/')) {
            $previous = admin_url('/');
        }

        return redirect()->to($previous);
    }
}
