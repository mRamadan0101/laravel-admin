<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\GlobalSearch\GlobalSearch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        abort_unless(GlobalSearch::enabled(), 404);

        $keyword = Str::limit(trim((string) $request->get('q')), 100, '');

        return response()->json([
            'query'  => $keyword,
            'groups' => GlobalSearch::search($keyword),
        ]);
    }
}
