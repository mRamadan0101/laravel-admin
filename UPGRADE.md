# Upgrading from 1.x to 2.x

laravel-admin 2.x targets modern stacks only. Applications that must stay on
older PHP or Laravel versions should use the `1.x` branch, which keeps
receiving security and compatibility fixes.

| Branch | PHP       | Laravel | Frontend                                              |
|--------|-----------|---------|-------------------------------------------------------|
| 1.x    | 7.2 - 8.x | 6 - 8   | AdminLTE 2, Bootstrap 3.4.1, jQuery 3.7 + migrate, FA 4 |
| 2.x    | 8.2+      | 10 - 12 | AdminLTE 4, Bootstrap 5.3, jQuery 3.7, Font Awesome 6 |

## 1. Update the package

```
composer require encore/laravel-admin:^2.0
```

`doctrine/dbal` is no longer required: `admin:make` reads the table columns with
`Schema::getColumns()`. Remove it from your `composer.json` if nothing else uses it.

## 2. Republish assets

All bundled frontend libraries changed, so the published copy in
`public/vendor/laravel-admin` must be replaced:

```
php artisan vendor:publish --tag=laravel-admin-assets --force
```

If you use `php artisan admin:minify`, run it again afterwards.

## 3. Configuration

Compare your `config/admin.php` with the one shipped in the package. New keys:

- `skin` – 1.x names keep working. `"blue"` gives a dark sidebar, `"blue-light"`
  (or `"skin-blue-light"`) a light one. Supported colors: blue, yellow, green,
  purple, red, black.
- `theme` – Bootstrap color mode: `light`, `dark` or `auto` (follows the OS).
  Users can switch it from the navbar; the choice is stored in `localStorage`.
- `rtl` – load the RTL build of AdminLTE. `null` (default) enables it
  automatically for RTL locales (ar, fa, he, ur, ...).
- `layout` – AdminLTE 4 layout options: `fixed`, `fixed-footer`, `sidebar-mini`,
  `sidebar-collapse`. The 1.x values `layout-boxed` and `layout-top-nav` have no
  AdminLTE 4 styles; they are still added to `<body>` but change nothing.

The migrations are now anonymous classes. Already-run migrations are not
affected.

## 4. Removed and replaced frontend libraries

| 1.x                                   | 2.x                                          |
|---------------------------------------|----------------------------------------------|
| AdminLTE 2 / Bootstrap 3              | AdminLTE 4.10 / Bootstrap 5.3                |
| Font Awesome 4                        | Font Awesome 6 Free + v4 shims               |
| iCheck                                | native `form-check` (+ jQuery shim)          |
| bootstrap-switch                      | `form-switch` (+ jQuery shim)                |
| eonasdan datetimepicker + moment      | flatpickr (+ jQuery shim)                    |
| bootstrap-number-input                | built-in `-`/`+` buttons (+ jQuery shim)     |
| bootstrap3-editable (x-editable)      | Bootstrap 5 popover editor (+ jQuery shim)   |
| slimScroll                            | OverlayScrollbars                            |
| SweetAlert2 7                         | SweetAlert2 11                               |
| select2 4.0                           | select2 4.1 + `select2-bootstrap-5-theme`    |
| bootstrap-fileinput 4                 | bootstrap-fileinput 5.5 (`fa6` theme)        |
| bootstrap-duallistbox 3               | bootstrap-duallistbox 4                      |
| fontawesome-iconpicker 1              | fontawesome-iconpicker 3                     |

### Compatibility layer for extensions

Most 1.x extensions keep working without changes thanks to two layers that ship
with 2.x:

- **`laravel-admin/plugins.js`** re-implements the jQuery APIs of the removed
  plugins on top of their replacements: `$.fn.iCheck` (including the
  `ifChecked`/`ifChanged`/... events), `$.fn.bootstrapSwitch`,
  `$.fn.datetimepicker` (moment formats are converted, `dp.change` is fired,
  `data('DateTimePicker')` is available), `$.fn.bootstrapNumber`,
  `$.fn.editable`, and the Bootstrap 3 `tooltip('destroy')` style calls.
