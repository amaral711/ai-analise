# Analise AI

Sistema de análise de texto para detecção de conteúdo gerado por IA.

**Stack:** Laravel 13 · PHP 8.3 · Vue 3 · Inertia.js · MySQL 8 · Redis · Docker

---

## Pré-requisitos

- [Docker](https://docs.docker.com/get-docker/) + Docker Compose
- Make

---

## Configuração inicial

### 1. /etc/hosts

Adicione a entrada abaixo para acessar o projeto pelo domínio local:

```bash
sudo sh -c 'echo "127.0.0.1 app.localhost.com" >> /etc/hosts'
```

### 2. Buildar e subir os containers

```bash
make build
make up
```

### 3. Instalar dependências e configurar o projeto

```bash
make install
```

Este comando executa automaticamente:
- Copia `.env.example` → `.env`
- Instala dependências PHP via Composer
- Gera a `APP_KEY`
- Instala Laravel Breeze + Inertia.js (Vue 3)
- Instala dependências JS (npm)
- Cria o link de storage

### 4. Rodar as migrations

```bash
make migrate
```

### 5. Acessar no navegador

```
http://app.localhost.com
```

---

## Desenvolvimento

### Iniciar o Vite (hot reload)

Em um terminal separado:

```bash
make dev
```

O servidor Vite ficará disponível em `http://localhost:5173` e o hot reload funcionará automaticamente em `http://app.localhost.com`.

---

## Comandos disponíveis

```bash
make help          # Lista todos os comandos
```

| Comando            | Descrição                             |
|--------------------|---------------------------------------|
| `make build`       | Builda as imagens Docker              |
| `make up`          | Sobe os containers                    |
| `make down`        | Derruba os containers                 |
| `make restart`     | Reinicia os containers                |
| `make install`     | Instala dependências + configura app  |
| `make migrate`     | Roda as migrations                    |
| `make fresh`       | migrate:fresh --seed                  |
| `make seed`        | Roda os seeders                       |
| `make bash`        | Shell no container PHP                |
| `make tinker`      | Laravel Tinker                        |
| `make npm`         | Instala dependências JS               |
| `make dev`         | Inicia o Vite com hot reload          |
| `make test`        | Roda os testes                        |
| `make logs`        | Exibe logs dos containers             |
| `make ps`          | Lista containers em execução          |
| `make permissions` | Corrige permissões de storage         |

---

## Serviços Docker

| Serviço   | Container       | Acesso                      |
|-----------|-----------------|-----------------------------|
| PHP-FPM   | analise_app     | interno (porta 9000)        |
| Nginx     | analise_nginx   | http://app.localhost.com    |
| MySQL     | analise_mysql   | localhost:3306              |
| Redis     | analise_redis   | interno                     |
| Node/Vite | analise_node    | localhost:5173 (`make dev`) |

### Credenciais do banco (desenvolvimento)

| Campo    | Valor        |
|----------|--------------|
| Host     | `mysql`      |
| Database | `analise_ai` |
| Username | `analise`    |
| Password | `secret`     |
| Root pw  | `root`       |

---

## Resolução de problemas

**Permissões de storage:**
```bash
make permissions
```

**Containers não sobem / erro de porta 80:**
```bash
sudo lsof -i :80
make down && make up
```

**Erro de conexão com banco após `make up`:**

O MySQL demora alguns segundos para inicializar. Aguarde e tente novamente:
```bash
make migrate
```

**Recriar tudo do zero:**
```bash
make down
docker volume rm analise-ai_mysql_data analise-ai_redis_data
make build && make up && make install && make migrate
```
