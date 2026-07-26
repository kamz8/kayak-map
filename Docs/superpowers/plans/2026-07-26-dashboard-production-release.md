# Dashboard Production Release Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the dashboard release-ready for production on `https://dashboard.wartkinurt.pl` and convert every repository `README.md` to English.

**Architecture:** Keep the dashboard inside the existing Laravel application. Traefik routes the dashboard subdomain to the production Nginx service, Nginx forwards requests to Laravel, Laravel serves `dashboard.blade.php`, and the Vue dashboard SPA loads from production Vite assets.

**Tech Stack:** Laravel 11, PHP 8.3, Vue 3, Vite 7, Docker Compose, Nginx, Traefik, Pest.

## Global Constraints

- Production dashboard URL is `https://dashboard.wartkinurt.pl`.
- Keep `/dashboard` as a Laravel/Vue SPA fallback where needed.
- Do not introduce a separate dashboard container unless the current routing cannot reliably support the subdomain.
- Convert every repository `README.md` to English.
- Do not translate non-README documentation files as part of this release.
- Use Laravel 11 route/domain patterns and Vite production asset loading.
- Run Laravel Pint on dirty PHP files before completion.

---

## File Structure

- Modify `routes/web.php`: add dashboard subdomain route before catch-all routes and use `config('app.name')` through the Blade view.
- Modify `resources/views/dashboard.blade.php`: replace direct `env('APP_NAME')` usage with config-backed app name.
- Modify `docker-compose.prod.yml`: fix the missing production Nginx config mount and ensure Traefik dashboard labels include the dashboard subdomain.
- Modify `docker/nginx/laravel.conf`: make dashboard subdomain requests serve the dashboard SPA entry instead of the main app catch-all.
- Create or modify `tests/Unit/DashboardProductionConfigTest.php`: focused text-level config tests for Traefik, Nginx mount, dashboard route ordering, and Vite dashboard entry.
- Modify all repository `README.md` files: `README.md`, `devops/README.md`, `tests/README.md`, `tests/vitest/README.md`, `resources/js/dashboard/README.md`, `resources/js/dashboard/components/README.md`, `resources/js/dashboard/components/ui/README.md`, `resources/js/dashboard/modules/README.md`, and `resources/js/dashboard/modules/users/README.md`.

---

### Task 1: Production Dashboard Routing And Config

**Files:**
- Modify: `routes/web.php`
- Modify: `resources/views/dashboard.blade.php`
- Modify: `docker-compose.prod.yml`
- Modify: `docker/nginx/laravel.conf`
- Test: `tests/Unit/DashboardProductionConfigTest.php`

**Interfaces:**
- Consumes: Existing Laravel route loading from `routes/web.php`.
- Produces: A dashboard subdomain route that returns `view('dashboard')`; Docker Compose mounting `./docker/nginx/laravel.conf`; Nginx dashboard host forwarding all non-file requests to Laravel with dashboard context.

- [ ] **Step 1: Write failing config tests**

Create `tests/Unit/DashboardProductionConfigTest.php` with these Pest tests:

```php
<?php

it('exposes the production dashboard subdomain through traefik', function () {
    $compose = file_get_contents(base_path('docker-compose.prod.yml'));

    expect($compose)
        ->toContain('traefik.http.routers.production-dashboard.rule=Host(`dashboard.wartkinurt.pl`)')
        ->toContain('traefik.http.routers.production-dashboard.entrypoints=websecure')
        ->toContain('traefik.http.routers.production-dashboard.tls.certresolver=letsencrypt')
        ->toContain('traefik.http.services.production-dashboard.loadbalancer.server.port=80');
});

it('mounts the existing production nginx dashboard config', function () {
    $compose = file_get_contents(base_path('docker-compose.prod.yml'));

    expect($compose)
        ->toContain('./docker/nginx/laravel.conf:/etc/nginx/conf.d/default.conf')
        ->not->toContain('./nginx/production.conf:/etc/nginx/conf.d/default.conf');
});

it('registers dashboard subdomain routes before the main catch all route', function () {
    $routes = file_get_contents(base_path('routes/web.php'));

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
    $nginx = file_get_contents(base_path('docker/nginx/laravel.conf'));

    expect($nginx)
        ->toContain('server_name dashboard.wartkinurt.pl')
        ->toContain('fastcgi_param HTTP_X_DASHBOARD_HOST true')
        ->toContain('try_files $uri $uri/ /index.php?$query_string');
});

it('builds the dashboard vite entry for production', function () {
    $viteConfig = file_get_contents(base_path('vite.config.js'));
    $dashboardView = file_get_contents(base_path('resources/views/dashboard.blade.php'));

    expect($viteConfig)
        ->toContain("'resources/js/dashboard/main.js'")
        ->toContain("dashboard: 'resources/js/dashboard/main.js'");

    expect($dashboardView)
        ->toContain("@vite(['resources/js/dashboard/main.js'])")
        ->toContain("config('app.name')")
        ->not->toContain('env(');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/DashboardProductionConfigTest.php`

