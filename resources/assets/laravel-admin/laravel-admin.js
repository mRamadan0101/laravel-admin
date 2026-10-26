/*!
 * laravel-admin 2.x
 */

toastr.options = {
    closeButton: true,
    progressBar: true,
    showMethod: 'slideDown',
    timeOut: 4000
};

$.pjax.defaults.timeout = 5000;
$.pjax.defaults.maxCacheLength = 0;
$(document).pjax('a:not(a[target="_blank"]):not([data-bs-toggle]):not([data-lte-toggle]):not([data-pjax="false"]):not([href^="#"]):not([href^="javascript:"])', {
    container: '#pjax-container'
});

NProgress.configure({parent: '#app'});

$(document).on('pjax:timeout', function (event) {
    event.preventDefault();
});

$(document).on('submit', 'form[pjax-container]', function (event) {
    $.pjax.submit(event, '#pjax-container');
});

$(document).on('pjax:popstate', function () {
    $(document).one('pjax:end', function (event) {
        $(event.target).find('script[data-exec-on-popstate]').each(function () {
            $.globalEval(this.text || this.textContent || this.innerHTML || '');
        });
    });
});

$(document).on('pjax:send', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        $('form[pjax-container] :submit').button('loading');
    }
    NProgress.start();
});

$(document).on('pjax:complete', function (xhr) {
    if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
        $('form[pjax-container] :submit').button('reset');
    }
    NProgress.done();
    $.admin.grid.selects = {};
});

$(document).on('pjax:beforeReplace', function () {
    $.admin.disposeBootstrap(document.getElementById('pjax-container'));
});

$(document).on('pjax:end', function () {
    $.admin.initBootstrap(document.getElementById('pjax-container'));
    $.admin.activateMenu();
});

/* ---------------------------------------------------------------------
 * Bootstrap 3 style $.fn.button('loading' | 'reset')
 * Bootstrap 5 registers its own jQuery bridge on DOMContentLoaded,
 * so the wrapper has to be installed afterwards.
 * ------------------------------------------------------------------- */
(function ($) {
    function install() {
        var bridge = $.fn.button;

        if (bridge && bridge.laravelAdmin) {
            return;
        }

        var button = function (action) {
            if (action === 'loading' || action === 'reset') {
                return this.each(function () {
                    var $btn = $(this);
                    var isInput = $btn.is('input');

                    if (action === 'loading') {
                        if ($btn.data('reset-html') === undefined) {
                            $btn.data('reset-html', isInput ? $btn.val() : $btn.html());
                        }
                        var text = $btn.data('loading-text') || $btn.data('bs-loading-text');
                        if (text) {
                            isInput ? $btn.val(text) : $btn.html(text);
                        } else if (!isInput) {
                            $btn.prepend('<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>');
                        }
                        $btn.addClass('disabled').prop('disabled', true);
                    } else {
                        if ($btn.data('reset-html') !== undefined) {
                            isInput ? $btn.val($btn.data('reset-html')) : $btn.html($btn.data('reset-html'));
                            $btn.removeData('reset-html');
                        }
                        $btn.removeClass('disabled').prop('disabled', false);
                    }
                });
            }

            return bridge ? bridge.apply(this, arguments) : this;
        };

        button.laravelAdmin = true;
        button.Constructor = bridge && bridge.Constructor;
        button.noConflict = bridge && bridge.noConflict;
        $.fn.button = button;
    }

    install();
    document.addEventListener('DOMContentLoaded', install);
})(jQuery);

/* ---------------------------------------------------------------------
 * Color mode
 * ------------------------------------------------------------------- */
(function ($) {
    var KEY = 'laravel-admin.theme';
    var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function preferred() {
        var stored = null;
        try {
            stored = localStorage.getItem(KEY);
        } catch (e) {}
        return stored || document.documentElement.getAttribute('data-admin-theme') || 'light';
    }

    function apply(theme) {
        var resolved = theme === 'auto' ? (media && media.matches ? 'dark' : 'light') : theme;
        document.documentElement.setAttribute('data-bs-theme', resolved);

        $('.admin-theme-switcher [data-admin-theme-value]').each(function () {
            $(this).toggleClass('active', $(this).data('admin-theme-value') === theme);
        });

        $('.admin-theme-icon')
            .toggleClass('fa-sun', resolved === 'light' && theme !== 'auto')
            .toggleClass('fa-moon', resolved === 'dark' && theme !== 'auto')
            .toggleClass('fa-circle-half-stroke', theme === 'auto');
    }

    $(document).on('click', '[data-admin-theme-value]', function () {
        var theme = $(this).data('admin-theme-value');
        try {
            localStorage.setItem(KEY, theme);
        } catch (e) {}
        apply(theme);
    });

    if (media && media.addEventListener) {
        media.addEventListener('change', function () {
            if (preferred() === 'auto') {
                apply('auto');
            }
        });
    }

    $(function () {
        apply(preferred());
    });
})(jQuery);

