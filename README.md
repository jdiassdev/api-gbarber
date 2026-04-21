# 💈 API minimalista para barbearias

Base URL: `http://localhost:8000/api`

---

## 🔐 Auth

| Method | Endpoint    | Description                      | Auth |
| ------ | ----------- | -------------------------------- | ---- |
| `POST` | `/register` | Cadastrar novo usuário           | ✗    |
| `POST` | `/login`    | Autenticar e obter Bearer token  | ✗    |
| `POST` | `/logout`   | Invalidar Bearer token           | ✓    |
| `GET`  | `/profile`  | Pegar dados pessoais para perfil | ✓    |

---

## ✂️ Serviços

| Method | Endpoint    | Description     | Auth |
| ------ | ----------- | --------------- | ---- |
| `GET`  | `/services` | Listar serviços | ✗    |

---

## 📅 Reservar / agendamento

| Method | Endpoint    | Description       | Auth |
| ------ | ----------- | ----------------- | ---- |
| `POST` | `/bookings` | Criar agendamento | ✓    |

---

## 💈 Barbeiros

| Method | Endpoint                     | Description          | Auth |
| ------ | ---------------------------- | -------------------- | ---- |
| `GET`  | `/barbers`                   | Listar barbeiros     | ✗    |
| `GET`  | `/barbers/{id}`              | Detalhes do barbeiro | ✗    |
| `GET`  | `/barbers/{id}/availability` | Agenda do barbeiro   | ✗    |
| `POST` | `/barbers`                   | Cadastrar barbeiro   | ✓    |

---

## 💈 Produtos

| Method | Endpoint                     | Description          | Auth |
| ------ | ---------------------------- | -------------------- | ---- |
| `GET`  | `/products`                   | Listar produtos     | ✗    |
| `POST` | `/products`                   | Cadastrar produto   | ✓    |

---

## 🔑 Legenda

| Símbolo | Significado                          |
| ------- | ------------------------------------ |
| `✓`     | Requer autenticação via Bearer Token |
| `✗`     | Endpoint público                     |

> Inclua o token no header: `Authorization: Bearer {token}`

---

_API para barbearia minimaista em Laravel_
