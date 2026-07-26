<?php

it('exposes the production dashboard subdomain through traefik', function () {
    $compose = file_get_contents(__DIR__.'/../../docker-compose.prod.yml');

    expect($compose)
        ->toContain('traefik.http.routers.production-dashboard.rule=Host(`dashboard.wartkinurt.pl`)')
        ->toContain('traefik.http.routers.production-dashboard.entrypoints=websecure')
        ->toContain('traefik.http.routers.production-dashboard.tls.certresolver=letsencrypt')
        ->toContain('traefik.http.services.production-dashboard.loadbalancer.server.port=80');
});

it('mounts the existing production nginx dashboard config', function () {
    $compose = file_get_contents(__DIR__.'/../../docker-compose.prod.yml');

    expect($compose)
        ->toContain('./docker/nginx/laravel.conf:/etc/nginx/conf.d/default.conf')
        ->not->toContain('./nginx/production.conf:/etc/nginx/conf.d/default.conf');
});

it('registers dashboard subdomain routes before the main catch all route', function () {
    $routes = file_get_contents(__DIR__.'/../../routes/web.php');

    $domainPosition = strpos($routes, "Route::domain('dashboard.wartkinurt.pl')");
    $dashboardPathPosition = strpos($routes, "Route::get('/dashboard/{any?}'");
    $catchAllPosition = strpos($routes, "Route::get('/{any}'");

    expect($domainPosition)->not->toBeFalse()
        ->and($dashboardPathPosition)->not->toBeFalse()
        ->and($catchAllPosition)->not->toBeFalse()
        ->and($domainPosition)->toBeLessThan($catchAllPosition)
        ->and($dashboardPathPosition)->toBeLessThan($catchAllPosition);
});

it('serves the dashboard spa for the dashboard nginx host', function () {
    $nginx = file_get_contents(__DIR__.'/../../docker/nginx/laravel.conf');

    expect($nginx)
        ->toContain('server_name dashboard.wartkinurt.pl')
        ->toContain('try_files $uri $uri/ /index.php?$query_string');
});

it('builds the dashboard vite entry for production', function () {
    $viteConfig = file_get_contents(__DIR__.'/../../vite.config.js');
    $dashboardView = file_get_contents(__DIR__.'/../../resources/views/dashboard.blade.php');

    expect($viteConfig)
        ->toContain("'resources/js/dashboard/main.js'")
        ->toContain("dashboard: 'resources/js/dashboard/main.js'");

    expect($dashboardView)
        ->toContain("@vite(['resources/js/dashboard/main.js'])")
        ->toContain("config('app.name')")
        ->not->toContain('env(');
});
