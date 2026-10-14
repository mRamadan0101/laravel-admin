# Changelog

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
