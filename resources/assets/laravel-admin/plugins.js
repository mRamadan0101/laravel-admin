/*!
 * laravel-admin 2.x plugin layer.
 *
 * Keeps the jQuery plugin APIs used by laravel-admin 1.x (and its extensions)
 * working on top of Bootstrap 5 and the 2.x vendor stack:
 *
 *   $.fn.iCheck            -> native .form-check-input
 *   $.fn.bootstrapSwitch   -> .form-switch
 *   $.fn.datetimepicker    -> flatpickr (moment.js style formats)
 *   $.fn.bootstrapNumber   -> input-group with -/+ buttons
 *   $.fn.editable          -> Bootstrap 5 popover editor
 */
(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var locale = (document.documentElement.getAttribute('lang') || 'en').toLowerCase();

    /* ------------------------------------------------------------------
     * select2
     * ------------------------------------------------------------------ */
    if ($.fn.select2) {
        $.fn.select2.defaults.set('theme', 'bootstrap-5');
        $.fn.select2.defaults.set('width', 'style');
        if ($.fn.select2.amd && $.fn.select2.amd.require && $.fn.select2.amd.require.defined) {
            var select2Locales = [locale.replace('_', '-'), locale.split(/[-_]/)[0]];
            for (var l = 0; l < select2Locales.length; l++) {
                if ($.fn.select2.amd.require.defined('select2/i18n/' + select2Locales[l])) {
                    $.fn.select2.defaults.set('language', select2Locales[l]);
                    break;
                }
            }
        }
    }

    /* ------------------------------------------------------------------
     * iCheck
     * ------------------------------------------------------------------ */
    var ICHECK = 'icheck-shim';

    function icheckSync(input) {
        var $input = $(input);
        var prev = $input.data(ICHECK + '-state');
        var now = input.checked;

        if (prev === now) {
            return;
        }

        $input.data(ICHECK + '-state', now);
        $input.trigger(now ? 'ifChecked' : 'ifUnchecked').trigger('ifChanged').trigger('ifToggled');
    }

    function icheckSyncGroup(input) {
        if (input.type === 'radio' && input.name) {
            var scope = input.form || document;
            $(scope).find('input[type=radio]').filter(function () {
                return this.name === input.name && this !== input;
            }).each(function () {
                if ($(this).data(ICHECK)) {
                    icheckSync(this);
                }
            });
        }

        icheckSync(input);
    }

    $.fn.iCheck = function (options) {
        var method = typeof options === 'string' ? options : null;

        return this.each(function () {
            var input = this;
            var $input = $(input);

            if (!$input.is('input[type=checkbox],input[type=radio]')) {
                $input.find('input[type=checkbox],input[type=radio]').iCheck(options);
                return;
            }

            if (!$input.data(ICHECK)) {
                $input.data(ICHECK, true).data(ICHECK + '-state', input.checked).addClass('form-check-input');

                $input.on('click.' + ICHECK, function () {
                    input.checked = !input.checked;
                    $input.trigger('ifClicked');
                    input.checked = !input.checked;
                }).on('change.' + ICHECK, function () {
                    icheckSyncGroup(input);
                });
            }

            switch (method) {
                case 'check':
                    input.checked = true;
                    icheckSyncGroup(input);
                    break;
                case 'uncheck':
                    input.checked = false;
                    icheckSync(input);
                    break;
                case 'toggle':
                    input.checked = !input.checked;
                    icheckSyncGroup(input);
                    break;
                case 'disable':
                    input.disabled = true;
                    $input.trigger('ifDisabled');
                    break;
                case 'enable':
                    input.disabled = false;
                    $input.trigger('ifEnabled');
                    break;
                case 'update':
                    $input.data(ICHECK + '-state', input.checked);
                    break;
                case 'destroy':
                    $input.off('.' + ICHECK).removeData(ICHECK).removeData(ICHECK + '-state');
                    break;
            }
        });
    };

    /* ------------------------------------------------------------------
     * bootstrap-switch
     * ------------------------------------------------------------------ */
    var SWITCH = 'bootstrap-switch-shim';

    $.fn.bootstrapSwitch = function (options, value) {
        if (typeof options === 'string') {
            var method = options;
            var result;

            this.each(function () {
                var api = $(this).data(SWITCH);
                if (api && typeof api[method] === 'function') {
                    result = api[method](value);
                }
            });

            return result === undefined ? this : result;
        }

        var settings = $.extend({
            size: 'small',
            onText: 'ON',
            offText: 'OFF',
            onColor: 'primary',
            offColor: 'default',
            state: null,
            disabled: false,
            onSwitchChange: null,
            onInit: null
        }, options || {});

        return this.each(function () {
            var input = this;
            var $input = $(input);

            if ($input.data(SWITCH)) {
                return;
            }

            var $wrap = $('<div class="bootstrap-switch form-check form-switch"></div>')
                .addClass('bootstrap-switch-' + settings.size);
            var $label = $('<span class="bootstrap-switch-label"></span>');

            $input.addClass('form-check-input').attr('role', 'switch').wrap($wrap);
            $wrap = $input.parent();
            $wrap.append($label);

            if (settings.state !== null) {
                input.checked = !!settings.state;
            }

            if (settings.disabled) {
                input.disabled = true;
            }

            var render = function () {
                $label.text(input.checked ? settings.onText : settings.offText);
                $wrap.toggleClass('bootstrap-switch-on', input.checked)
                    .toggleClass('bootstrap-switch-off', !input.checked);
            };

            var api = {
                state: function (state) {
                    if (state === undefined) {
                        return input.checked;
                    }
                    if (input.checked !== !!state) {
                        input.checked = !!state;
                        $input.trigger('change');
                    }
                    return $input;
                },
                toggleState: function () {
                    return api.state(!input.checked);
                },
                disabled: function (disabled) {
                    if (disabled === undefined) {
                        return input.disabled;
                    }
                    input.disabled = !!disabled;
                    return $input;
                },
                destroy: function () {
                    $label.remove();
                    $input.unwrap().off('.' + SWITCH).removeData(SWITCH);
                }
            };

            $input.data(SWITCH, api);

            $input.on('change.' + SWITCH, function (event) {
                var state = input.checked;

                if (typeof settings.onSwitchChange === 'function') {
                    if (settings.onSwitchChange.call(input, event, state) === false) {
                        input.checked = !state;
                        render();
                        return;
                    }
                }

                render();
                $input.trigger('switchChange.bootstrapSwitch', [state]);
            });

            render();

            if (typeof settings.onInit === 'function') {
                settings.onInit.call(input, null, input.checked);
            }
        });
    };

    /* ------------------------------------------------------------------
     * bootstrap-datetimepicker (moment formats) -> flatpickr
     * ------------------------------------------------------------------ */
    var MOMENT_TOKENS = [
        ['YYYY', 'Y'], ['YY', 'y'],
        ['MMMM', 'F'], ['MMM', 'M'], ['MM', 'm'], ['M', 'n'],
        ['DDDD', 'z'], ['Do', 'J'], ['DD', 'd'], ['D', 'j'],
        ['dddd', 'l'], ['ddd', 'D'],
        ['HH', 'H'], ['H', 'H'], ['hh', 'G'], ['h', 'h'],
        ['mm', 'i'], ['m', 'i'],
        ['ss', 'S'], ['s', 's'],
        ['A', 'K'], ['a', 'K'],
        ['X', 'U'], ['ww', 'W'], ['WW', 'W']
    ];

    function momentToFlatpickr(format) {
        var out = '';
        var i = 0;

        while (i < format.length) {
            if (format[i] === '[') {
                var end = format.indexOf(']', i);
                end = end === -1 ? format.length : end;
                out += format.substring(i + 1, end).replace(/([a-zA-Z])/g, '\\$1');
                i = end + 1;
                continue;
            }

            var matched = false;
            for (var t = 0; t < MOMENT_TOKENS.length; t++) {
                var token = MOMENT_TOKENS[t][0];
                if (format.substr(i, token.length) === token) {
                    out += MOMENT_TOKENS[t][1];
                    i += token.length;
                    matched = true;
                    break;
                }
            }

            if (!matched) {
                out += /[a-zA-Z]/.test(format[i]) ? '\\' + format[i] : format[i];
                i++;
            }
        }

        return out;
    }

    function flatpickrLocale(name) {
        if (!window.flatpickr || !window.flatpickr.l10ns) {
            return undefined;
        }

        name = (name || locale).toLowerCase().replace('_', '-');

        var candidates = [name, name.split('-')[0]];
        if (name === 'zh-tw' || name === 'zh-hk') {
            candidates.unshift('zh_tw');
        }

        for (var i = 0; i < candidates.length; i++) {
            if (window.flatpickr.l10ns[candidates[i]]) {
                return candidates[i];
            }
        }

        return undefined;
    }

    function DateValue(date, format) {
        this._d = date;
        this._f = format;
    }

    DateValue.prototype.format = function (format) {
        if (!this._d) {
            return '';
        }
        return window.flatpickr.formatDate(this._d, momentToFlatpickr(format || this._f));
    };
    DateValue.prototype.toDate = function () {
        return this._d;
    };
    DateValue.prototype.valueOf = function () {
        return this._d ? this._d.getTime() : NaN;
    };
    DateValue.prototype.isValid = function () {
        return !!this._d;
    };
    DateValue.prototype.toString = function () {
        return this.format();
    };

    function toDate(value, fpFormat) {
        if (value === false || value === null || value === undefined || value === '') {
            return null;
        }
        if (value instanceof DateValue) {
            return value.toDate();
        }
        if (value instanceof Date) {
            return value;
        }
        if (typeof value === 'object' && typeof value.toDate === 'function') {
            return value.toDate();
        }
        return window.flatpickr.parseDate(String(value), fpFormat) || null;
    }

    var DTP = 'DateTimePicker';

    $.fn.datetimepicker = function (options) {
        if (!window.flatpickr) {
            return this;
        }

        if (typeof options === 'string') {
            var args = Array.prototype.slice.call(arguments, 1);
            return this.each(function () {
                var api = $(this).data(DTP);
                if (api && typeof api[options] === 'function') {
                    api[options].apply(api, args);
                }
            });
        }

        options = $.extend({format: 'YYYY-MM-DD'}, options || {});

        return this.each(function () {
            var $el = $(this);

            if ($el.data(DTP)) {
                return;
            }

            var $input = $el.is('input') ? $el : $el.find('input').first();
            var inline = !!options.inline;

            if (!$input.length) {
                $input = $('<input type="hidden">').appendTo($el);
            }

            var format = options.format;
            var fpFormat = momentToFlatpickr(format);
            var hasDate = /[YMDdw]/.test(format.replace(/\[[^\]]*]/g, ''));
            var hasTime = /[Hhms]/.test(format.replace(/\[[^\]]*]/g, ''));
            var monthOnly = hasDate && !/[Dd]/.test(format.replace(/\[[^\]]*]/g, ''));

            var config = {
                dateFormat: fpFormat,
                allowInput: true,
                disableMobile: true,
                enableTime: hasTime,
                noCalendar: hasTime && !hasDate,
                enableSeconds: /s/.test(format.replace(/\[[^\]]*]/g, '')),
                time_24hr: !/[hA a]/.test(format.replace(/\[[^\]]*]/g, '').replace(/\s/g, '')),
                inline: inline,
                locale: flatpickrLocale(options.locale),
                minDate: toDate(options.minDate, fpFormat) || undefined,
                maxDate: toDate(options.maxDate, fpFormat) || undefined,
                defaultDate: toDate(options.date || options.defaultDate, fpFormat) || undefined,
                plugins: []
            };

            if (monthOnly && window.monthSelectPlugin) {
                config.plugins.push(new window.monthSelectPlugin({
                    shorthand: true,
                    dateFormat: fpFormat,
                    altFormat: fpFormat
                }));
            }

            if (options.daysOfWeekDisabled && options.daysOfWeekDisabled.length) {
                config.disable = [function (date) {
                    return options.daysOfWeekDisabled.indexOf(date.getDay()) !== -1;
                }];
            }

            if (options.disabledDates && options.disabledDates.length) {
                config.disable = (config.disable || []).concat(options.disabledDates);
            }

            if (options.enabledDates && options.enabledDates.length) {
                config.enable = options.enabledDates;
            }

            if (options.flatpickr) {
                $.extend(config, options.flatpickr);
            }

            var target = inline && !$el.is('input') ? $el[0] : $input[0];

            if (inline && !$el.is('input')) {
                config.appendTo = $el[0];
            }

            config.onChange = function (dates) {
                var date = dates.length ? dates[dates.length - 1] : null;
                if (inline && $input[0] !== target) {
                    $input.val(date ? window.flatpickr.formatDate(date, fpFormat) : '');
                }
                var event = $.Event('dp.change', {
                    date: date ? new DateValue(date, format) : false,
                    oldDate: api._last ? new DateValue(api._last, format) : null
                });
                api._last = date;
                $el.trigger(event);
            };
            config.onOpen = function () {
                $el.trigger('dp.show');
            };
            config.onClose = function () {
                $el.trigger('dp.hide');
            };

            var fp = window.flatpickr(inline && !$el.is('input') ? $input[0] : target, config);

            var api = {
                _fp: fp,
                _last: fp.selectedDates[0] || null,
                date: function (value) {
                    if (value === undefined) {
                        return fp.selectedDates[0] ? new DateValue(fp.selectedDates[0], format) : null;
                    }
                    fp.setDate(toDate(value, fpFormat), true);
                    return api;
                },
                minDate: function (value) {
                    fp.set('minDate', toDate(value, fpFormat) || null);
                    return api;
                },
                maxDate: function (value) {
                    fp.set('maxDate', toDate(value, fpFormat) || null);
                    return api;
                },
                format: function (value) {
                    if (value === undefined) {
                        return format;
                    }
                    format = value;
                    fpFormat = momentToFlatpickr(value);
                    fp.set('dateFormat', fpFormat);
                    return api;
                },
                locale: function (value) {
                    var name = flatpickrLocale(value);
                    if (name) {
                        fp.set('locale', name);
                    }
                    return api;
                },
                clear: function () {
                    fp.clear();
                    return api;
                },
                show: function () {
                    fp.open();
                    return api;
                },
                hide: function () {
                    fp.close();
                    return api;
                },
                toggle: function () {
                    fp.toggle();
                    return api;
                },
                disable: function () {
                    $input.prop('disabled', true);
                    return api;
                },
                enable: function () {
                    $input.prop('disabled', false);
                    return api;
                },
                destroy: function () {
                    fp.destroy();
                    $el.removeData(DTP);
                }
            };

            $el.data(DTP, api);

            if (!$el.is('input')) {
                $input.data(DTP, api);

                if (options.allowInputToggle !== false) {
                    $el.find('.input-group-text').css('cursor', 'pointer').on('click', function () {
                        fp.open();
                    });
                }
            }
        });
    };

    /* ------------------------------------------------------------------
     * bootstrap-number-input
     * ------------------------------------------------------------------ */
    $.fn.bootstrapNumber = function (options) {
        var settings = $.extend({
            upClass: 'success',
            downClass: 'primary',
            upText: '+',
            downText: '-',
            center: true
        }, options || {});

        return this.each(function () {
            var $input = $(this);

            if ($input.data('bootstrap-number')) {
                return;
            }

            $input.data('bootstrap-number', true);

            var $group = $input.parent('.input-group');

            if (!$group.length) {
                $input.wrap('<div class="input-group"></div>');
                $group = $input.parent();
            }

            $group.addClass('input-group-number');

            if (settings.center) {
                $input.addClass('text-center');
            }

            var step = function (direction) {
                if ($input.prop('disabled') || $input.prop('readonly')) {
                    return;
                }

                var min = parseFloat($input.attr('min'));
                var max = parseFloat($input.attr('max'));
                var by = parseFloat($input.attr('step')) || 1;
                var value = parseFloat($input.val()) || 0;
                var next = value + direction * by;
                var decimals = (String(by).split('.')[1] || '').length;

                if (!isNaN(min) && next < min) {
                    next = min;
                }
                if (!isNaN(max) && next > max) {
                    next = max;
                }

                $input.val(decimals ? next.toFixed(decimals) : next).trigger('change');
            };

            var $down = $('<button type="button" class="btn btn-' + settings.downClass + '"></button>').text(settings.downText);
            var $up = $('<button type="button" class="btn btn-' + settings.upClass + '"></button>').text(settings.upText);

            $down.on('click', function () { step(-1); });
            $up.on('click', function () { step(1); });

            $input.before($down).after($up);
        });
    };

    /* ------------------------------------------------------------------
     * x-editable
     * ------------------------------------------------------------------ */
    var EDITABLE = 'editable-shim';

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
    }

    function editableSource(source) {
        if (typeof source === 'string') {
            try {
                source = JSON.parse(source);
            } catch (e) {
                source = [];
            }
        }

        if ($.isArray(source)) {
            return source;
        }

        return $.map(source || {}, function (text, value) {
            return {value: value, text: text};
        });
    }

    function editableDisplay($el, type, value, source) {
        if (type === 'select') {
            var text = '';
            $.each(source, function (_, item) {
                if (String(item.value) === String(value)) {
                    text = item.text;
                }
            });
            value = text;
        }

        if (value === '' || value === null || value === undefined) {
            $el.addClass('editable-empty').html($el.data(EDITABLE).options.emptytext);
        } else {
            $el.removeClass('editable-empty').text(value);
        }
    }

    $.fn.editable = function (options) {
        if (typeof options === 'string') {
            var method = options;
            return this.each(function () {
                var state = $(this).data(EDITABLE);
                if (state && method === 'destroy') {
                    var instance = bootstrap.Popover.getInstance(this);
                    if (instance) {
                        instance.dispose();
                    }
                    $(this).off('.' + EDITABLE).removeData(EDITABLE);
                }
            });
        }

        var defaults = $.extend({
            type: 'text',
            emptytext: 'Empty',
            name: null,
            pk: null,
            url: null,
            source: [],
            placement: 'top',
            params: null,
            success: null,
            error: null
        }, $.fn.editable.defaults);

        return this.each(function () {
            var el = this;
            var $el = $(el);
            var opts = $.extend({}, defaults, options || {}, {
                type: $el.data('type') || (options && options.type) || defaults.type,
                pk: $el.data('pk') !== undefined ? $el.data('pk') : (options && options.pk),
                url: $el.data('url') || (options && options.url),
                name: $el.data('name') || (options && options.name)
            });

            if ($el.attr('data-source')) {
                opts.source = $el.attr('data-source');
            }

            var source = editableSource(opts.source);
            var value = $el.attr('data-value');

            if (value === undefined) {
                value = $el.text();
            }

            $el.addClass('grid-editable').data(EDITABLE, {options: opts, value: value});

            editableDisplay($el, opts.type, value, source);

            var buildForm = function () {
                var current = $el.data(EDITABLE).value;
                var field;

                switch (opts.type) {
                    case 'textarea':
                        field = '<textarea class="form-control form-control-sm editable-input" rows="3">' + escapeHtml(current) + '</textarea>';
                        break;
                    case 'select':
                        field = '<select class="form-select form-select-sm editable-input">' + $.map(source, function (item) {
                            return '<option value="' + escapeHtml(item.value) + '"' + (String(item.value) === String(current) ? ' selected' : '') + '>' + escapeHtml(item.text) + '</option>';
                        }).join('') + '</select>';
                        break;
                    default:
                        field = '<input type="text" class="form-control form-control-sm editable-input" value="' + escapeHtml(current) + '">';
                }

                return '<form class="editable-form">' +
                    '<div class="d-flex gap-1">' + field +
                    '<button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-check"></i></button>' +
                    '<button type="button" class="btn btn-outline-secondary btn-sm editable-cancel"><i class="fa-solid fa-xmark"></i></button>' +
                    '</div><div class="editable-error"></div></form>';
            };

            var popover = new bootstrap.Popover(el, {
                html: true,
                sanitize: false,
                trigger: 'manual',
                container: 'body',
                placement: opts.placement,
                customClass: 'grid-editable-popover',
                content: buildForm
            });

            $el.on('click.' + EDITABLE, function (event) {
                event.preventDefault();
                event.stopPropagation();

                $('.grid-editable').not(el).each(function () {
                    var other = bootstrap.Popover.getInstance(this);
                    if (other) {
                        other.hide();
                    }
                });

                popover.toggle();
            });

            el.addEventListener('shown.bs.popover', function () {
                var $tip = $(popover.tip);
                var $field = $tip.find('.editable-input');

                if (opts.type === 'combodate' && $.fn.datetimepicker) {
                    $field.datetimepicker({format: opts.format || 'YYYY-MM-DD', locale: locale});
                }

                $field.trigger('focus');

                $tip.find('.editable-cancel').on('click', function () {
                    popover.hide();
                });

                $tip.find('form').on('submit', function (event) {
                    event.preventDefault();

                    var newValue = $field.val();
                    var params = {name: opts.name, value: newValue, pk: opts.pk};

                    if (typeof opts.params === 'function') {
                        params = opts.params.call(el, params);
                    } else if (opts.params) {
                        params = $.extend(params, opts.params);
                    }

                    $.ajax({url: opts.url, type: 'POST', data: params}).done(function (response) {
                        var message = typeof opts.success === 'function' ? opts.success.call(el, response, newValue) : null;

                        if (typeof message === 'string') {
                            $tip.find('.editable-error').text(message);
                            return;
                        }

                        $el.data(EDITABLE).value = newValue;
                        $el.attr('data-value', newValue);
                        editableDisplay($el, opts.type, newValue, source);
                        $el.trigger('save', [{newValue: newValue, response: response}]);
                        popover.hide();
                    }).fail(function (xhr) {
                        var message = typeof opts.error === 'function' ? opts.error.call(el, xhr, newValue) : xhr.statusText;
                        $tip.find('.editable-error').text(message || '');
                    });
                });
            });
        });
    };

    $.fn.editable.defaults = {
        emptytext: '<i class="fa-solid fa-pencil"></i>',
        params: function (params) {
            params._token = window.LA ? LA.token : $('meta[name="csrf-token"]').attr('content');
            params._editable = 1;
            params._method = 'PUT';
            return params;
        },
        error: function (xhr) {
            var msg = '';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                $.each(xhr.responseJSON.errors, function (k, v) {
                    msg += v + "\n";
                });
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            return msg;
        }
    };

    $(document).on('click', function (event) {
        if ($(event.target).closest('.grid-editable-popover, .grid-editable, .flatpickr-calendar').length) {
            return;
        }
        $('.grid-editable').each(function () {
            var instance = bootstrap.Popover.getInstance(this);
            if (instance) {
                instance.hide();
            }
        });
    });

    /* ------------------------------------------------------------------
     * misc plugin defaults
     * ------------------------------------------------------------------ */
    if ($.fn.fileinput) {
        $.fn.fileinputBsVersion = '5';
        $.fn.fileinput.defaults = $.extend($.fn.fileinput.defaults || {}, {theme: 'fa6'});
    }

})(window.jQuery);
