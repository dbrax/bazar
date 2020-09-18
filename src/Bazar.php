<?php

namespace Bazar;

use Bazar\Exceptions\InvalidCurrencyException;
use Bazar\Http\Middleware\ComponentMiddleware;
use Bazar\Http\Middleware\ShareComponentData;
use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

abstract class Bazar
{
    /**
     * The package version.
     *
     * @var string
     */
    public const VERSION = '0.1.0';

    /**
     * Get the version.
     *
     * @return string
     */
    public static function version(): string
    {
        return static::VERSION;
    }

    /**
     * Get the asset version.
     *
     * @param  string|null  $path
     * @return string|null
     */
    public static function assetVersion(string $path = null): ?string
    {
        $path = $path ?: public_path('mix-manifest.json');

        return is_file($path) ? md5_file($path) : null;
    }

    /**
     * Get all the currencies.
     *
     * @return array
     */
    public static function currencies(): array
    {
        return Config::get('bazar.currencies.available', []);
    }

    /**
     * Get or set the currency in use.
     *
     * @param  string|null  $currency
     * @return string
     *
     * @throws \Bazar\Exceptions\InvalidCurrencyException
     */
    public static function currency(string $currency = null): string
    {
        if (is_null($currency)) {
            return Config::get('bazar.currencies.default', 'usd');
        }

        if (! in_array($currency, array_keys(static::currencies()))) {
            throw new InvalidCurrencyException("The [{$currency}] currency is not available.");
        }

        Config::set('bazar.currencies.default', $currency);

        return $currency;
    }

    /**
     * Register Bazar routes.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function routes(Closure $callback): void
    {
        Route::as('bazar.')
            ->prefix('bazar')
            ->middleware([
                ComponentMiddleware::class, 'web', 'auth', 'can:manage-bazar', ShareComponentData::class,
            ])->group(function ($router) use ($callback) {
                $callback($router);
            });
    }
}
