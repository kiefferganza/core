<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Set this to the IP address(es) or CIDR range(s) of your reverse proxy /
    | load balancer so Laravel can correctly read forwarded headers such as
    | X-Forwarded-For, X-Forwarded-Proto, and X-Forwarded-Host.
    |
    | Accepted formats (comma-separated string or left blank):
    |   Single IP      — TRUSTED_PROXIES=10.0.0.1
    |   Multiple IPs   — TRUSTED_PROXIES=10.0.0.1,10.0.0.2
    |   CIDR range     — TRUSTED_PROXIES=10.0.0.0/8
    |   AWS ELB / ALB  — TRUSTED_PROXIES=10.0.0.0/8,172.16.0.0/12
    |
    | WARNING: Do NOT set this to "*" in production. Trusting all proxies
    | allows any client to spoof X-Forwarded-* headers, enabling IP spoofing,
    | HTTPS downgrade attacks, and host-header injection.
    |
    | Leave blank (null) in local development — the application boots without
    | proxy trust, which is safe for direct browser-to-app connections.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxy Headers
    |--------------------------------------------------------------------------
    |
    | The forwarded headers to trust from your proxy. The default covers the
    | standard set plus the AWS ELB header, which is appropriate for most
    | load-balanced deployments.
    |
    */

    'headers' => \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
        | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
        | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
        | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        | \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB,

];
