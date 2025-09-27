<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Insider Messaging Project

A **Laravel 10** based messaging system with repository-service architecture and Redis-backed job queues.

## ✨ Features
- Repository + Service layer pattern  
- Queue/Job system powered by Redis  
- Global rate limiting (max 2 messages every 5 seconds)  
- Sent messages marked in database  
- REST API endpoints to fetch sent messages with `messageId`  
- Swagger/OpenAPI documentation  
- Docker Compose setup (Laravel, PostgreSQL, Redis)  
- **Automated first-time setup** via `entrypoint.sh`  
- Makefile with handy developer commands  

---

## 🚀 Installation

### 1. Clone repository
```bash
git clone https://github.com/mgunesdev/insider-messaging.git
cd insider-messaging
```

### 2. Start Docker environment
```bash
docker compose up -d --build
```

Services:
- Laravel API: [http://localhost:8000](http://localhost:8000)  
- PostgreSQL: `localhost:5432` (user: postgres / pass: postgres)  
- Redis: `localhost:6379`

> `entrypoint.sh` automatically creates `.env`, runs `php artisan key:generate`, and performs migrations.

---

## 📨 Message Flow

1. `pending` messages are inserted into the `messages` table.  
2. Dispatch jobs:
   ```bash
   make dispatch
   ```
3. Start worker:
   ```bash
   make queue-work
   ```
4. **Rate limit:** maximum **2 messages per 5 seconds**.

---

## 📡 API Usage

### List sent messages
```http
GET /api/messages/sent
```

Sample response:
```json
{
  "data": [
    {
      "id": 1,
      "to": "+905555555555",
      "messageId": "abc-123",
      "sent_at": "2025-09-27T11:20:31Z"
    }
  ]
}
```

Swagger docs available at:  
[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

---

## 🧪 Testing

Run all tests:
```bash
make test
```

Run specific test:
```bash
make test-filter name=MessageSenderServiceTest
```

---

## 📦 Makefile Commands

```bash
# Docker
make up            # Start containers
make down          # Stop containers
make restart       # Restart containers
make logs          # Follow logs

# Laravel
make bash          # Enter container shell
make migrate       # Run migrations
make fresh         # Fresh migrate + seed
make seed          # Run database seeders
make key-generate  # Generate app key
make dispatch      # Dispatch pending messages

# Queue
make queue-work    # Start worker
make queue-stop    # Stop worker
make queue-restart # Restart worker

# Swagger
make swagger-generate # Generate docs

# Logs
make logs-app      # Tail application log

# Tests
make test          # Run all tests
make test-filter   # Run filtered test
```

---

## ⚙️ Notes
- Message max length can be configured in `.env`:
  ```env
  MSG_MAX_LENGTH=160
  ```
- Sent messages are cached in Redis:  
  `messages:sent:{id}`  
- Laravel Horizon can be added for queue monitoring:
  ```bash
  composer require laravel/horizon
  php artisan horizon
  ```
