# GBarber API

API REST para gerenciamento de barbearias — agendamentos, barbeiros, serviços e produtos.

> **Projeto de aprendizado.** O objetivo principal é aprofundar conhecimentos em Laravel, PHP moderno, arquitetura de APIs e boas práticas de desenvolvimento backend. Não é um produto final.

---

## Sobre o Projeto

O GBarber nasceu da vontade de construir algo com domínio real enquanto aprendia. Uma barbearia tem regras de negócio interessantes: agendamentos com risco de conflito, papéis distintos (cliente, barbeiro, admin), disponibilidade de horários e gestão de serviços.

A ideia foi usar o framework ao máximo sem over-engineering — sem microserviços, sem abstrações desnecessárias. Monólito simples, direto ao ponto.

---

## O que tentei aprender e fazer

- **Arquitetura em camadas** — separar responsabilidades entre `Routes → FormRequest → Controller → Service → Model`
- **Controle de concorrência** — resolver o problema de double-booking usando `DB::transaction` com `lockForUpdate`, evitando que dois agendamentos simultâneos ocupem o mesmo horário
- **JWT sem Sanctum** — autenticação stateless com `php-open-source-saver/jwt-auth`, middleware customizado e propagação de identidade via `Request::attributes`
- **Enums backed (PHP 8.1+)** — substituir strings hardcoded (`'barber'`, `'pending'`) por tipos seguros com `BookingStatusEnum` e `UserRoleEnum`
- **ULIDs como chave primária** — IDs legíveis, ordenáveis e sem colisão, usando `HasUlids` do Eloquent
- **Laravel Resources** — controle explícito do que é exposto na API, com relações condicionais via `whenLoaded`
- **Testes com Pest PHP** — cobertura dos fluxos críticos de agendamento com banco real (SQLite in-memory), sem mocks excessivos
- **Query Builder para queries complexas** — disponibilidade de barbeiros com `GROUP_CONCAT` e agrupamento por data
- **Paginação** — resposta paginada com `per_page` configurável pelo cliente

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Runtime | PHP 8.3 |
| Framework | Laravel 12 |
| Banco de dados | MySQL |
| Autenticação | JWT (`php-open-source-saver/jwt-auth`) |
| Testes | Pest PHP |
| IDs | ULID |
| Cache/Filas | Redis *(integração futura)* |
| Storage | AWS S3 *(integração futura)* |

---

## Pré-requisitos

- PHP >= 8.3
- Composer
- MySQL >= 8.0
- Extensões PHP: `pdo_mysql`, `mbstring`, `openssl`

---

## Como rodar

### 1. Clonar e instalar dependências

```bash
git clone <url-do-repositorio>
cd api-gbarber
composer install
```

### 2. Configurar o ambiente

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Edite o `.env` com suas credenciais de banco:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gbarber
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

### 3. Rodar as migrations

```bash
php artisan migrate
```

### 4. Iniciar o servidor

```bash
php artisan serve
```

A API estará disponível em `http://localhost:8000`.

---

## Rodando os testes

Os testes usam SQLite in-memory — não precisam de banco configurado.

```bash
php artisan test
```

```
Tests: 21 passed (35 assertions)
```

---

## Endpoints da API

Base URL: `/api`

### Autenticação

| Método | Rota | Auth | Descrição |
|--------|------|------|-----------|
| `POST` | `/auth/register` | Não | Cadastrar usuário |
| `POST` | `/auth/login` | Não | Login, retorna JWT |
| `GET` | `/auth/profile` | Sim | Dados do usuário logado |
| `POST` | `/auth/logout` | Sim | Invalidar token |

