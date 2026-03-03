<?php

namespace App\User;

use App\Core\Response;

class UserController
{
    public function __construct(
        private UserService $service
    ) {
    }

    public function index(): Response {
        try {
            return new Response(['data' => $this->service->getAll()]);
        } catch (\Exception $e) {
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function show(UserRequest $request): Response {
        $request->validateShow();
        try {
            $res = $this->service->getById((int) $request->getRouteParam('id'));

            return new Response(['data' => $res]);
        } catch (\Exception $e) {
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * @throws \Exception
     */
    public function store(UserRequest $request): Response {
        $request->validateStore();

        try {
            $newUser = $this->service->create($request->getBody());

            return new Response(['message' => "User {$newUser['name']} created"]);
        } catch (\Exception $e) {
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function update(UserRequest $request): Response {
        $request->validateUpdate();

        try {
            $data = $request->getBody();
            $id = (int) $request->getRouteParam('id');

            if (!$this->service->update($id, $data)) {
                return new Response(['message' => "User {$id} not updated. Try again later."]);
            }
        } catch (\Exception $e) {
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }

        return new Response(['message' => "User {$id} updated"]);
    }

    public function delete(UserRequest $request): Response {
        $request->validateDelete();

        try {
            $id = (int) $request->getRouteParam('id');

            if (!$this->service->delete($id)) {
                return new Response(['message' => "User {$id} not deleted. Try again later."]);
            }
        } catch (\Exception $e) {
            return new Response(['message' => $e->getMessage()], $e->getCode());
        }

        return new Response(['message' => "User {$id} deleted"]);
    }
}
