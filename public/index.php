<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\User\UserController;
use App\User\UserRepository;
use App\User\UserRequest;
use App\User\UserService;
use App\Task\TaskController;
use App\Task\TaskRepository;
use App\Task\TaskRequest;
use App\Task\TaskService;

set_exception_handler(function (Throwable $e) {
    if ($e instanceof InvalidArgumentException) {
        (new Response(['error' => $e->getMessage()], 422))->send();
        return;
    }
    (new Response(['error' => 'Internal server error'], 500))->send();
});

// --- Database ---

$pdo = require __DIR__ . '/../config/database.php';

// --- DI Container ---

$container = new Container();

$container->set(UserRepository::class, fn() => new UserRepository($pdo));
$container->set(TaskRepository::class, fn() => new TaskRepository($pdo));

$container->set(UserService::class, fn(Container $c) => new UserService(
    $c->get(UserRepository::class),
    $c->get(TaskRepository::class),
));
$container->set(TaskService::class, fn(Container $c) => new TaskService(
    $c->get(TaskRepository::class),
    $c->get(UserRepository::class),
));

$container->set(UserController::class, fn(Container $c) => new UserController(
    $c->get(UserService::class),
));
$container->set(TaskController::class, fn(Container $c) => new TaskController(
    $c->get(TaskService::class),
));

// --- Routes ---

$router = new Router();

$router->get('/users',         UserController::class, 'index',   UserRequest::class);
$router->post('/users',        UserController::class, 'store',   UserRequest::class);
$router->get('/users/{id}',    UserController::class, 'show',    UserRequest::class);
$router->put('/users/{id}',    UserController::class, 'update',  UserRequest::class);
$router->delete('/users/{id}', UserController::class, 'delete', UserRequest::class);

$router->get('/tasks',         TaskController::class, 'index',   TaskRequest::class);
$router->post('/tasks',        TaskController::class, 'store',   TaskRequest::class);
$router->get('/tasks/{id}',    TaskController::class, 'show',    TaskRequest::class);
$router->put('/tasks/{id}',    TaskController::class, 'update',  TaskRequest::class);
$router->delete('/tasks/{id}', TaskController::class, 'delete', TaskRequest::class);

// --- Dispatch ---

$request = new Request();
$router->dispatch($request, $container);