**Register** — body:
```json
{
  "name": "João Silva",
  "email": "joao@email.com",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

**Login** — body:
```json
{
  "email": "joao@email.com",
  "password": "senha123"
}
```

> Rotas com **Auth: Sim** exigem o header `Authorization: Bearer {token}`.

---

### Barbeiros

| Método | Rota | Auth | Descrição |
|--------|------|------|-----------|
| `GET` | `/barbers` | Não | Listar barbeiros ativos |
| `GET` | `/barbers/{id}` | Não | Detalhes de um barbeiro |
| `GET` | `/barbers/{id}/availability?month=2025-06` | Não | Horários ocupados do mês |
| `POST` | `/barbers` | Sim | Promover usuário a barbeiro |

**Availability** — retorna os dias e horários já ocupados no mês:
```json
{
  "data": {
    "availability": [
      { "booking_date": "2025-06-10", "booking_time": ["09:00", "10:00"] },
      { "booking_date": "2025-06-11", "booking_time": ["14:00"] }
    ]
  }
}
```

---

### Agendamentos

| Método | Rota | Auth | Descrição |
|--------|------|------|-----------|
| `POST` | `/bookings` | Sim | Criar agendamento |
| `GET` | `/bookings/{id}` | Não | Detalhes de um agendamento |
| `GET` | `/bookings/me/list` | Sim | Meus agendamentos (cliente ou barbeiro) |
| `PATCH` | `/bookings/{id}/cancel` | Sim | Cancelar agendamento |

**Criar agendamento** — body:
```json
{
  "barber_id": "01jt...",
  "service_id": 1,
  "booking_date": "2025-06-15",
  "booking_time": "10:00"
}
```

**Status possíveis:** `pending` → `confirmed` → `completed` / `canceled`

**Regras de negócio:**
- Não é possível agendar no mesmo horário/barbeiro — retorna `409 Conflict`
- Horários de bookings cancelados ficam disponíveis novamente
- Só o dono do agendamento ou o barbeiro podem cancelar
- Não é possível cancelar um booking `completed` ou já `canceled`
- `me/list` retorna visão diferente conforme o papel: cliente vê barbeiro e serviço; barbeiro vê cliente e serviço
- Paginação via `?per_page=N` (padrão: 15)

---

### Serviços

| Método | Rota | Auth | Descrição |
|--------|------|------|-----------|
| `GET` | `/services` | Não | Listar serviços ativos |

---

### Produtos

| Método | Rota | Auth | Descrição |
|--------|------|------|-----------|
| `GET` | `/products` | Não | Listar produtos |
| `POST` | `/products` | Sim | Cadastrar produto |

---

## Formato padrão das respostas

**Sucesso:**
```json
{
  "message": "Agendamento criado",
  "status": 201,
  "data": { ... }
}
```

**Erro:**
```json
{
  "message": "Horário já está ocupado",
  "status": 409,
  "errors": {}
}
```

---

## Estrutura do projeto

```
app/
├── Enum/
│   ├── BookingStatusEnum.php   # pending, confirmed, canceled, completed
│   └── UserRoleEnum.php        # client, barber, admin
├── Exceptions/
│   └── BookingConflictException.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/               # Login, Register, Profile, Logout
│   │   ├── Barbers/            # Listagem, promoção, disponibilidade
│   │   ├── Bookings/           # Criar, ver, cancelar, listar meus agendamentos
│   │   ├── Products/
│   │   └── Services/
│   ├── Middleware/
│   │   └── JwtMiddleware.php
│   ├── Requests/               # Validação por domínio (FormRequests)
│   └── Resources/
│       └── BookingResource.php
├── Models/
│   ├── Booking.php
│   ├── Service.php
│   └── User.php
└── Services/
    └── BookingService.php      # Criação de booking com controle de concorrência
```

---

## Decisões técnicas

**Por que JWT e não Sanctum?**
Sanctum é ótimo para SPAs com cookie ou tokens simples de API. Para uma API consumida por um React desacoplado, JWT stateless é mais direto, sem dependência de sessão ou CSRF.

**Por que não Repository Pattern?**
Com um banco só e em fase de MVP, o Repository adiciona indireção sem benefício real. `Service + Eloquent direto` resolve sem cerimônia — se o projeto crescer e precisar de múltiplas fontes de dados, aí faz sentido.

**Por que ULID e não UUID?**
ULIDs são ordenáveis por tempo, o que melhora performance em índices B-tree e facilita debug — dá pra ordenar registros só pelo ID sem precisar de `created_at`.

**Por que `lockForUpdate` no BookingService?**
A verificação de conflito de horário é um clássico problema de race condition. Sem lock, duas requisições simultâneas passam no `exists()` e ambas criam o agendamento. O `lockForUpdate` dentro de uma `DB::transaction` garante que apenas uma delas prossegue.

---

## Próximos passos

- [ ] Cache de disponibilidade com Redis
- [ ] Notificações (e-mail/push) ao criar/cancelar agendamento
- [ ] Upload de foto do barbeiro via AWS S3
- [ ] Painel admin com métricas
- [ ] Rate limiting nos endpoints públicos
- [ ] Testes para Auth e Barbers
