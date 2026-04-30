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

## 🧠 7. Funcionalidade principal: análise de texto (detecção de IA)

Implemente a base funcional do sistema de análise de texto.

### 📌 Endpoint

Criar endpoint protegido por autenticação:

    POST /analyze

---

### 📥 Input

    {
      "text": "string"
    }

---

### 📤 Output esperado

    {
      "ai_score": 0.0-1.0,
      "classification": "human | ai | inconclusive",
      "explanation": [
        "motivo 1",
        "motivo 2"
      ]
    }

---

### 🧩 Service de análise

Criar uma classe:

    App\Services\AiDetectionService

Responsabilidades:

- Receber o texto
- Realizar análise (mock inicial OU integração com API externa)
- Retornar score + classificação + explicação

---

### 🧮 Lógica do MVP

- Gerar um score entre 0 e 1
- Classificar com base no score:

    0.0 – 0.4 → human  
    0.4 – 0.7 → inconclusive  
    0.7 – 1.0 → ai  

---

### 💬 Explicação

Gerar explicações simples e interpretáveis, como:

- "Baixa variação de vocabulário"
- "Estrutura muito previsível"
- "Frases com padrão repetitivo"

---

### 💾 Persistência

Criar tabela:

    analyses

Campos:

    id
    user_id
    text
    ai_score
    classification
    explanation (json)
    created_at

---

### 🔐 Autenticação

- Usuário deve estar autenticado para realizar análise  
- Relacionar cada análise ao usuário  

---

### 🖥️ Interface (Inertia)

Criar páginas:

**Dashboard**
- textarea para input
- botão "Analisar"

**Resultado**
- score
- classificação
- explicação

**Histórico**
- lista de análises do usuário

---

## 🚫 8. Restrições importantes

- NÃO utilizar filas (queues)  
- NÃO implementar processamento assíncrono  
- Todas as requisições devem ser síncronas  
- Manter o projeto simples (MVP)  

---

## 📘 9. Instruções

Inclua passo a passo claro para:

- subir o ambiente  
- instalar o projeto  
- rodar migrations  
- acessar no navegador  

---

## ✅ 10. Boas práticas

- evitar problemas de permissão  
- usar volumes corretamente  
- otimizar build do container  
- separar responsabilidades  
- usar nomes claros nos serviços  

---

## ✉️ 11. Extras (opcional, mas desejado)

- Mailpit (email local)  
- phpMyAdmin ou Adminer  

---

## 🧾 12. Estilo da resposta

- Código completo (sem omitir partes importantes)  
- Pronto para copiar e rodar  
- Explicações curtas e objetivas  
- Organização clara por arquivos  