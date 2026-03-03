<?php

namespace App\Task;

use App\User\UserRepository;

class TaskService
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function getAll(?string $status = null): array {
        return $this->taskRepository->findAll($status);
    }

    public function getById(int $id): ?array {
        return $this->taskRepository->findById($id);
    }

    /**
     * @throws \Exception
     */
    public function create(array $data): array {
        if (!$this->userRepository->findById((int) $data['id_assigned_user'])) {
            throw new \Exception('Assigned user not found', 422);
        }

        return $this->taskRepository->create($data);
    }

    /**
     * @throws \Exception
     */
    public function update(int $id, array $data): bool {
        $task = $this->taskRepository->findById($id);
        if (!$task) {
            throw new \Exception('Task not found', 404);
        }

        if (!$this->userRepository->findById((int) $data['id_assigned_user'])) {
            throw new \Exception('Assigned user not found', 422);
        }

        return $this->taskRepository->update($id, $data);
    }

    /**
     * @throws \Exception
     */
    public function delete(int $id): bool {
        $task = $this->taskRepository->findById($id);
        if (!$task) {
            throw new \Exception('Task not found', 404);
        }

        return $this->taskRepository->delete($id);
    }
}
