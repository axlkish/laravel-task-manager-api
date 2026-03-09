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
- Full-text search with Meilisearch
- Clean Laravel backend architecture

### Running the Project

Start the development environment:

`docker compose up -d`

Install dependencies:

`docker compose exec app composer install`

Run migrations:

`docker compose exec app php artisan migrate`

### API Endpoint Example

Health check endpoint:

`GET /api/v1/health`

Response:

`{
"status": "ok"
}`

### Development

Clear Laravel caches:

`docker compose exec app php artisan optimize:clear`

Run tests:

`docker compose exec app php artisan test`
