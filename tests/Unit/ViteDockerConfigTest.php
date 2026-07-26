<?php

it('lets vite own content type headers for transformed css modules', function () {
    $nginxConfig = file_get_contents(__DIR__.'/../../docker/nginx.conf');

    expect($nginxConfig)
        ->not->toContain('add_header Content-Type "text/css')
        ->not->toContain('add_header Content-Type "text/javascript');
});

it('proxies hmr websocket traffic to the vite dev server', function () {
    $nginxConfig = file_get_contents(__DIR__.'/../../docker/nginx.conf');
    $viteConfig = file_get_contents(__DIR__.'/../../vite.config.js');

    expect($nginxConfig)
        ->toContain('location = /_vite/ws')
        ->toContain('proxy_pass https://vite:5173')
        ->not->toContain('proxy_pass https://vite:24678');

    expect($viteConfig)
        ->toContain("protocol: 'wss'")
        ->toContain("clientPort: 443")
        ->toContain("path: '/_vite/ws'")
        ->not->toContain('port: 24678');
});
