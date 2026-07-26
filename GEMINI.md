# GEMINI.md - Kayak Map Project Knowledge Base

This document provides a comprehensive overview of the Kayak Map project, its technical stack, architecture, and development conventions to be used as instructional context for future interactions.

## 1. Project Overview

**Kayak Map** is a full-stack, interactive web application for exploring and discovering kayak trails, with an initial focus on Poland. The project aims to be the most comprehensive and user-friendly resource for kayakers, providing detailed route data, hazard warnings, and a rich map interface.

### Core Objectives
- **Data Aggregation:** Collect and manage a large dataset of kayak trails (initially ~200 in Poland).
- **Safety:** Provide users with safety information and warnings about on-trail hazards or points of interest.
- **GPX Processing:** Process GPX files to add or modify trail data.
- **Future Integrations:** Plan for future integration with devices like GPS trackers and water-level monitors.

---

## 2. Technical Architecture

### 2.1. Technology Stack

*   **Backend:**
    *   **Framework:** Laravel 11 (PHP 8.2+)
    *   **Database:** MySQL/MariaDB with Spatial Extensions
    *   **Authentication:** Laravel Sanctum, JWT (`php-open-source-saver/jwt-auth`), Laravel Socialite (Google, Facebook)
    *   **API Documentation:** Swagger (`l5-swagger`)
    *   **Geospatial:** `matanyadaev/laravel-eloquent-spatial`, `kamz8/laravel-overpass`
    *   **File Processing:** `sibyx/phpgpx` for GPX parsing.

*   **Frontend:**
    *   **Framework:** Vue.js 3
    *   **UI:** Vuetify
    *   **State Management:** Vuex
    *   **Routing:** Vue Router 4
    *   **Maps:** Leaflet.js (`@vue-leaflet/vue-leaflet`)
    *   **HTTP Client:** Axios
    *   **Build Tool:** Vite (with multi-entry config for main app and dashboard)

*   **Development & DevOps:**
    *   **Containerization:** Docker & Docker Compose
    *   **Environments:** Separate configurations for development, staging, and production.
    *   **CI/CD:** Git workflow defined, with plans for automation.
    *   **Backups:** Scripts for creating and restoring encrypted database backups.

*   **External APIs:**
    *   **Geographic Data:** OpenStreetMap / Overpass API
    *   **Geocoding:** Nominatim
    *   **Weather:** A weather proxy service is integrated.

### 2.2. Building and Running the Project

The project is designed to be run within a Docker environment.

*   **First-Time Setup:** The `npm run setup` command is the primary entry point. It automates dependency installation, Docker environment startup, and database seeding with production data.
*   **Development Servers:**
    *   `docker-compose up -d` starts all necessary services (Nginx, PHP, Vite, DB, Redis).
    *   The main application is accessible via a local domain (e.g., `https://kayak-map.test`).
    *   The Vite HMR server runs within a container.
*   **Backend Commands:** Laravel Artisan commands should be executed inside the `app` container (e.g., `docker-compose exec app php artisan <command>`).
*   **Testing:**
    *   **Backend:** Pest for unit/feature tests (`composer test`).
    *   **Frontend:** Vitest for unit tests (`npm test`).

---

## 3. Database Schema

The database relies heavily on MySQL's spatial data types (`POINT`, `POLYGON`, `LINESTRING`) for geospatial queries.

### Key Models:
*   **`trails`**: The core model for kayak trails, including names, descriptions, length, difficulty, ratings, and start/end coordinates.
*   **`regions`**: A hierarchical structure for geographical areas (Country > State > City), using a `parent_id` for self-referencing. Stores `center_point` and `area` as spatial types.
*   **`points`**: Points of interest along a trail (e.g., warnings, campsites), linked to a `point_type`.
*   **`rivers`**: Stores the geographical path of a river as a `LINESTRING`.
*   **`river_tracks`**: Stores the specific GPS track for a `trail` as a JSON array of coordinates.
*   **`users`**: A comprehensive user model including profile information, preferences, and support for soft deletes.
*   **`social_accounts`**: Stores OAuth data for users logging in via social providers.
*   **`imageables` & `linkables`**: Polymorphic pivot tables allowing images and external links to be attached to various models like `Trail`, `Section`, and `Region`.

---

## 4. Frontend Architecture

The frontend is divided into two separate Single Page Applications (SPAs) but shares the same backend and Vite build process.