/* ---------------------------------------------------------------------
 * Sidebar
 * ------------------------------------------------------------------- */
$(document).on('click', function () {
    $('.sidebar-search-form .dropdown-menu').removeClass('show');
});

$(function () {
    $.admin.activateMenu();
    $.admin.initBootstrap(document);

    var wrapper = document.querySelector('.app-sidebar .sidebar-wrapper');
    if (wrapper && window.OverlayScrollbarsGlobal && window.OverlayScrollbarsGlobal.OverlayScrollbars) {
        window.OverlayScrollbarsGlobal.OverlayScrollbars(wrapper, {
            scrollbars: {
                theme: 'os-theme-light',
                autoHide: 'leave',
                clickScroll: true
            }
        });
    }

    $(document).on('click', '.app-sidebar .sidebar-menu .nav-link:not([href="#"])', function () {
        var $item = $(this).closest('.nav-item');
        $('.app-sidebar .sidebar-menu .nav-link.active').removeClass('active');
        $(this).addClass('active');
        $item.parents('.nav-item').children('.nav-link').addClass('active');
    });

    // Sidebar menu search
    $('.sidebar-search-form .autocomplete').on('keyup focus', function () {
        var $menu = $('.sidebar-search-form .dropdown-menu');
        var text = $(this).val();

        if (text === '') {
            $menu.removeClass('show');
            return;
        }

        var regex = new RegExp(text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i');
        var matched = false;

        $menu.find('li').each(function () {
            if (!regex.test($(this).find('a').text())) {
                $(this).hide();
            } else {
                $(this).show();
                matched = true;
            }
        });

        $menu.toggleClass('show', matched);
    }).on('click', function (event) {
        event.stopPropagation();
    });

    $(document).on('click', '.sidebar-search-form .dropdown-menu li a', function () {
        $('.sidebar-search-form .autocomplete').val($(this).text().trim());
    });
});

/* ---------------------------------------------------------------------
 * Back to top
 * ------------------------------------------------------------------- */
$(window).on('scroll', function () {
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        $('#totop').fadeIn(500);
    } else {
        $('#totop').fadeOut(500);
    }
});

$(document).on('click', '#totop', function (e) {
    e.preventDefault();
    $('html,body').animate({scrollTop: 0}, 500);
});

/* ---------------------------------------------------------------------
 * $.admin
 * ------------------------------------------------------------------- */
