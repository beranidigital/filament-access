# filament-access

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beranidigital/filament-access.svg?style=flat-square)](https://packagist.org/packages/beranidigital/filament-access)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/beranidigital/filament-access/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/beranidigital/filament-access/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/beranidigital/filament-access/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/beranidigital/filament-access/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/beranidigital/filament-access.svg?style=flat-square)](https://packagist.org/packages/beranidigital/filament-access)



Multi-panel aware fine-grained permissions for Laravel Filament to analyze, generate and inject into all classes and methods in the application.

## What this package does
- Generate list of almost all Filament component permissions
- Automatically redefine classes to avoid conflict

## Why I need this package
- You have too many classes and methods to define permissions one by one
- Default Filament authorization doesn't support multi-panel

## What this package doesn't do
- It doesn't provide complete solution for authorization
- It's not meant for custom defined permissions, you need to define it yourself

## Installation

You can install the package via composer:

```bash
composer require beranidigital/filament-access
```



You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-access-config"
```


## Usage

```php
\BeraniDigitalID\FilamentAccess\Facades\FilamentAccess::analyzeAll();
```
or
```bash
php artisan filament-access:generate
php artisan filament-access:hijack
```

## How it works
1. It hooks into a class to correct the permission
2. The class will call `Gate::authorize('viewAny', $correctArgument)` with an example of `App\Filament\Resources\MyResource` as the argument
3. It's up to you to authorize it with your own custom logic
```php
\Illuminate\Support\Facades\Gate::before(function ($user, $ability, $arguments) {
    $permission = \BeraniDigitalID\FilamentAccess\Facades\FilamentAccess::determinePermission($ability, $arguments);
    if (!$user->hasPermissionTo($permission)) {
        return false;
    }
    // continue to the next authorization logic
});
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Yusuf Sekhan Althaf](https://github.com/Ticlext-Altihaf)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
