<?php

namespace Encore\Admin\GlobalSearch;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SearchResource
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @var string
     */
    protected $label;

    /**
     * Searchable columns, `relation.column` searches through a relation.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * @var string|Closure
     */
    protected $title;

    /**
     * @var string|Closure|null
     */
    protected $subtitle;

    /**
     * @var string|Closure
     */
    protected $url;

    /**
     * @var string
     */
    protected $icon = 'fa-file-lines';

    /**
     * @var string|null
     */
    protected $permission;

    /**
     * @var Closure|null
     */
    protected $query;

    /**
     * @param string $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Build from a `admin.global_search.resources` config entry.
     *
     * @param string $model
     * @param array  $options
     *
     * @return static
     */
    public static function fromArray($model, array $options)
    {
        $resource = new static($model);

        foreach (['label', 'columns', 'title', 'subtitle', 'url', 'icon', 'permission'] as $option) {
            if (array_key_exists($option, $options)) {
                $resource->{$option}($options[$option]);
            }
        }

        return $resource;
    }

    public function label($label)
    {
        $this->label = $label;

        return $this;
    }

    public function columns($columns)
    {
        $this->columns = (array) $columns;

        return $this;
    }

    /**
     * @param string|Closure $title column name, or closure receiving the model
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string|Closure|null $subtitle column name, or closure receiving the model
     */
    public function subtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * @param string|Closure $url admin path with `{column}` placeholders, or closure receiving the model
     */
    public function url($url)
    {
        $this->url = $url;

        return $this;
    }

    public function icon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Only show this resource to users that have the permission slug.
     */
    public function permission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Customize the base query, e.g. to scope or eager load.
     */
    public function query(Closure $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param \Encore\Admin\Auth\Database\Administrator|null $user
     *
     * @return bool
     */
    public function authorized($user)
    {
        return !$this->permission || ($user && $user->can($this->permission));
    }

    /**
     * @param string $keyword
     * @param int    $limit
     *
     * @return array
     */
    public function search($keyword, $limit)
    {
        if (empty($this->columns)) {
            return [];
        }

        /** @var Model $model */
        $model = new $this->model();
        $query = $model->newQuery();

        if ($this->query) {
            call_user_func($this->query, $query);
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword).'%';

        $query->where(function (Builder $query) use ($like) {
            foreach ($this->columns as $column) {
                if (Str::contains($column, '.')) {
                    $relation = Str::beforeLast($column, '.');
                    $attribute = Str::afterLast($column, '.');

                    $query->orWhereHas($relation, function (Builder $query) use ($attribute, $like) {
                        $query->where($query->qualifyColumn($attribute), 'like', $like);
                    });
                } else {
                    $query->orWhere($query->qualifyColumn($column), 'like', $like);
                }
            }
        });

        return $query->limit($limit)->get()->map(function (Model $record) {
            return [
                'title'    => (string) $this->resolve($this->title ?: $this->columns[0], $record),
                'subtitle' => $this->subtitle ? (string) $this->resolve($this->subtitle, $record) : null,
                'url'      => $this->resolveUrl($record),
            ];
        })->all();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'label' => $this->label ?: Str::headline(Str::plural(class_basename($this->model))),
            'icon'  => admin_icon_class($this->icon),
        ];
    }

    /**
     * @param string|Closure $value
     * @param Model          $record
     *
     * @return mixed
     */
    protected function resolve($value, Model $record)
    {
        if ($value instanceof Closure) {
            return $value($record);
        }

        return data_get($record, $value);
    }

    /**
     * @param Model $record
     *
     * @return string
     */
    protected function resolveUrl(Model $record)
    {
        if ($this->url instanceof Closure) {
            return call_user_func($this->url, $record);
        }

        $pattern = $this->url ?: Str::kebab(Str::plural(class_basename($this->model))).'/{'.$record->getKeyName().'}/edit';

        $path = preg_replace_callback('/\{([\w.]+)\}/', function ($matches) use ($record) {
            return rawurlencode((string) data_get($record, $matches[1]));
        }, $pattern);

        return url()->isValidUrl($path) ? $path : admin_url($path);
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }
}