(function ($) {

    var Grid = function () {
        this.selects = {};
    };

    Grid.prototype.select = function (id) {
        this.selects[id] = id;
    };

    Grid.prototype.unselect = function (id) {
        delete this.selects[id];
    };

    Grid.prototype.selected = function () {
        var rows = [];
        $.each(this.selects, function (key, val) {
            rows.push(key);
        });

        return rows;
    };

    /**
     * SweetAlert2 11 with the option names used by SweetAlert2 7 (laravel-admin 1.x).
     */
    var swal = function (title, text, icon) {
        var options = title;

        if (typeof title !== 'object' || title === null) {
            options = {title: title, text: text, icon: icon};
        } else {
            options = $.extend({}, title);
        }

        if (options.type && !options.icon) {
            options.icon = options.type;
        }
        delete options.type;

        if (options.icon === undefined) {
            delete options.icon;
        }

        if (options.confirmButtonClass || options.cancelButtonClass) {
            options.customClass = $.extend({}, options.customClass, {
                confirmButton: options.confirmButtonClass,
                cancelButton: options.cancelButtonClass
            });
            delete options.confirmButtonClass;
            delete options.cancelButtonClass;
        }

        return Swal.fire(options);
    };

    $.each(['close', 'isVisible', 'showLoading', 'hideLoading', 'isLoading', 'clickConfirm', 'clickCancel',
        'getPopup', 'getTitle', 'getContent', 'getHtmlContainer', 'getInput', 'showValidationMessage',
        'resetValidationMessage', 'enableButtons', 'disableButtons', 'mixin', 'update'], function (_, method) {
        if (typeof Swal[method] === 'function') {
            swal[method] = function () {
                return Swal[method].apply(Swal, arguments);
            };
        }
    });

    if (!swal.getContent && Swal.getHtmlContainer) {
        swal.getContent = function () {
            return Swal.getHtmlContainer();
        };
    }

    swal.fire = swal;
    swal.DismissReason = Swal.DismissReason;
    swal.showValidationError = swal.showValidationMessage;
    swal.resetValidationError = swal.resetValidationMessage;

    if (typeof window.swal === 'undefined') {
        window.swal = swal;
    }

    $.fn.admin = LA;
    $.admin = LA;
    $.admin.swal = swal;
    $.admin.toastr = toastr;
    $.admin.grid = new Grid();

    $.admin.reload = function () {
        $.pjax.reload('#pjax-container');
        $.admin.grid = new Grid();
    };

    $.admin.redirect = function (url) {
        $.pjax({container: '#pjax-container', url: url});
        $.admin.grid = new Grid();
    };

    $.admin.getToken = function () {
        return $('meta[name="csrf-token"]').attr('content');
    };

    $.admin.initBootstrap = function (root) {
        if (!window.bootstrap || !root) {
            return;
        }

        $(root).find('[data-bs-toggle="tooltip"]').each(function () {
            bootstrap.Tooltip.getOrCreateInstance(this);
        });

        $(root).find('[data-bs-toggle="popover"]').not('[class^="ie-trigger"],[class*=" ie-trigger"]').each(function () {
            if (!bootstrap.Popover.getInstance(this)) {
                bootstrap.Popover.getOrCreateInstance(this);
            }
        });
    };

    $.admin.disposeBootstrap = function (root) {
        if (!window.bootstrap || !root) {
            return;
        }

        $(root).find('[data-bs-toggle="tooltip"],[data-bs-toggle="popover"]').each(function () {
            var tooltip = bootstrap.Tooltip.getInstance(this);
            var popover = bootstrap.Popover.getInstance(this);
            tooltip && tooltip.dispose();
            popover && popover.dispose();
        });

        $('body > .modal-backdrop').remove();
        $('body').removeClass('modal-open').css({overflow: '', paddingRight: ''});
    };

    $.admin.activateMenu = function () {
        var $menu = $('.app-sidebar .sidebar-menu');

        if (!$menu.length) {
            return;
        }

        var path = location.pathname + location.search + location.hash;
        var best = null;
        var bestLength = 0;

        $menu.find('a.nav-link[href]').each(function () {
            var href = this.getAttribute('href');

            if (!href || href === '#') {
                return;
            }

            var link = document.createElement('a');
            link.href = href;

            if (link.host !== location.host) {
                return;
            }

            var target = link.pathname + link.search + link.hash;
            var exact = target === path;
            var prefix = path.indexOf(link.pathname.replace(/\/$/, '') + '/') === 0;

            if ((exact || prefix) && link.pathname.length > bestLength) {
                best = this;
                bestLength = exact ? Infinity : link.pathname.length;
            }
        });

        $menu.find('.nav-link.active').removeClass('active');

        if (best) {
            var $best = $(best).addClass('active');
            $best.parents('.nav-item').each(function () {
                var $item = $(this);
                if ($item.children('.nav-treeview').length) {
                    $item.addClass('menu-open').children('.nav-link').addClass('active');
                    $item.children('.nav-treeview').css('display', 'block');
                }
            });
        }
    };

    $.admin.loadedScripts = [];

    $.admin.loadScripts = function (arr) {
        var _arr = $.map(arr, function (src) {

            if ($.inArray(src, $.admin.loadedScripts) !== -1) {
                return;
            }

            $.admin.loadedScripts.push(src);

            return $.getScript(src);
        });

        _arr.push($.Deferred(function (deferred) {
            $(deferred.resolve);
        }));

        return $.when.apply($, _arr);
    };

})(jQuery);
