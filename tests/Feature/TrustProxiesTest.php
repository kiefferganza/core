<?php

use App\Providers\TrustedProxyServiceProvider;
use Illuminate\Http\Request;

it('sets trusted proxies via Request::setTrustedProxies on boot', function () {
    config(['trustedproxy.proxies' => '10.0.0.1']);

    $provider = new TrustedProxyServiceProvider(app());
    $provider->boot();

    expect(Request::getTrustedProxies())->toContain('10.0.0.1');
});

it('allows null trusted proxies outside production', function () {
    config(['trustedproxy.proxies' => null]);

    expect(config('trustedproxy.proxies'))->toBeNull();
});

it('throws when trusted proxies is empty in production', function () {
    config(['trustedproxy.proxies' => null]);

    $this->app->detectEnvironment(fn () => 'production');

    $provider = new TrustedProxyServiceProvider(app());

    expect(fn () => $provider->boot())->toThrow(
        \RuntimeException::class,
        'TRUSTED_PROXIES must be configured in production'
    );
});

it('throws when trusted proxies is wildcard in production', function () {
    config(['trustedproxy.proxies' => '*']);

    $this->app->detectEnvironment(fn () => 'production');

    $provider = new TrustedProxyServiceProvider(app());

    expect(fn () => $provider->boot())->toThrow(
        \RuntimeException::class,
        'TRUSTED_PROXIES=* is not allowed in production'
    );
});

it('allows cidr ranges in production without throwing', function () {
    config(['trustedproxy.proxies' => '10.0.0.0/8,172.16.0.0/12']);

    $this->app->detectEnvironment(fn () => 'production');

    $provider = new TrustedProxyServiceProvider(app());

    expect(fn () => $provider->boot())->not->toThrow(\RuntimeException::class);
});