Expected before implementation: FAIL because the compose file mounts `./nginx/production.conf`, the dashboard subdomain route is missing, the Nginx dashboard host does not set `HTTP_X_DASHBOARD_HOST`, and the Blade title uses `env('APP_NAME')`.

- [ ] **Step 3: Implement minimal routing/config changes**

Update `routes/web.php` so subdomain routes are registered before catch-all routes:

```php
<?php

use Illuminate\Support\Facades\Route;

$dashboardView = function () {
    return view('dashboard');
};

Route::domain('dashboard.wartkinurt.pl')
    ->get('/{any?}', $dashboardView)
    ->where('any', '.*');

Route::get('/dashboard/{any?}', $dashboardView)->where('any', '.*');

Route::get('/{any}', function () {
    return view('index');
})->where('any', '^(?!api/|dashboard/).*$');
```

Update the title in `resources/views/dashboard.blade.php`:

```blade
<title>Dashboard - {{ config('app.name') }}</title>
```

Update the production Nginx mount in `docker-compose.prod.yml`:

```yaml
- ./docker/nginx/laravel.conf:/etc/nginx/conf.d/default.conf
```

Update the dashboard server block in `docker/nginx/laravel.conf` so PHP receives the dashboard host marker:

```nginx
location ~ \.php$ {
    fastcgi_pass app:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param HTTP_X_DASHBOARD_HOST true;
    include fastcgi_params;
    fastcgi_read_timeout 300;
}
```

- [ ] **Step 4: Run focused tests**

Run: `php artisan test --compact tests/Unit/DashboardProductionConfigTest.php`

Expected: PASS.

- [ ] **Step 5: Commit task**

Run:

```bash
git add routes/web.php resources/views/dashboard.blade.php docker-compose.prod.yml docker/nginx/laravel.conf tests/Unit/DashboardProductionConfigTest.php
git commit -m "fix(dashboard): enable production subdomain routing"
```

---

### Task 2: English README Cleanup

**Files:**
- Modify: `README.md`
- Modify: `devops/README.md`
- Modify: `tests/README.md`
- Modify: `tests/vitest/README.md`
- Modify: `resources/js/dashboard/README.md`
- Modify: `resources/js/dashboard/components/README.md`
- Modify: `resources/js/dashboard/components/ui/README.md`
- Modify: `resources/js/dashboard/modules/README.md`
- Modify: `resources/js/dashboard/modules/users/README.md`
- Test: `tests/Unit/ReadmeLanguageTest.php`

**Interfaces:**
- Consumes: Existing markdown documentation file paths.
- Produces: English-only README files and a guard test that fails on common Polish characters/phrases in README files.

- [ ] **Step 1: Write failing README language guard**

Create `tests/Unit/ReadmeLanguageTest.php`:

