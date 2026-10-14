<?php

namespace Encore\Admin\Form\Field;

class DateMultiple extends Text
{
    protected static $css = [
        '/vendor/laravel-admin/flatpickr/dist/flatpickr.min.css',
        '/vendor/laravel-admin/flatpickr/dist/shortcut-buttons-flatpickr/themes/light.min.css',
    ];

    protected static $js = [
        '/vendor/laravel-admin/flatpickr/dist/flatpickr.min.js',
        '/vendor/laravel-admin/flatpickr/dist/shortcut-buttons-flatpickr/shortcut-buttons-flatpickr.min.js',
    ];

    protected $format = 'YYYY-MM-DD';

    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    public function prepare($value)
    {
        if ($value === '') {
            $value = null;
        }

        return $value;
    }

    protected function clearLabel()
    {
        return addslashes(trans('admin.reset'));
    }

    public function render()
    {
        $this->options['format'] = $this->format;
        $this->options['locale'] = array_key_exists('locale', $this->options) ? $this->options['locale'] : config('app.locale');
        $this->options['allowInputToggle'] = true;

        $options = json_encode([
            'mode'       => 'multiple',
            'dateFormat' => 'Y-m-d',
            'locale'     => $this->options['locale'],
        ]);

        $this->script = <<<SCRIPT
(function () {
    var options = {$options};
    var locale = String(options.locale || '').toLowerCase().replace('_', '-');
    if (!flatpickr.l10ns[locale]) {
        locale = locale.split('-')[0];
    }
    options.locale = flatpickr.l10ns[locale] ? locale : 'default';
    options.plugins = [
        ShortcutButtonsPlugin({
            button: {label: '{$this->clearLabel()}'},
            onClick: function (index, fp) {
                fp.clear();
                fp.close();
            }
        })
    ];
    $('{$this->getElementClassSelector()}').flatpickr(options);
})();
SCRIPT;

        $this->prepend('<i class="fa-regular fa-calendar fa-fw"></i>');

        return parent::render();
    }
}