- **`laravel-admin/bs3-compat.css`** maps Bootstrap 3 / AdminLTE 2 classes to
  their Bootstrap 5 look: `box*`, `panel*`, `label-*`, `btn-default`, `btn-xs`,
  `pull-left/right`, `hide`/`hidden`, `form-group`, `control-label`,
  `help-block`, `input-group-addon`, `bg-aqua`/`text-red` style colors, etc.
  Set `Admin::$compatCss = null` in `app/Admin/bootstrap.php` once you no longer
  need it.

`fa fa-*` icons keep working through the v4 shims. Icon names stored in the
database (menu, `$form->icon()`) keep the 1.x `fa-xxx` format. Brand and regular
icons picked in 2.x are stored with their style prefix (`fab fa-github`).

SweetAlert: `swal({...})` and `$.admin.swal({...})` still accept the 1.x
options. `type` is mapped to `icon`, and `confirmButtonClass` to `customClass`.
New code should use `icon`.

## 5. Custom views and inline HTML

If you override package views (`resources/views/vendor/admin`) or output HTML
from custom fields, displayers, actions or widgets, update the markup:

| Bootstrap 3 / AdminLTE 2                 | Bootstrap 5 / AdminLTE 4                     |
|------------------------------------------|----------------------------------------------|
| `data-toggle`, `data-dismiss`, `data-target` | `data-bs-toggle`, `data-bs-dismiss`, `data-bs-target` |
| `box`, `box-header`, `box-body`, `box-footer` | `card`, `card-header`, `card-body`, `card-footer` |
| `box-primary` (outline)                  | `card-primary card-outline`                  |
| `panel panel-default`                    | `card`                                       |
| `label label-success`                    | `badge text-bg-success`                      |
| `btn-default`                            | `btn-outline-secondary` / `btn-secondary`    |
| `btn-xs`                                 | `btn-sm`                                     |
| `pull-right` / `pull-left`               | `float-end` / `float-start`                  |
| `col-xs-*`                               | `col-*`                                      |
| `hidden-xs`                              | `d-none d-sm-inline-block`                   |
| `input-group-addon`, `input-group-btn`   | `input-group-text`, buttons directly in `.input-group` |
| `control-label`                          | `col-form-label`                             |
| `help-block`                             | `form-text`                                  |
| `has-error` + `control-label` message    | `has-error` + `invalid-feedback d-block`     |
| `close` button in modals                 | `btn-close` (placed after the title)         |
| `nav-tabs > li.active > a`               | `nav-tabs > li.nav-item > a.nav-link.active` |
| `tab-pane active`                        | `tab-pane active show`                       |
| `fa fa-edit`                             | `fa-solid fa-pen-to-square`                  |

JavaScript:

- `$(el).modal('show')`, `.tooltip()`, `.popover()` still work through the
  Bootstrap 5 jQuery bridge, but the bridge throws on unknown methods. Prefer
  `bootstrap.Modal.getOrCreateInstance(el)` and friends.
- Bootstrap 5 handles `data-bs-toggle` clicks in the capture phase. If a click
  handler inserts the collapse/modal target first, toggle it yourself
  (`bootstrap.Collapse.getOrCreateInstance(el, {toggle: false}).toggle()`).
- The sidebar markup is AdminLTE 4 (`.app-sidebar .sidebar-menu .nav-item`
  with `.menu-open` / `.nav-link.active`). Call `$.admin.activateMenu()` to
  re-highlight the item matching the current URL.
- Bootstrap tooltips, popovers and dropdowns in pjax content are
  initialised and disposed automatically (`$.admin.initBootstrap()`).

PHP helpers:

- `Box::style()` produces `card-{style} card-outline`. Call `->solid()` for a
  filled header.
- `admin_bs_color('aqua')` converts AdminLTE 2 color names to Bootstrap 5
  contextual names (`info`). The label, badge, dot and info box displayers use
  it, so 1.x color names keep working there.
- `admin_icon_class('fa-bars')` returns the class list for a stored icon value.

## 6. Tests

The package test suite runs on Orchestra Testbench (BrowserKit) and PHPUnit
10/11. If your application tests assert on admin markup, update the selectors
for the class changes above.
