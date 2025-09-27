APP_CONTAINER=insider_app

# -------------------------------
# Docker Compose
# -------------------------------

up:
	docker compose up -d --build

down:
	docker compose down

restart: down up

logs:
	docker compose logs -f

# -------------------------------
# Laravel Artisan
# -------------------------------

bash:
	docker exec -it $(APP_CONTAINER) bash

migrate:
	docker exec -it $(APP_CONTAINER) php artisan migrate

fresh:
	docker exec -it $(APP_CONTAINER) php artisan migrate:fresh --seed

seed:
	docker exec -it $(APP_CONTAINER) php artisan db:seed

key-generate:
	docker exec -it $(APP_CONTAINER) php artisan key:generate

dispatch:
	docker exec -it $(APP_CONTAINER) php artisan messages:dispatch-pending

# -------------------------------
# Tests
# -------------------------------

test:
	docker exec -it $(APP_CONTAINER) php artisan test

test-filter:
	docker exec -it $(APP_CONTAINER) php artisan test --filter=$(name)

test-unit:
	docker exec -it $(APP_CONTAINER) php artisan test --testsuite=Unit

test-feature:
	docker exec -it $(APP_CONTAINER) php artisan test --testsuite=Feature

coverage:
	docker exec -it $(APP_CONTAINER) php artisan test --coverage --min=80

# -------------------------------
# Swagger
# -------------------------------

swagger-generate:
	docker exec -it $(APP_CONTAINER) php artisan l5-swagger:generate

# -------------------------------
# Logs
# -------------------------------

logs-app:
	docker exec -it $(APP_CONTAINER) tail -f storage/logs/laravel.log

# -------------------------------
# Queue
# -------------------------------

queue-work:
	docker exec -it $(APP_CONTAINER) php artisan queue:work --queue=messages --sleep=1 --tries=3

queue-stop:
	docker exec -it $(APP_CONTAINER) pkill -f "artisan queue:work" || true

queue-restart: queue-stop
	docker exec -it $(APP_CONTAINER) php artisan queue:work --queue=messages --sleep=1 --tries=3
