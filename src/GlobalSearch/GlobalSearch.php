<?php

namespace Encore\Admin\GlobalSearch;

use Encore\Admin\Facades\Admin;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class GlobalSearch
{
    /**
     * @var SearchResource[]
     */
    protected static $resources = [];

    /**
     * Register a model in the navbar search.
     *
     * @param string $model
     *
     * @return SearchResource
     */
    public static function resource($model)
    {
        return static::$resources[$model] = new SearchResource($model);
    }

    /**
     * Resources registered in code plus the ones in `admin.global_search.resources`.
     *
     * @return SearchResource[]
     */
    public static function resources()
    {
        $resources = [];

        foreach ((array) config('admin.global_search.resources', []) as $model => $options) {
            $resources[$model] = SearchResource::fromArray($model, (array) $options);
        }

        return array_values(array_merge($resources, static::$resources));
    }

    /**
     * @return void
     */
    public static function flush()
    {
        static::$resources = [];
    }

    /**
     * @return bool
     */
    public static function enabled()
    {
        return (bool) config('admin.global_search.enable', true);
    }

    /**
     * Search menu pages and every resource the current user may see.
     *
     * @param string $keyword
     *
     * @return array
     */
    public static function search($keyword)
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return [];
        }

        $limit = (int) config('admin.global_search.limit', 5);
        $user = Admin::user();
        $groups = [];

        if (config('admin.global_search.menu', true) && $pages = static::searchMenu($keyword, $limit)) {
            $groups[] = ['label' => trans('admin.menu'), 'icon' => 'fa-solid fa-bars', 'items' => $pages];
        }

        foreach (static::resources() as $resource) {
            if (!$resource->authorized($user)) {
                continue;
            }

            try {
                $items = $resource->search($keyword, $limit);
            } catch (\Throwable $exception) {
                report($exception);

                continue;
            }

            if ($items) {
                $groups[] = $resource->toArray() + ['items' => $items];
            }
        }

        return $groups;
    }

    /**
     * @param string $keyword
     * @param int    $limit
     *
     * @return array
     */
    protected static function searchMenu($keyword, $limit)
    {
        $pages = [];

        static::walkMenu(Admin::menu(), [], function ($item, $trail) use ($keyword, &$pages) {
            $title = static::menuTitle($item['title']);

            if (Str::contains(Str::lower($title), Str::lower($keyword))) {
                $pages[] = [
                    'title'    => $title,
                    'subtitle' => implode(' / ', $trail) ?: null,
                    'url'      => url()->isValidUrl($item['uri']) ? $item['uri'] : admin_url($item['uri']),
                ];
            }
        });

        return array_slice($pages, 0, $limit);
    }

    /**
     * Visit the leaf menu items the current user is allowed to see.
     *
     * @param array    $menu
     * @param array    $trail
     * @param callable $callback
     *
     * @return void
     */
    protected static function walkMenu($menu, $trail, callable $callback)
    {
        $user = Admin::user();

        foreach ($menu as $item) {
            if (!$user->visible(Arr::get($item, 'roles', [])) || !$user->can(Arr::get($item, 'permission'))) {
                continue;
            }

            if (!empty($item['children'])) {
                static::walkMenu($item['children'], array_merge($trail, [static::menuTitle($item['title'])]), $callback);
            } elseif (!empty($item['uri'])) {
                $callback($item, $trail);
            }
        }
    }

    /**
     * @param string $title
     *
     * @return string
     */
    protected static function menuTitle($title)
    {
        $key = 'admin.menu_titles.'.trim(str_replace(' ', '_', strtolower($title)));

        return Lang::has($key) ? __($key) : admin_trans($title);
    }
}
