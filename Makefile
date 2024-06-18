.PHONY: up-dev up-down run-dev

up-dev:
	@./rebuild.dev.sh

up-down:
	@docker compose -f docker-compose.dev.yml down

run-dev:
	@php artisan serve

analyse:
	@./vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=2G

migrate:
	@php artisan migrate

seed:
	@php artisan db:seed

tinker:
	@php artisan tinker

start-schedule:
	@php artisan schedule:run

clear:
	@php artisan cache:clear
	@php artisan config:clear
	@php artisan route:clear

