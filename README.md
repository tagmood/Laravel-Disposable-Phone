# Laravel Disposable Phone

Adds a validator to Laravel for checking whether a given phone number isn't originating from disposable phone services.
Uses the disposable numbers blacklist from [iP1SMS/disposable-phone-numbers](https://github.com/iP1SMS/disposable-phone-numbers) by default.

### Installation

1. Run the Composer require command to install the package:

    ```bash
    composer require tagmood/laravel-disposable-phone
    ```

2. If you don't use auto-discovery, open up your app config and add the Service Provider to the `$providers` array:

     ```php
    'providers' => [
        ...
     
        Tagmood\LaravelDisposablePhone\DisposablePhoneServiceProvider::class,
    ],
    ```

3. Publish the configuration file and adapt the configuration as desired:

	```bash
    php artisan vendor:publish --tag=laravel-disposable-phone
    ```

4. Run the following artisan command to fetch an up-to-date list of disposable numbers:
    
    ```bash
    php artisan disposablephone:update
    ```

5. (optional) In your languages directory, add for each language an extra language line for the validator:

	```php
	'indisposablephone' => 'Disposable phone numbers are not allowed.',
	```

6. (optional) It's highly advised to update the disposable numbers list regularly. You can either run the command yourself now and then or, if you make use of Laravel's scheduler, include it over there (`App\Console\Kernel`):
    
    ```php
    protected function schedule(Schedule $schedule)
	{
        $schedule->command('disposablephone:update')->weekly();
	}
    ```

### Usage

Use the `indisposablephone` validator to ensure a given field doesn't hold a disposable phone number.

```php
'field' => 'indisposablephone',
```

### Custom fetches

By default the package retrieves a new list by using `file_get_contents()`. 
If your application has different needs (e.g. when behind a proxy) please review the `disposable-phone.fetcher` configuration value.
