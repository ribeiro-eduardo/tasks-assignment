# Tasks Assignment — Vanilla PHP REST API

A framework-free PHP REST API with Users and Tasks domains, layered architecture, and PDO-based data access.

## Requirements

- Docker & Docker Compose

## Quick Start

```bash
make up        # Start containers, install Composer dependencies
make reset     # Full reset: destroy DB volume, rebuild from schema
make test      # Run PHPUnit tests
```

The API is live at **http://localhost:8080** and the frontend at **http://localhost:8080/app.html**.

## API Endpoints

### Users

| Method | URL             | Description    |
|--------|-----------------|----------------|
| GET    | `/users`        | List all users |
| GET    | `/users/{id}`   | Get user by ID |
| POST   | `/users`        | Create user    |
| PUT    | `/users/{id}`   | Update user    |
| DELETE | `/users/{id}`   | Soft delete    |

**Request body (POST/PUT):**

```json
{
    "name": "John Doe",
    "email": "john@example.com"
}
```

### Tasks

| Method | URL             | Description                                 |
|--------|-----------------|---------------------------------------------|
| GET    | `/tasks`        | List all tasks (optional `?status=` filter) |
| GET    | `/tasks/{id}`   | Get task by ID                              |
| POST   | `/tasks`        | Create task                                 |
| PUT    | `/tasks/{id}`   | Update task                                 |
| DELETE | `/tasks/{id}`   | Soft delete                                 |

**Request body (POST/PUT):**

```json
{
    "title": "Finish the API",
    "description": "Optional description text",
    "status": "todo",
    "id_assigned_user": 1
}
```

**Status values:** `todo`, `in_progress`, `done`

## Business Rules

- User email must be unique
- A user cannot be deleted if they have assigned tasks (409)
- Task title is required
- Task `id_assigned_user` is required and the user must exist (422 if not)
- Task status must be one of: `todo`, `in_progress`, `done`
- Both users and tasks use soft deletes (`deleted` flag)

## Error Responses

| Status | Meaning                                        |
|--------|------------------------------------------------|
| 404    | Resource not found                             |
| 405    | Method not allowed                             |
| 409    | Conflict (duplicate email, user has tasks)     |
| 422    | Validation error                               |
| 500    | Internal server error                          |

```json
{ "error": "INVALID_EMAIL" }
```

## Project Structure

```
public/index.php          Entry point — DI wiring, routing, dispatch
public/app.html           Frontend (vanilla JS)
public/app.css            Frontend styles
public/app.js             Frontend logic
config/database.php       PDO connection factory
src/Core/                 Framework core (Container, Router, Request, Response)
src/User/                 User domain (Controller, Service, Repository, UserRequest)
src/Task/                 Task domain (Controller, Service, Repository, TaskRequest)
database/schema.sql       MySQL table definitions
tests/                    PHPUnit tests
```

## Makefile Commands

| Command        | Description                                     |
|----------------|-------------------------------------------------|
| `make up`      | Start containers, install Composer dependencies |
| `make down`    | Stop containers                                 |
| `make restart` | Stop + start + install                          |
| `make reset`   | Destroy DB volume and rebuild from schema       |
| `make test`    | Run PHPUnit tests                               |
| `make logs`    | Tail app container logs                         |
| `make shell`   | Open bash inside PHP container                  |
| `make db`      | Open MySQL CLI                                  |
