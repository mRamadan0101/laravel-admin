# Contribute

Thanks for helping with this project. This repository is the independently maintained 2.x line of laravel-admin (`mramadan0101/laravel-admin`). The original project lives at [z-song/laravel-admin](https://github.com/z-song/laravel-admin); issues and pull requests for this line belong here.

## Reporting a bug

Open an [issue](https://github.com/mRamadan0101/laravel-admin/issues) and include:

- PHP, Laravel and laravel-admin versions
- what you expected and what happened
- the smallest steps that reproduce it, plus the relevant grid or form code
- a screenshot or the browser console output when the problem is visual

Search the existing issues first so the same report is not opened twice.

## Pull requests

1. Fork the repository and branch off `master`. `master` is the 2.x line (PHP 8.2+, Laravel 10–12). The `1.x` branch only takes security and compatibility fixes for the legacy line.
2. Keep the change focused. A pull request that does one thing is much easier to review.
3. Match the style of the code around your change.
4. Add or update a test when the change has behavior that can break. The suite runs with `./vendor/bin/phpunit` and needs a MySQL database (see `phpunit.xml.dist`).
5. Describe what the change does and how to check it.

## Questions

Ask in an [issue](https://github.com/mRamadan0101/laravel-admin/issues). A quick search usually shows whether it was already answered.
