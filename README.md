<p align="center">
<a href="https://github.com/mRamadan0101/laravel-admin">
<img src="https://laravel-admin.org/images/logo002.png" alt="laravel-admin">
</a>
</p>

<p align="center">Administrative interface builder for Laravel. Build CRUD backends with a few lines of code.</p>

<p align="center">
<a href="https://github.com/mRamadan0101/laravel-admin">Repository</a> |
<a href="UPGRADE.md">Upgrade guide</a> |
<a href="#extensions">Extensions</a> |
<a href="CONTRIBUTING.md">Contribute</a>
</p>

<p align="center">
    <a href="https://github.com/mRamadan0101/laravel-admin/actions/workflows/tests.yml">
        <img src="https://github.com/mRamadan0101/laravel-admin/actions/workflows/tests.yml/badge.svg" alt="Tests">
    </a>
    <a href="https://github.com/mRamadan0101/laravel-admin">
        <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square" alt="PHP 8.2+">
    </a>
    <a href="https://github.com/mRamadan0101/laravel-admin">
        <img src="https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012-FF2D20?style=flat-square" alt="Laravel 10 to 12">
    </a>
    <a href="https://packagist.org/packages/mramadan0101/laravel-admin">
        <img src="https://img.shields.io/packagist/v/mramadan0101/laravel-admin.svg?style=flat-square" alt="Packagist">
    </a>
</p>

<p align="center">
    Maintained independently from <a href="https://github.com/z-song/laravel-admin">z-song/laravel-admin</a>.
    The PHP namespace stays <code>Encore\Admin</code>. The Packagist name is <code>mramadan0101/laravel-admin</code>.
</p>

Requirements
------------

- PHP >= 8.2
- Laravel 10, 11 or 12
- Fileinfo PHP Extension

Versions
------------

| Version | Branch   | PHP       | Laravel | Frontend                                              | Status |
|---------|----------|-----------|---------|-------------------------------------------------------|--------|
| 2.x     | `master` | 8.2+      | 10 – 12 | AdminLTE 4, Bootstrap 5.3, jQuery 3.7, Font Awesome 6 | active |
| 1.x     | `1.x`    | 7.2 – 8.x | 6 – 8   | AdminLTE 2, Bootstrap 3.4.1, jQuery 3.7 + migrate, FA 4 | legacy |

`master` is the 2.x line. Upgrading an existing 1.x app? Read [UPGRADE.md](UPGRADE.md).

Installation
------------

```bash
composer require mramadan0101/laravel-admin
```

Do not install this next to the original `encore/laravel-admin` package. Both ship the `Encore\Admin` namespace, so remove the old one first if it is already in the application.

Publish the assets and install the admin tables:

```bash
php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"
php artisan admin:install
```

The config file is `config/admin.php`. It controls the route prefix, database connection, and table names.

Open `http://localhost/admin/` and log in with username `admin` and password `admin`.

Configurations
------------

`config/admin.php` holds the admin settings.

Right to left support
------------

The RTL build of AdminLTE loads automatically for right-to-left locales (`ar`, `fa`, `he`, `ur`, ...).
Set `rtl` in `config/admin.php` to `true` or `false` to force it on or off.

Dark mode
------------

Set `theme` in `config/admin.php` to `light`, `dark` or `auto`. Users can switch the color mode from the navbar.

Global search
------------

The navbar search box (`/` or `Ctrl+K`) searches the menu and every model you register, for example in `app/Admin/bootstrap.php`:

```php
Admin::globalSearch(App\Models\Post::class)
    ->label('Posts')
    ->columns(['title', 'author.name'])   // `relation.column` searches through a relation
    ->subtitle('author.name')
    ->url('posts/{id}/edit')
    ->permission('posts');                // hidden from users without this permission
```

You can also list resources under `global_search.resources` in `config/admin.php`.

Trash for soft deletes
------------

For models that use `SoftDeletes`, `$grid->enableTrash()` adds an *All / Trash* switch to the grid. Deleting a row still only soft-deletes it. In the trash view each row, or a batch selection, can be restored or deleted permanently.

Security
------------

- **Two-factor authentication**: each admin can turn on TOTP (Google Authenticator, Authy, 1Password, ...) from *Settings*. Recovery codes are included. Disable the feature with `ADMIN_TWO_FACTOR=false`.
- **Login throttling**: 5 failed attempts per username and IP lock the login for 60 seconds (`auth.throttle`).
- **Operation log**: passwords, tokens and 2FA codes are masked. Add more keys in `operation_log.hidden_fields`.
- **Uploads**: files get random names so they can't overwrite each other. Use `->originalName()` to keep the client file name.

## Extensions

These extensions were written for laravel-admin 1.x. Check each one before using it with 2.x.

