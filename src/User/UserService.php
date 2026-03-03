<?php

namespace App\User;

use App\Task\TaskRepository;

class UserService
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly TaskRepository $taskRepository,
    ) {
    }

    public function getAll(): array {
        return $this->repository->findAll();
    }

    public function getById(int $id): ?array {
        return $this->repository->findById($id);
    }

    /**
     * @throws \Exception
     */
    public function create(array $data): array {
        if ($this->repository->findByEmail($data['email'])) {
            throw new \Exception('Email already exists', 409);
        }

        return $this->repository->create($data);
    }

    /**
     * @throws \Exception
     */
    public function update(int $id, array $data): bool {
        $user = $this->repository->findById($id);
        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        // check email uniqueness
        if ($user['email'] !== $data['email']) {
            if ($this->repository->findByEmail($data['email'])) {
                throw new \Exception('Email already exists', 409);
            }
        }

        return $this->repository->update($id, $data);
    }

    /**
     * @throws \Exception
     */
    public function delete(int $id): bool {
        $user = $this->repository->findById($id);
        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        if ($this->taskRepository->findByUserId($id)) {
            throw new \Exception('User has assigned tasks', 409);
        }

        return $this->repository->delete($id);
    }
}
