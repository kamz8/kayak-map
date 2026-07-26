# DevOps

This directory contains setup, backup, restore, and deployment helpers for Kayak Map.

## Encrypted Database Backup

The repository can include an encrypted database backup for sharing non-user production geography data with developers.

- Encryption: AES-256-CBC with salt.
- Default backup password: `kayak2024!backup#secure`.
- User data is not included; the backup is intended for trails, regions, points, and related geography data.
- Plain SQL dumps are ignored by Git.

## Quick Start

```bash
git clone <repo-url>
cd kayak-map
npm run setup
```

Alternative:

```bash
make setup
```

## Available Commands

### NPM Scripts

```bash
npm run setup        # Full project setup
npm run fresh        # Clean local setup
npm run fresh:deep   # Clean setup and remove node_modules/vendor
npm run db:backup    # Create encrypted database backup
npm run db:restore   # Restore encrypted database backup
npm run db:test      # Test restore in an isolated database
npm run db:cleanup   # Clean test restore artifacts
```

### Makefile

```bash
make setup           # Full project setup
make fresh           # Clean local setup
make db-backup       # Create database backup
make db-restore      # Restore database backup
make db-test         # Test restore workflow
make status          # Show project/container status
make help            # Show available commands
```

## Directory Structure

```text
devops/
├── database/         # Backup, restore, and cleanup scripts
├── docker/           # Docker-specific notes
├── setup/            # Local setup scripts
└── README.md         # This file
```

## Backup Workflow

```bash
npm run db:backup
git add database/backups/production_data.sql.enc
git commit -m "Update encrypted database backup"
```

## Restore Workflow

```bash
npm run db:restore
```

The restore script decrypts the backup, imports it into the Docker database, and prints basic import statistics.

## New Developer Workflow

1. Clone the repository.
2. Run `npm run setup`.
3. Start frontend development with `npm run dev`.
4. Use `php artisan`, `composer`, or `./dev-helper.sh` depending on local setup.

## System Requirements

- Docker and Docker Compose.
- PHP 8.3+ if running PHP locally.
- Composer 2+ if running Composer locally.
- Node.js and NPM.
- OpenSSL for backup encryption and decryption.

## Production Dashboard

The production dashboard is exposed through Traefik at `https://dashboard.wartkinurt.pl`. The production Compose file routes this host to the Nginx service and mounts `docker/nginx/laravel.conf` as the Nginx default server config.

## Troubleshooting

```bash
docker-compose logs
docker-compose down
docker-compose up -d
```

If backup restore fails, confirm that `database/backups/production_data.sql.enc` exists and that the backup password in the restore script matches the backup password used during export.
