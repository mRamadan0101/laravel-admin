# Changelog

## v2.0.0

New major version for modern stacks. See [UPGRADE.md](UPGRADE.md) for the migration guide.

- Requirements: `php ^8.2`, `laravel/framework ^10.0|^11.0|^12.0`, `symfony/dom-crawler ^6.0|^7.0`.
  `doctrine/dbal` is no longer required.
- PHP 8.4 ready: explicit nullable parameter types everywhere.
- `admin:make` reads columns through `Schema::getColumns()` instead of Doctrine.
- Anonymous class migrations; route and service provider registration compatible with the Laravel 11+ skeleton.
- Frontend rebuilt on AdminLTE 4.10, Bootstrap 5.3.8, jQuery 3.7.1 and Font Awesome 6.7 (with v4 shims).
- Light / dark / auto color modes (`admin.theme`) with a navbar switcher; RTL build loaded automatically for RTL locales (`admin.rtl`).
  1.x `admin.skin` names keep working and now recolor the navbar, brand, primary
  buttons and accent variables (AdminLTE 4 has no built-in skins).
  Setting `admin.rtl` to a locale code (e.g. `"ar"`) adds a navbar language
  switcher; direction and translations follow the selected language.
- Replaced plugins: flatpickr for every date/time field, native `form-check` / `form-switch` instead of iCheck and
  bootstrap-switch, a Bootstrap 5 popover editor instead of x-editable, OverlayScrollbars instead of slimScroll.
- Updated plugins: SweetAlert2 11, select2 4.1 with the Bootstrap 5 theme, bootstrap-fileinput 5.5,
  bootstrap-duallistbox 4, fontawesome-iconpicker 3, toastr, nprogress, pjax.
- Compatibility layer for 1.x extensions: `plugins.js` (jQuery APIs of the removed plugins) and `bs3-compat.css`
  (Bootstrap 3 / AdminLTE 2 classes, disable with `Admin::$compatCss = null`).
- New helpers `admin_bs_color()` and `admin_icon_class()`.
- Tests run on Orchestra Testbench + PHPUnit 11; CI moved from Travis to GitHub Actions
  (PHP 8.2 - 8.4 x Laravel 10 - 12).

## v1.9.0 (1.x LTS)

Long-term support line for legacy applications: PHP 7.2 - 8.x, Laravel 6 - 8.
New features go to 2.x; this branch only receives security and compatibility fixes.

- Requirements: `php ^7.2|^8.0`, `laravel/framework ^6.0|^7.0|^8.0`, `symfony/dom-crawler ^4.0|^5.0`.
- jQuery 2.1.4 -> 3.7.1, loaded together with jquery-migrate 3.5.2 (`Admin::$jQueryMigrate`, set to `null` to disable).
  The old `jQuery-2.1.4.min.js` file is still shipped for extensions that reference it directly.
- Bootstrap 3.3.x -> 3.4.1 (XSS fixes in tooltip, popover, collapse, scrollspy).
- SweetAlert2 7.26.12 -> 7.33.1, toastr 2.1.3 -> 2.1.4.
- Test suite runs on Laravel 8 (`laravel/legacy-factories`, `fakerphp/faker`).

Republish assets after upgrading:

```
php artisan vendor:publish --tag=laravel-admin-assets --force
```

Older releases: see https://laravel-admin.org/docs/
