# 🚀 Prompt para geração de ambiente Laravel + Docker + Makefile

Quero que você atue como um engenheiro backend sênior especializado em Docker, Laravel e ambientes de desenvolvimento modernos.

Preciso que você crie um ambiente completo usando Docker Compose para um projeto Laravel com Inertia.js, com foco em desenvolvimento local.

O projeto será um sistema de análise de texto para detectar conteúdo gerado por IA (MVP), então a estrutura deve ser organizada, escalável e próxima de produção.

---

## 📦 Requisitos

### 1. Containers (Docker Compose)

O ambiente deve conter:

- PHP 8.3 com FPM  
- Nginx  
- MySQL 8  
- Redis (opcional, apenas para cache — NÃO usar para filas)  
- Node (para build do frontend com Vite/Inertia)

---

### 2. Estrutura de pastas

Organize de forma profissional:

    docker/
      nginx/
      php/
    src/
    Makefile
    docker-compose.yml

---

### 3. Arquivos necessários

Gere TODOS com código completo:

- docker-compose.yml  
- Dockerfile (PHP)  
- configuração do Nginx  
- .env.example  
- configuração do Redis (apenas cache, se incluído)  
- configuração do Vite/Inertia  

---

### 4. 🛠️ Makefile (OBRIGATÓRIO)

Crie um **Makefile completo e bem pensado** para gerenciar o projeto.

Ele deve conter comandos como:

    up        # sobe os containers
    down      # derruba os containers
    build     # builda containers
    install   # instala Laravel + dependências
    migrate   # roda migrations
    fresh     # migrate:fresh --seed
    seed      # roda seeders
    bash      # entra no container PHP
    npm       # instala dependências frontend
    dev       # roda Vite
    test      # roda testes

👉 O Makefile deve ser o **principal ponto de entrada do projeto**, evitando comandos diretos de docker sempre que possível.

---

### 5. ⚙️ Funcionalidades obrigatórias

O ambiente deve permitir:

- rodar `make up`  
- acessar o Laravel no navegador  
- rodar migrations via `make migrate`  
- rodar frontend com Vite  
- hot reload funcionando  

---

### 6. 🧠 Laravel já preparado

Configure o Laravel com:

- autenticação (Laravel Breeze + Inertia)  
- Redis configurado **apenas para cache (opcional)**  
- NÃO configurar filas (queues) ou workers  
- .env ajustado para ambiente Docker  

---

### 7. 🚫 Restrições importantes

- NÃO utilizar filas (queues)  
- NÃO implementar processamento assíncrono  
- Todas as requisições devem ser **síncronas**  
- Manter o projeto simples (MVP)  

---

### 8. 📘 Instruções

Inclua passo a passo claro para:

- subir o ambiente  
- instalar o projeto  
- rodar migrations  
- acessar no navegador  

---

### 9. ✅ Boas práticas

- evitar problemas de permissão  
- usar volumes corretamente  
- otimizar build do container  
- separar responsabilidades  
- usar nomes claros nos serviços  

---

### 10. ✉️ Extras (opcional, mas desejado)

- Mailpit (email local)  
- phpMyAdmin ou Adminer  

---

### 11. 🧾 Estilo da resposta

- Código completo (sem omitir partes importantes)  
- Pronto para copiar e rodar  
- Explicações curtas e objetivas  
- Organização clara por arquivos  