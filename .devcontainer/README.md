# DevContainer For JetBrains IDEs

This DevContainer provides a complete Docker-based development environment for Kayak Map.

## Quick Start

1. Install the Dev Containers plugin in PhpStorm or another JetBrains IDE.
2. Open the project.
3. Choose `Reopen in Container` from the Dev Containers tool window.
4. Wait for the container build and post-create setup to finish.

## Included Services

- PHP 8.3 with required extensions.
- Composer dependencies.
- Node.js and NPM dependencies.
- MariaDB with project data.
- Redis.
- Vite development server.
- Nginx.
- PhpMyAdmin on `localhost:8081`.
- Xdebug 3 configured for PhpStorm.

## Forwarded Ports

- `3306` - MariaDB.
- `5173` - Vite.
- `8000` - Laravel serve.
- `8081` - PhpMyAdmin.
- `6379` - Redis.
- `80` and `443` - Nginx.

## Common Commands

Run commands in the IDE terminal inside the container:

```bash
php artisan migrate
php artisan test --compact
composer install
npm install
npm run dev
./dev-helper.sh help
```

## Xdebug

- Server name: `kayak-map-devcontainer`.
- Host: `host.docker.internal`.
- Port: `9003`.

Set a breakpoint in PHP code, start a debug session in PhpStorm, then open the application in the browser.

## Database Connection

```text
Host: mariadb
Port: 3306
Database: kayak_map
User: root
Password: admin123
```

## Performance Notes

- `vendor/` and `node_modules/` are stored in Docker volumes for better performance.
- Source code is mounted as a bind mount so changes are visible immediately.
- Allocate at least 8 GB RAM to Docker Desktop for a smoother experience.

## Troubleshooting

```bash
docker ps
docker-compose ps
docker-compose logs
```

If the container becomes inconsistent, use the IDE command `Dev Containers: Rebuild Container`.
