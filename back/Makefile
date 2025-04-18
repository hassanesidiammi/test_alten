DC := docker-compose

.PHONY: up down shell logs jwt init

up:
	$(DC) up -d --build

down:
	$(DC) down

shell:
	$(DC) exec php bash

logs:
	$(DC) logs -f php

jwt:
	@echo "Fixing permissions..."
	$(DC) exec php mkdir config/jwt/ || echo 'Continue...'
	$(DC) exec php chown -R www-data:www-data var/ config/jwt/
	@echo "Generating JWT keypair..."
	$(DC) exec php php bin/console lexik:jwt:generate-keypair --no-interaction --overwrite

migrate:
	@echo "Running migrations..."
	$(DC) exec php php bin/console doctrine:migrations:migrate --no-interaction

migrate-test:
	@echo "Running migrations for test env..."
	$(DC) exec php php bin/console doctrine:database:create --env=test --if-not-exists || true
	$(DC) exec php php bin/console doctrine:migrations:migrate --env=test --no-interaction

install:
	@echo "Installing composer dependencies..."
	$(DC) exec php composer install

init: install migrate migrate-test jwt

test:
	$(DC) exec php bin/phpunit

reset-db:
	$(DC) exec php bin/console doctrine:database:drop --force --env test
	$(DC) exec php bin/console doctrine:database:create --env test
	$(DC) exec php bin/console doctrine:schema:drop --force --env dev
	$(DC) exec php bin/console doctrine:schema:create --env dev
	$(DC) exec php php bin/console doctrine:migrations:migrate --no-interaction
	$(DC) exec php php bin/console doctrine:migrations:migrate --env=test --no-interaction