### 4.1. Main Application (`resources/js/app.js`)
*   **Modular Structure:** Code is organized into modules under `resources/js/modules/` (e.g., `auth`, `trails`, `regions`).
*   **Key Components:**
    *   `MapView.vue`: The main interactive map interface using Leaflet.
    *   `TrailPopup.vue`: Displays detailed information for a selected trail.
    *   `SidebarTrails.vue`: A panel with filters and a list of trail results.
*   **Custom Caching Plugin:** A Laravel-style caching system (`this.$cache.remember(...)`) is implemented for Vue. It supports Time-to-Live (TTL) and tag-based invalidation, using `localStorage` as the backend.
*   **Global Helpers:** Global helper functions are available (e.g., `$alertInfo`, `$formatDate`).

### 4.2. Dashboard SPA (`resources/js/modules/dashboard/main.js`)
*   **Separate Entry Point:** The dashboard is a distinct Vue application, loaded on the `/dashboard` route.
*   **Purpose:** Provides admin-only functionality for managing trails, users, and system settings (CRUD operations).
*   **Custom UI Kit:** The dashboard uses a bespoke UI component kit found in `resources/js/dashboard/components/ui/`. **Development standard requires using these components (e.g., `<UiButton>`, `<UiDataTable>`) instead of raw Vuetify components.** This kit is inspired by `shadcn/ui`.
*   **Authentication:** The dashboard has its own JWT authentication flow and token storage, but it communicates with the same backend API.

---

## 5. Backend Architecture

### 5.1. API Design
*   The API is versioned under `/api/v1/`.
*   It follows RESTful principles, using Laravel API Resources for consistent JSON responses.
*   **Key Endpoints:**
    *   `GET /api/v1/trails`: Fetches trails with bounding-box and attribute-based filtering.
    *   `GET /api/v1/trails/{slug}`: Retrieves a single trail.
    *   `GET /api/v1/regions`: Fetches the region hierarchy.
    *   `POST /api/v1/upload-gpx`: Endpoint for uploading GPX files.
    *   `POST /api/v1/geocoding/reverse`: Converts coordinates to region data.
    *   `GET /api/v1/search`: A full-text search endpoint.
*   **Dashboard API:** Protected endpoints under `/api/v1/dashboard/` for managing content, including polymorphic link management for different models.

### 5.2. Service Layer & Async Jobs
*   Business logic is abstracted into Service classes (e.g., `TrailService`, `GeocodingService`, `GpxProcessor`).
*   Long-running tasks, such as processing large GPX files or associating trails with regions, are handled asynchronously using Laravel's queue system with Redis as the driver.

---

## 6. Authentication System

The project features a robust, RFC 6749 compliant OAuth 2.0 refresh token system.

*   **Tokens:** Upon login, the client receives a short-lived `access_token` and a long-lived `refresh_token`.
*   **Token Rotation:** The refresh token is rotated with each use to enhance security.
*   **Automatic Refresh:** The frontend features a `TokenManager` integrated with Axios interceptors to handle token management automatically:
    1.  **Proactive Refresh:** A timer refreshes the token 5 minutes before it expires.
    2.  **Reactive Refresh:** If an API call returns a 401 Unauthorized error, the manager attempts to refresh the token and retries the original request.
    3.  **Request Queuing:** Concurrent requests are queued while a token refresh is in progress to prevent race conditions.
*   **Security:** On token refresh, user status (active/inactive) and permissions (ACL) are re-validated from the database.

---

## 7. Development Workflow & Standards

*   **Git Workflow:** The project follows a `GitFlow`-like branching model:
    *   `main`: Production-ready code.
    *   `develop`: Main development branch where features are merged.
    *   Branches: `feature/*`, `bugfix/*`, `release/*`, `hotfix/*`.
*   **Dashboard Coding Standard:** It is mandatory to use the custom UI kit from `resources/js/dashboard/components/ui/` for all new UI in the dashboard. This ensures visual consistency and encapsulates complex logic.
*   **DevOps:** A `Makefile` and `npm scripts` provide helpers for common tasks like setup (`npm run setup`), backups (`npm run db:backup`), and fresh installs (`npm run fresh`).

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.13
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/socialite (SOCIALITE) - v5
- laravel/dusk (DUSK) - v8
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v2
- phpunit/phpunit (PHPUNIT) - v10
- vue (VUE) - v3

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs
- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version-specific documentation.
- Laravel 11 brought a new streamlined file structure which this project now uses.

### Laravel 11 Structure
- In Laravel 11, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands
- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests that have a lot of duplicated data. This is often the case when testing validation rules, so consider this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>
</laravel-boost-guidelines>
