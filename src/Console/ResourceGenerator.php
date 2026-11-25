<?php

namespace Encore\Admin\Console;

use Illuminate\Database\Eloquent\Model;

class ResourceGenerator
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $formats = [
        'form_field'  => "\$form->%s('%s', __('%s'))",
        'show_field'  => "\$show->field('%s', __('%s'))",
        'grid_column' => "\$grid->column('%s', __('%s'))",
    ];

    /**
     * @var array
     */
    private $doctrineTypeMapping = [
        'string' => [
            'enum', 'geometry', 'geometrycollection', 'linestring',
            'polygon', 'multilinestring', 'multipoint', 'multipolygon',
            'point',
        ],
    ];

    /**
     * @var array
     */
    protected $fieldTypeMapping = [
        'ip'       => 'ip',
        'email'    => 'email|mail',
        'password' => 'password|pwd',
        'url'      => 'url|link|src|href',
        'mobile'   => 'mobile|phone',
        'color'    => 'color|rgb',
        'image'    => 'image|img|avatar|pic|picture|cover',
        'file'     => 'file|attachment',
    ];

    /**
     * ResourceGenerator constructor.
     *
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->model = $this->getModel($model);
    }

    /**
     * @param mixed $model
     *
     * @return mixed
     */
    protected function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (!class_exists($model) || !is_string($model) || !is_subclass_of($model, Model::class)) {
            throw new \InvalidArgumentException("Invalid model [$model] !");
        }

        return new $model();
    }

    /**
     * @return string
     */
    public function generateForm()
    {
        $reservedColumns = $this->getReservedColumns();

        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column['name'];
            if (in_array($name, $reservedColumns)) {
                continue;
            }
            $type = $column['type'];
            $default = $column['default'];

            $defaultValue = '';

            // set column fieldType and defaultValue
            switch ($type) {
                case 'boolean':
                case 'bool':
                    $fieldType = 'switch';
                    break;
                case 'json':
                case 'array':
                case 'object':
                    $fieldType = 'text';
                    break;
                case 'string':
                    $fieldType = 'text';
                    foreach ($this->fieldTypeMapping as $type => $regex) {
                        if (preg_match("/^($regex)$/i", $name) !== 0) {
                            $fieldType = $type;
                            break;
                        }
                    }
                    $defaultValue = "'{$default}'";
                    break;
                case 'integer':
                case 'bigint':
                case 'smallint':
                case 'timestamp':
                    $fieldType = 'number';
                    break;
                case 'decimal':
                case 'float':
                case 'real':
                    $fieldType = 'decimal';
                    break;
                case 'datetime':
                    $fieldType = 'datetime';
                    $defaultValue = "date('Y-m-d H:i:s')";
                    break;
                case 'date':
                    $fieldType = 'date';
                    $defaultValue = "date('Y-m-d')";
                    break;
                case 'time':
                    $fieldType = 'time';
                    $defaultValue = "date('H:i:s')";
                    break;
                case 'text':
                case 'blob':
                    $fieldType = 'textarea';
                    break;
                default:
                    $fieldType = 'text';
                    $defaultValue = "'{$default}'";
            }

            $defaultValue = $defaultValue ?: $default;

            $label = $this->formatLabel($name);

            $output .= sprintf($this->formats['form_field'], $fieldType, $name, $label);

            if (trim($defaultValue, "'\"")) {
                $output .= "->default({$defaultValue})";
            }

            $output .= ";\r\n";
        }

        return $output;
    }

    public function generateShow()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column['name'];

            // set column label
            $label = $this->formatLabel($name);

            $output .= sprintf($this->formats['show_field'], $name, $label);

            $output .= ";\r\n";
        }

        return $output;
    }

    public function generateGrid()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column['name'];
            $label = $this->formatLabel($name);

            $output .= sprintf($this->formats['grid_column'], $name, $label);
            $output .= ";\r\n";
        }

        return $output;
    }

    protected function getReservedColumns()
    {
        return [
            $this->model->getKeyName(),
            $this->model->getCreatedAtColumn(),
            $this->model->getUpdatedAtColumn(),
            'deleted_at',
        ];
    }

    /**
     * Get columns of a giving model.
     *
     * @throws \Exception
     *
     * @return array<int, array{name: string, type: string, default: mixed}>
     */
    protected function getTableColumns()
    {
        $schema = $this->model->getConnection()->getSchemaBuilder();

        if (!method_exists($schema, 'getColumns')) {
            return $this->getDoctrineTableColumns();
        }

        return array_map(function (array $column) {
            return [
                'name'    => $column['name'],
                'type'    => $this->normalizeColumnType($column['type_name'], $column['type']),
                'default' => $this->normalizeColumnDefault($column['default']),
            ];
        }, $schema->getColumns($this->model->getTable()));
    }

    /**
     * Map a native database type to the generic type names used by generateForm().
     *
     * @param string $typeName
     * @param string $fullType
     *
     * @return string
     */
    protected function normalizeColumnType($typeName, $fullType)
    {
        $typeName = strtolower($typeName);

        if ($typeName === 'tinyint' && str_starts_with(strtolower($fullType), 'tinyint(1)')) {
            return 'boolean';
        }

        $map = [
            'boolean'  => ['bool', 'boolean', 'bit'],
            'integer'  => ['int', 'integer', 'int2', 'int4', 'int8', 'mediumint', 'tinyint', 'serial', 'bigserial', 'year'],
            'bigint'   => ['bigint'],
            'smallint' => ['smallint', 'smallserial'],
            'decimal'  => ['decimal', 'numeric', 'money'],
            'float'    => ['float', 'float4', 'float8', 'double', 'double precision', 'real'],
            'datetime' => ['datetime', 'datetime2', 'timestamp', 'timestamptz', 'datetimeoffset', 'smalldatetime'],
            'date'     => ['date'],
            'time'     => ['time', 'timetz'],
            'text'     => ['text', 'tinytext', 'mediumtext', 'longtext', 'ntext', 'clob'],
            'blob'     => ['blob', 'tinyblob', 'mediumblob', 'longblob', 'bytea', 'binary', 'varbinary', 'image'],
            'json'     => ['json', 'jsonb'],
        ];

        foreach ($map as $type => $names) {
            if (in_array($typeName, $names, true)) {
                return $type;
            }
        }

        return 'string';
    }

    /**
     * Strip quoting and casts that some drivers add to column defaults, e.g. 'foo'::character varying.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    protected function normalizeColumnDefault($default)
    {
        if (!is_string($default)) {
            return $default;
        }

        if (preg_match("/^\(?'(.*)'\)?(::[\w\s]+)?$/s", $default, $matches)) {
            return str_replace("''", "'", $matches[1]);
        }

        if (strtoupper($default) === 'NULL') {
            return null;
        }

        return $default;
    }

    /**
     * Fallback for Laravel versions without Schema::getColumns() (before 10.30), requires doctrine/dbal.
     *
     * @throws \Exception
     *
     * @return array<int, array{name: string, type: string, default: mixed}>
     */
    protected function getDoctrineTableColumns()
    {
        if (!$this->model->getConnection()->isDoctrineAvailable()) {
            throw new \Exception(
                'You need to require doctrine/dbal in your own composer.json to get database columns, or upgrade to Laravel 10.30+.'
            );
        }

        $table = $this->model->getConnection()->getTablePrefix().$this->model->getTable();
        /** @var \Doctrine\DBAL\Schema\MySqlSchemaManager $schema */
        $schema = $this->model->getConnection()->getDoctrineSchemaManager($table);

        // custom mapping the types that doctrine/dbal does not support
        $databasePlatform = $schema->getDatabasePlatform();

        foreach ($this->doctrineTypeMapping as $doctrineType => $dbTypes) {
            foreach ($dbTypes as $dbType) {
                $databasePlatform->registerDoctrineTypeMapping($dbType, $doctrineType);
            }
        }

        $database = null;
        if (strpos($table, '.')) {
            list($database, $table) = explode('.', $table);
        }

        return array_values(array_map(function ($column) {
            return [
                'name'    => $column->getName(),
                'type'    => $column->getType()->getName(),
                'default' => $column->getDefault(),
            ];
        }, $schema->listTableColumns($table, $database)));
    }

    /**
     * Format label.
     *
     * @param string $value
     *
     * @return string
     */
    protected function formatLabel($value)
    {
        return ucfirst(str_replace(['-', '_'], ' ', $value));
    }
}
