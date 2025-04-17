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
