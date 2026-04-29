DC       = docker compose
APP      = $(DC) exec app
ARTISAN  = $(APP) php artisan

.PHONY: help up down build restart install setup migrate fresh seed \
        bash tinker npm dev test logs ps permissions

help:
	@echo ""
	@echo "  make build       Builda as imagens Docker"
	@echo "  make up          Sobe os containers"
	@echo "  make down        Derruba os containers"
	@echo "  make restart     Reinicia os containers"
	@echo "  make install     Instala dependencias PHP + JS + configura Breeze/Inertia"
	@echo "  make migrate     Roda as migrations"
	@echo "  make fresh       migrate:fresh --seed"
	@echo "  make seed        Roda os seeders"
	@echo "  make bash        Shell no container PHP"
	@echo "  make tinker      Laravel Tinker"
	@echo "  make npm         Instala dependencias JS"
	@echo "  make dev         Inicia o Vite (hot reload)"
	@echo "  make test        Roda os testes"
	@echo "  make logs        Exibe logs dos containers"
	@echo "  make ps          Lista containers"
	@echo "  make permissions Corrige permissoes de storage"
	@echo ""

## ─── Infra ───────────────────────────────────────────────────────────────────

build:
	$(DC) build --no-cache

up:
	$(DC) up -d

down:
	$(DC) down

restart:
	$(DC) restart

## ─── Aplicação ───────────────────────────────────────────────────────────────

install:
	@echo ">>> Configurando .env..."
	@cp -n .env.example .env || true
	@echo ">>> Instalando dependencias PHP..."
	$(DC) run --rm --no-deps app composer install
	@echo ">>> Gerando APP_KEY..."
	$(DC) run --rm --no-deps app php artisan key:generate --force
	@echo ">>> Instalando Laravel Breeze + Inertia (Vue)..."
	$(DC) run --rm --no-deps app composer require laravel/breeze --dev
	$(DC) run --rm --no-deps app php artisan breeze:install vue --no-interaction
	@echo ">>> Aplicando vite.config.js otimizado para Docker..."
	cp docker/vite.config.js vite.config.js
	@echo ">>> Instalando dependencias JS..."
	$(DC) run --rm --no-deps node npm install
	@echo ">>> Criando link de storage..."
	$(DC) run --rm --no-deps app php artisan storage:link
	@echo ""
	@echo "✓ Instalacao concluida. Rode: make migrate && make dev"

migrate:
	$(ARTISAN) migrate

fresh:
	$(ARTISAN) migrate:fresh --seed

seed:
	$(ARTISAN) db:seed

## ─── Shell ───────────────────────────────────────────────────────────────────

bash:
	$(DC) exec app bash

tinker:
	$(ARTISAN) tinker

## ─── Frontend ────────────────────────────────────────────────────────────────

npm:
	$(DC) run --rm --no-deps node npm install

dev:
	$(DC) --profile dev up node

## ─── Testes ──────────────────────────────────────────────────────────────────

test:
	$(ARTISAN) test

## ─── Utilitários ─────────────────────────────────────────────────────────────

logs:
	$(DC) logs -f

ps:
	$(DC) ps

permissions:
	$(APP) chmod -R 775 storage bootstrap/cache
	$(APP) chown -R www-data:www-data storage bootstrap/cache
