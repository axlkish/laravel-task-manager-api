## Task Manager API

A REST API backend for a task management system built with Laravel 12.

This project demonstrates a modern Laravel backend architecture including authentication, authorization, queues, caching, search, and API documentation.

The goal of this repository is to showcase best practices for building scalable backend APIs using Laravel.

### Tech Stack

- Laravel 12
- PHP 8.3
- PostgreSQL
- Redis
- Meilisearch
- Docker
- Nginx
- Mailpit

### Features

- REST API architecture
- API versioning (/api/v1)
- Docker development environment
- PostgreSQL database
- Redis caching and queues
- Sending notification emails
- Full-text search with Meilisearch
- Clean Laravel backend architecture
- Tests

### Running the Project

Start the development environment:

`docker compose up -d --build`

Install dependencies:

`docker compose exec app composer install`

Run migrations:

`docker compose exec app php artisan migrate`

### Development

Clear Laravel caches:

`docker compose exec app php artisan optimize:clear`

Run queue worker (mail send)

`docker compose exec app php artisan queue:work`

Refresh Database:

`docker compose exec app php artisan migrate:fresh --seed`

Run tests:

`docker compose exec app php artisan test`

### Demo Credentials

Email: admin@example.com  
Password: password

### API

Local Base URL

http://localhost:8000/api/v1

#### Full API documentation available at:

Github URL

https://axlkish.github.io/laravel-task-manager-api/

Localhost URL

http://localhost:8000/api/docs
