<?php

namespace App\Task;

use App\Core\Response;

class TaskController
{
    public function __construct(private TaskService $service) {
    }

    public function index(TaskRequest $request): Response {
        try {
            $status = $request->getQuery('status');
            return new Response(['data' => $this->service->getAll($status)]);
        } catch (\Exception $e) {
            dd($e);
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function show(TaskRequest $request): Response {
        $request->validateShow();
        try {
            $res = $this->service->getById((int) $request->getRouteParam('id'));

            return new Response(['data' => $res]);
        } catch (\Exception $e) {
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function store(TaskRequest $request): Response {
        $request->validateStore();

        try {
            $newTask = $this->service->create($request->getBody());

            return new Response(['message' => "Task {$newTask['title']} created"]);
        } catch (\Exception $e) {
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function update(TaskRequest $request): Response {
        $request->validateUpdate();

        try {
            $data = $request->getBody();
            $id = (int) $request->getRouteParam('id');

            if (!$this->service->update($id, $data)) {
                return new Response(['message' => "Task {$id} not updated. Try again later."]);
            }
        } catch (\Exception $e) {
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }

        return new Response(['message' => "Task {$id} updated"]);
    }

    public function delete(TaskRequest $request): Response {
        $request->validateDelete();

        try {
            $id = (int) $request->getRouteParam('id');

            if (!$this->service->delete($id)) {
                return new Response(['message' => "Task {$id} not deleted. Try again later."]);
            }
        } catch (\Exception $e) {
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }

        return new Response(['message' => "Task {$id} deleted"]);
    }
}
