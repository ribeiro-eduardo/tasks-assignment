.PHONY: up down restart logs shell db test

up:
	docker compose up -d
	docker compose exec app composer install --quiet

down:
	docker compose down

restart:
	docker compose down
	docker compose up -d
	docker compose exec app composer install --quiet

logs:
	docker compose logs -f app

shell:
	docker compose exec app bash

db:
	docker compose exec db mysql -u app_user -psecret tasks_api

reset:
	docker compose down -v
	docker compose up -d
	docker compose exec app composer install --quiet

test:
	docker compose exec app vendor/bin/phpunit