```php
<?php

it('keeps repository readme files in english', function () {
    $readmeFiles = collect([
        'README.md',
        'devops/README.md',
        'tests/README.md',
        'tests/vitest/README.md',
        'resources/js/dashboard/README.md',
        'resources/js/dashboard/components/README.md',
        'resources/js/dashboard/components/ui/README.md',
        'resources/js/dashboard/modules/README.md',
        'resources/js/dashboard/modules/users/README.md',
    ]);

    $polishSignals = [
        'ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż',
        'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż',
        'Przegląd', 'Instalacja', 'Użytkowanie', 'Konfiguracja', 'Wymagania', 'Testy jednostkowe', 'Komponenty', 'Moduły',
    ];

    $violations = $readmeFiles
        ->filter(fn (string $path): bool => file_exists(base_path($path)))
        ->flatMap(function (string $path) use ($polishSignals): array {
            $content = file_get_contents(base_path($path));

            return collect($polishSignals)
                ->filter(fn (string $signal): bool => str_contains($content, $signal))
                ->map(fn (string $signal): string => $path.' contains '.$signal)
                ->all();
        })
        ->values();

    expect($violations->all())->toBe([]);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact tests/Unit/ReadmeLanguageTest.php`

Expected before translation: FAIL with one or more README files containing Polish signals.

- [ ] **Step 3: Rewrite README files in English**

Replace Polish content with concise English documentation. Preserve useful commands, paths, module descriptions, and setup instructions. Keep dashboard UI Kit rules explicit in English, including “use UI Kit components instead of raw Vuetify components” for dashboard work.

- [ ] **Step 4: Run README language guard**

Run: `php artisan test --compact tests/Unit/ReadmeLanguageTest.php`

Expected: PASS.

- [ ] **Step 5: Commit task**

Run:

```bash
git add README.md devops/README.md tests/README.md tests/vitest/README.md resources/js/dashboard/README.md resources/js/dashboard/components/README.md resources/js/dashboard/components/ui/README.md resources/js/dashboard/modules/README.md resources/js/dashboard/modules/users/README.md tests/Unit/ReadmeLanguageTest.php
git commit -m "docs: translate readme files to english"
```

---

### Task 3: Release Verification And Master Merge

**Files:**
- Modify only files changed by Tasks 1 and 2 if verification exposes a release blocker.

**Interfaces:**
- Consumes: Passing Task 1 and Task 2 focused tests.
- Produces: A locally merged `master` branch containing the dashboard production release commits.

- [ ] **Step 1: Run formatter**

Run: `vendor/bin/pint --dirty`

Expected: PHP files are formatted with no remaining dirty formatting-only changes outside intended files.

- [ ] **Step 2: Run focused Laravel tests**

Run: `php artisan test --compact tests/Unit/DashboardProductionConfigTest.php tests/Unit/ReadmeLanguageTest.php tests/Unit/ViteDockerConfigTest.php`

Expected: PASS.

- [ ] **Step 3: Run production frontend build**

Run: `npm run build`

Expected: PASS and dashboard entry appears in the generated Vite manifest.

- [ ] **Step 4: Commit verification fixes if needed**

If verification changed files, run:

```bash
git add <changed-files>
git commit -m "fix: resolve dashboard release verification issues"
```

Expected: no commit is created if no files changed during verification.

- [ ] **Step 5: Inspect merge preconditions**

Run:

```bash
git status --short
git fetch origin master
git log --oneline origin/master..HEAD
git diff --stat origin/master...HEAD
```

Expected: worktree clean; intended commits only.

- [ ] **Step 6: Merge into master locally**

Run:

```bash
git checkout master
git pull origin master
git merge feature/dashboard/trails
```

Expected: merge succeeds without conflicts.

- [ ] **Step 7: Verify merged result**

Run: `php artisan test --compact tests/Unit/DashboardProductionConfigTest.php tests/Unit/ReadmeLanguageTest.php tests/Unit/ViteDockerConfigTest.php`

Expected: PASS on `master`.

- [ ] **Step 8: Report final state**

Report the merge commit or fast-forward result, the verification commands, and whether `master` is clean.

---

## Self-Review Notes

- Spec coverage: dashboard subdomain routing, production build readiness, auth/admin readiness documentation, README English conversion, and verification are covered by Tasks 1-3.
- Placeholder scan: this plan contains no TBD/TODO placeholders.
- Type/name consistency: test file names, route strings, and paths match the files listed in the File Structure section.
