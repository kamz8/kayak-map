# Tests

This directory contains backend tests for the Laravel API, dashboard services, release configuration, and focused regression coverage.

## Main Test Areas

- `tests/Unit` - unit and configuration tests.
- `tests/Feature` - API and application behavior tests.
- `tests/vitest` - frontend dashboard tests powered by Vitest.

## Dashboard Links API Coverage

The dashboard links API is covered by unit and feature tests for polymorphic links attached to trails and sections.

Relevant files:

- `database/factories/LinkFactory.php`
- `tests/Unit/Services/Dashboard/LinkServiceTest.php`
- `tests/Feature/Api/V1/Dashboard/TrailLinksControllerTest.php`
- `tests/Feature/Api/V1/Dashboard/SectionLinksControllerTest.php`

Covered behavior includes listing, creating, updating, deleting, ownership validation, and shared link relationships.

## Release Configuration Tests

The dashboard production release uses focused unit tests for configuration files:

```bash
php artisan test --compact tests/Unit/DashboardProductionConfigTest.php
php artisan test --compact tests/Unit/ViteDockerConfigTest.php
php artisan test --compact tests/Unit/ReadmeLanguageTest.php
```

These tests guard production dashboard routing, Vite/Nginx HMR configuration, and English README content.

## Running Tests

```bash
php artisan test --compact
php artisan test --compact tests/Unit/DashboardProductionConfigTest.php
php artisan test --compact --filter=LinkServiceTest
php artisan test --compact --filter=TrailLinksControllerTest
php artisan test --compact --filter=SectionLinksControllerTest
```

## Test Database

`phpunit.xml` configures the test database and test cache driver. If you run database-backed tests locally, make sure the configured MySQL/MariaDB test database exists and Redis is available when migrations require it.

Useful commands:

```bash
php artisan db:wipe --database=mysql_testing --force
php artisan migrate --database=mysql_testing --force
```

## Troubleshooting

- Table already exists: wipe the test database with `php artisan db:wipe --database=mysql_testing --force`.
- Redis connection failed: start Redis with Docker or use the project Docker Compose stack.
- Database connection refused: confirm the database service is running and the credentials in `phpunit.xml` match the local service.

## Notes For Developers

- Use Pest syntax for new PHP tests.
- Prefer focused tests with `php artisan test --compact <path>` while developing.
- Use factories for database data.
- Use specific response assertions such as `assertForbidden()` and `assertNotFound()`.
- Run `vendor/bin/pint --dirty` before committing PHP changes.
