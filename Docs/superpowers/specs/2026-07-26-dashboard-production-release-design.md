# Dashboard Production Release Design

## Goal

Prepare a release-ready production dashboard for Kayak Map. The dashboard must be available on the production subdomain `https://dashboard.wartkinurt.pl`, built as part of the production frontend bundle, and documented in English across every repository `README.md` file.

## Scope

- Expose the dashboard through Traefik on `dashboard.wartkinurt.pl`.
- Keep `/dashboard` as the Laravel/Vue SPA route fallback where needed, but treat the subdomain as the production entry point.
- Verify the dashboard production build does not depend on the Vite development server.
- Verify admin authentication and existing admin setup paths are production-ready.
- Convert every `README.md` in the repository to English.
- Add or update focused tests/smoke checks for the production dashboard routing and build configuration.

## Current Context

- The dashboard Vue app entry point is `resources/js/dashboard/main.js`.
- The dashboard Blade entry point is `resources/views/dashboard.blade.php`.
- Production Docker Compose already contains a Traefik router named `production-dashboard` with the host rule `Host(\`dashboard.wartkinurt.pl\`)` on `nginx-production`.
- Blue, green, and test services already define dashboard environment URLs such as `DASHBOARD_URL`.
- The repository currently contains nine `README.md` files that must be English-only for this release.

## Architecture

The production dashboard remains part of the Laravel application and uses the existing dashboard SPA bundle. Traefik routes `dashboard.wartkinurt.pl` to the production Nginx/Laravel service. Laravel serves the dashboard Blade view for dashboard requests, and Vue Router handles client-side dashboard navigation after initial load.

The implementation should prefer small corrections to existing Docker, Nginx, Laravel route, and Vite configuration over introducing a separate dashboard container. A separate container is out of scope unless the current routing cannot reliably support the subdomain.

## Components

- `docker-compose.prod.yml`: Traefik labels, dashboard host routing, and dashboard-related environment values.
- Production Nginx config: request forwarding and static asset serving for dashboard and Laravel assets.
- Laravel web routes: dashboard entry route and subdomain compatibility if required.
- Vite config and Blade views: production asset manifest and dashboard entry bundle.
- Dashboard auth modules: API base URL, token refresh, and admin-only access expectations.
- `README.md` files: English documentation for setup, development, dashboard usage, tests, and DevOps.

## Data And Request Flow

1. Browser opens `https://dashboard.wartkinurt.pl`.
2. Traefik matches `Host(\`dashboard.wartkinurt.pl\`)` and forwards traffic to the production Nginx service.
3. Nginx forwards PHP requests to Laravel and serves built assets from `public/build`.
4. Laravel returns `dashboard.blade.php` for dashboard entry requests.
5. The dashboard SPA loads its production bundle and uses existing API endpoints for authentication and dashboard data.

## Error Handling

- Missing production dashboard assets should fail during build or targeted tests, not after deployment.
- Incorrect Traefik host/service labels should be covered by focused configuration tests.
- Auth failures should surface as dashboard login errors and should not expose protected dashboard routes.
- Environment-dependent checks that cannot run locally must be reported clearly with the exact command or deployment check required.

## Testing And Verification

Minimum verification for the implementation:

- Run Laravel Pint on dirty PHP files.
- Run focused Laravel tests for production dashboard routing/configuration.
- Run a production frontend build.
- Run existing relevant dashboard/frontend tests where available and practical.
- Validate every `README.md` is English-only enough for release purposes by reviewing and updating all matches.

## Out Of Scope

- Rebuilding dashboard UI/UX.
- Replacing the existing authentication system.
- Creating a separate dashboard deployment artifact unless required by a discovered blocker.
- Translating non-README documentation files.
- Creating release tags or GitHub releases unless requested after implementation.

## Success Criteria

- `https://dashboard.wartkinurt.pl` is represented correctly in production Traefik/Docker configuration.
- Dashboard assets are included in production builds and served through Laravel/Nginx.
- Admin login path and required setup are documented and verified as far as the local environment allows.
- Every repository `README.md` is written in English.
- Focused automated checks pass or any environment blockers are documented with exact details.
