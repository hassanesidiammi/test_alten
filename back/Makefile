DC := docker-compose

.PHONY: up down shell logs

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
	$(DC) exec php chown -R www-data:www-data var/ config/jwt/
	@echo "Generating JWT keypair..."
	$(DC) exec php php bin/console lexik:jwt:generate-keypair --overwrite

