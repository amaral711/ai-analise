# PROGRESS — analise-ai

## Status: Infraestrutura Docker criada — pronto para rodar

---

## Configuracoes definidas

- Frontend: **Vue 3** + Inertia.js (via Laravel Breeze)
- Backend: **Laravel 13** + PHP 8.3-FPM
- Banco: **MySQL 8** — database: `analise_ai`, user: `analise`, password: `secret`
- Cache: **Redis 7** (somente cache, sem filas)
- Filas: **sync** (sem workers)
- Sessoes: **file**
- Dominio local: **http://app.localhost.com** (porta 80)
- Vite HMR: **http://localhost:5173**

---

## Implementacao

- [x] Estrutura de pastas (`docker/nginx/`, `docker/php/`)
- [x] `docker-compose.yml` (app, nginx, mysql, redis, node com profile `dev`)
- [x] `docker/php/Dockerfile` (PHP 8.3-FPM + extensoes + Composer 2)
- [x] `docker/php/php.ini`
- [x] `docker/nginx/default.conf` (server_name: app.localhost.com)
- [x] `docker/vite.config.js` (template com HMR para Docker)
- [x] `.dockerignore`
- [x] `Makefile` (up, down, build, install, migrate, fresh, seed, bash, npm, dev, test, logs, ps, permissions)
- [x] `.env.example` atualizado para Docker (DB_HOST=mysql, REDIS_HOST=redis, CACHE_STORE=redis, QUEUE_CONNECTION=sync)
- [x] `README.md` criado com instrucoes completas
- [x] `make install` executado — corrigido conflito PHP 8.3→8.4 e Tailwind 3→4
- [x] `make migrate` executado (3 migrations aplicadas)
- [x] App respondendo HTTP 200 em http://app.localhost.com
- [x] Rotas /login e /register funcionando
- [ ] `make dev` — Vite hot reload (rodar para desenvolvimento ativo)

---

## Como rodar (passo a passo)

```bash
# 1. Buildar e subir os containers
make build
make up

# 2. Instalar dependencias + Breeze + Inertia + Vue
make install

# 3. Rodar as migrations
make migrate

# 4. Acessar no navegador
# http://app.localhost.com

# 5. Iniciar Vite (hot reload) — terminal separado
make dev
```

> `app.localhost.com` resolve automaticamente no Chrome/Firefox sem editar /etc/hosts.

---

## Servicos Docker

| Servico | Container        | Porta |
|---------|-----------------|-------|
| PHP-FPM | analise_app     | 9000 (interno) |
| Nginx   | analise_nginx   | 80    |
| MySQL   | analise_mysql   | 3306  |
| Redis   | analise_redis   | — (interno) |
| Node/Vite | analise_node  | 5173 (profile: dev) |
