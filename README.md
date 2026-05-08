# Weesia FibPath Analyzer - Laravel

Laravel monolith version of Weesia FibPath Analyzer.

## Setup

```bash
composer install
npm install
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

## Demo Login

- Admin: `admin@weesia.local` / `password123`
- User: `user@weesia.local` / `password123`

## Routes

- `/` landing page
- `/login` login page
- `/admin` admin upload/status dashboard
- `/user` user signal dashboard

Admin can upload chart images, ticker, TP1, optional TP2, and SL. User can view the same signals and open chart images fullscreen.
