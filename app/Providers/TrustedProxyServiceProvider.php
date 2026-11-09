<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

/**
 * Intentionally uses Request::setTrustedProxies() directly instead of
 * $middleware->trustProxies() in bootstrap/app.php.
 *
 * The middleware approach configures but never enforces — a missing or wildcard
 * TRUSTED_PROXIES in production silently allows IP spoofing via forged
 * X-Forwarded-* headers. This provider fails loudly at boot time before any
 * request is served, so CI/CD catches misconfiguration before traffic hits it.
 * Every project inheriting from baseplate gets that enforcement for free.
 *
 * Tradeoff: less idiomatic Laravel, more explicit infrastructure contract.
 */
class TrustedProxyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $proxies = config('trustedproxy.proxies');

        if (app()->environment('production')) {
            if (empty($proxies)) {
                throw new \RuntimeException(
                    'TRUSTED_PROXIES must be configured in production. '.
                    'Set it to your load balancer IP(s) or CIDR range (e.g. 10.0.0.0/8).'
                );
            }

            if ($proxies === '*') {
                throw new \RuntimeException(
                    'TRUSTED_PROXIES=* is not allowed in production — it enables IP spoofing via forged X-Forwarded-* headers. '.
                    'Restrict it to your actual load balancer IP(s) or CIDR range.'
                );
            }
        }

        Request::setTrustedProxies(
            is_string($proxies)
                ? array_values(array_filter(array_map('trim', explode(',', $proxies))))
                : ($proxies ?? []),
            config('trustedproxy.headers'),
        );
    }
}