| Extension                                        | Description                              | laravel-admin                              |
| ------------------------------------------------ | ---------------------------------------- |---------------------------------------- |
| [helpers](https://github.com/laravel-admin-extensions/helpers)             | Several tools to help you in development | ~1.5 |
| [media-manager](https://github.com/laravel-admin-extensions/media-manager) | Provides a web interface to manage local files          | ~1.5 |
| [api-tester](https://github.com/laravel-admin-extensions/api-tester) | Help you to test the local laravel APIs          |~1.5 |
| [scheduling](https://github.com/laravel-admin-extensions/scheduling) | Scheduling task manager for laravel-admin          |~1.5 |
| [redis-manager](https://github.com/laravel-admin-extensions/redis-manager) | Redis manager for laravel-admin          |~1.5 |
| [backup](https://github.com/laravel-admin-extensions/backup) | An admin interface for managing backups          |~1.5 |
| [log-viewer](https://github.com/laravel-admin-extensions/log-viewer) | Log viewer for laravel           |~1.5 |
| [config](https://github.com/laravel-admin-extensions/config) | Config manager for laravel-admin          |~1.5 |
| [reporter](https://github.com/laravel-admin-extensions/reporter) | Provides a developer-friendly web interface to view the exception          |~1.5 |
| [wangEditor](https://github.com/laravel-admin-extensions/wangEditor) | A rich text editor based on [wangeditor](http://www.wangeditor.com/)         |~1.6 |
| [summernote](https://github.com/laravel-admin-extensions/summernote) | A rich text editor based on [summernote](https://summernote.org/)          |~1.6 |
| [china-distpicker](https://github.com/laravel-admin-extensions/china-distpicker) | 一个基于[distpicker](https://github.com/fengyuanchen/distpicker)的中国省市区选择器          |~1.6 |
| [simplemde](https://github.com/laravel-admin-extensions/simplemde) | A markdown editor based on [simplemde](https://github.com/sparksuite/simplemde-markdown-editor)          |~1.6 |
| [phpinfo](https://github.com/laravel-admin-extensions/phpinfo) | Integrate the `phpinfo` page into laravel-admin          |~1.6 |
| [php-editor](https://github.com/laravel-admin-extensions/php-editor) <br/> [python-editor](https://github.com/laravel-admin-extensions/python-editor) <br/> [js-editor](https://github.com/laravel-admin-extensions/js-editor)<br/> [css-editor](https://github.com/laravel-admin-extensions/css-editor)<br/> [clike-editor](https://github.com/laravel-admin-extensions/clike-editor)| Several programing language editor extensions based on code-mirror          |~1.6 |
| [star-rating](https://github.com/laravel-admin-extensions/star-rating) | Star Rating extension for laravel-admin          |~1.6 |
| [json-editor](https://github.com/laravel-admin-extensions/json-editor) | JSON Editor for Laravel-admin          |~1.6 |
| [grid-lightbox](https://github.com/laravel-admin-extensions/grid-lightbox) | Turn your grid into a lightbox & gallery          |~1.6 |
| [daterangepicker](https://github.com/laravel-admin-extensions/daterangepicker) | Integrates daterangepicker into laravel-admin          |~1.6 |
| [material-ui](https://github.com/laravel-admin-extensions/material-ui) | Material-UI extension for laravel-admin          |~1.6 |
| [sparkline](https://github.com/laravel-admin-extensions/sparkline) | Integrates jQuery sparkline into laravel-admin          |~1.6 |
| [chartjs](https://github.com/laravel-admin-extensions/chartjs) | Use Chartjs in laravel-admin          |~1.6 |
| [echarts](https://github.com/laravel-admin-extensions/echarts) | Use Echarts in laravel-admin          |~1.6 |
| [simditor](https://github.com/laravel-admin-extensions/simditor) | Integrates simditor full-rich editor into laravel-admin          |~1.6 |
| [cropper](https://github.com/laravel-admin-extensions/cropper) | A simple jQuery image cropping plugin.          |~1.6 |
| [composer-viewer](https://github.com/laravel-admin-extensions/composer-viewer) | A web interface of composer packages in laravel.          |~1.6 |
| [data-table](https://github.com/laravel-admin-extensions/data-table) | Advanced table widget for laravel-admin |~1.6 |
| [watermark](https://github.com/laravel-admin-extensions/watermark) | Text watermark for laravel-admin |~1.6 |
| [google-authenticator](https://github.com/ylic/laravel-admin-google-authenticator) | Google authenticator |~1.6 |

## Contribute

Bug reports and pull requests are welcome. Read [CONTRIBUTING.md](CONTRIBUTING.md) for how to report a bug and how the `master` and `1.x` branches differ.

<a href="https://github.com/mRamadan0101/laravel-admin/graphs/contributors">People who already contributed</a>

Other
------------

2.x is built on:

- [Laravel](https://laravel.com/)
- [AdminLTE 4](https://adminlte.io/)
- [Bootstrap 5](https://getbootstrap.com/)
- [jQuery](https://jquery.com/)
- [Font Awesome](https://fontawesome.com/)
- [flatpickr](https://flatpickr.js.org/)
- [select2](https://select2.org/)
- [bootstrap-fileinput](https://github.com/kartik-v/bootstrap-fileinput)
- [jquery-pjax](https://github.com/defunkt/jquery-pjax)
- [Nestable](http://dbushell.github.io/Nestable/)
- [toastr](https://github.com/CodeSeven/toastr)
- [SweetAlert2](https://github.com/sweetalert2/sweetalert2)
- [OverlayScrollbars](https://github.com/KingSora/OverlayScrollbars)
- [fontawesome-iconpicker](https://github.com/farbelous/fontawesome-iconpicker)

License
------------

`laravel-admin` is licensed under [The MIT License (MIT)](LICENSE).
