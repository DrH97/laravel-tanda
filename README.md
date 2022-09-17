# Tanda Api

[![GitHub TestCI Workflow](https://github.com/DrH97/laravel-tanda/actions/workflows/test.yml/badge.svg?branch=master)](https://github.com/DrH97/laravel-tanda/actions/workflows/test.yml)
[![Github StyleCI Workflow](https://github.com/DrH97/laravel-tanda/actions/workflows/styleci.yml/badge.svg?branch=master)](https://github.com/DrH97/laravel-tanda/actions/workflows/styleci.yml)
[![codecov](https://codecov.io/gh/DrH97/laravel-tanda/branch/master/graph/badge.svg?token=6b0d0ba1-c2c6-4077-8c3a-1f567eea88a0)](https://codecov.io/gh/DrH97/laravel-tanda)

[![Latest Stable Version](http://poser.pugx.org/drh/laravel-tanda/v)](https://packagist.org/packages/drh/laravel-tanda) 
[![Total Downloads](http://poser.pugx.org/drh/laravel-tanda/downloads)](https://packagist.org/packages/drh/laravel-tanda) 
[![License](http://poser.pugx.org/drh/laravel-tanda/license)](https://packagist.org/packages/drh/laravel-tanda) 
[![PHP Version Require](http://poser.pugx.org/drh/laravel-tanda/require/php)](https://packagist.org/packages/drh/laravel-tanda)

This is a <i>Laravel</i> package that interfaces with [Tanda](https://www.tanda.africa/) Payments Api.
The API enables you to initiate mobile payments, disburse payments to mobile and bank, purchase airtime & bundles* and to pay for utility bills.

Check out their [api documentation](https://www.tanda.africa/api).

## Documentation

### Installation

You can install the package via composer:

```bash
composer require drh/laravel-tanda
```

The package will automatically register itself.

You can publish the config file with:
```bash
php artisan tanda:install
```

### Getting Started
- ### Account
Enables you to check the status of items

1. Account balance
```php
Account::balance();
```

- ### Utility
Enables purchase of payment of goods and services

1. Airtime Purchase
```php
Utility::airtimePurchase(0712345678, 100);
```

2. Bill Payment
```php
Utility::billPayment(11011011011, 1000, Providers::KPLC_PREPAID);
```

3. Transaction status
```php
Utility::requestStatus("...");
```

- ### Payments
Coming soon

<br>

### NOTE: Phone Number Validation
The phone validator was built using regex and the latest allocation of prefixes by Communication authority of Kenya (Apr, 2021).
Check the [docs](docs) to see the pdf listing with allocations.

## Testing

You can run the tests with:

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [jmnabangi@gmail.com](mailto:jmnabangi@gmail.com) instead of using the issue tracker.

## Credits

- [Nabcellent](https://github.com/Nabcellent)
- [Dr H](https://github.com/drh97)

[comment]: <> (- [All Contributors]&#40;../../contributors&#41;)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
