# KayakapMD
`Started: 12-17-2025`

A web-based consultation system.

## Requirements
1. PHP ^8.2
2. Composer (https://getcomposer.org/)
3. NodeJS (https://nodejs.org/en)

## Setup
1. Place project inside XAMPP htdocs (If using XAMPP).

2. Install dependencies.
```bash
composer install
```

3. Install npm packages
```bash
npm i
```

4. Copy default environment.
```bash
cp .env.example .env
```

5. Setup environment.
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1           # or localhost
DB_PORT=3306
DB_DATABASE=econsultation
DB_USERNAME=root            # XAMPP/database root user
DB_PASSWORD=                # Databsae user password
```

6. Generate a key.
```bash
php artisan key:generate
```

## How to run
1. On a terminal, serve the app (If not using XAMPP)
```
php artisan serve
```

2. On another terminal run npm
```
npm run dev
```
