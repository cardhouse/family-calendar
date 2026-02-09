# Family Calendar

## Requirements
- PHP 8.4+
- Composer
- Node.js + npm
- SQLite (default) or another database configured in `.env`

## Local Setup

### Quick Start
1. Clone this repository.
2. Run `composer setup`.
3. Run `composer dev`.

### Manual Steps
1. Clone this repository.
2. Run `composer install`.
3. Copy environment config: `cp .env.example .env`.
4. Generate the app key: `php artisan key:generate`.
5. Run migrations: `php artisan migrate`.
6. Install frontend dependencies: `npm install`.
7. Build assets: `npm run build`.
8. Run `composer dev`.

## Helpful Commands
- `composer lint`
- `php artisan test --compact`
