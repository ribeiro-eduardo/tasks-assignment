<?php

namespace App\Task;

use PDO;

class TaskRepository
{
    public function __construct(private readonly PDO $pdo) {
    }

    public function findAll(?string $status = null): ?array {
        $sql = 'SELECT id, title, description, status, id_assigned_user, created_at
                FROM tasks
                WHERE deleted = "0"';

        $params = [];

        if ($status) {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll() ?: null;
    }

    public function findById(int $id): ?array {
        $sql = 'SELECT id, title, description, status, id_assigned_user, created_at
                FROM tasks
                WHERE id = :id AND deleted = "0"';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByUserId(int $userId): ?array {
        $sql = 'SELECT 1
                FROM tasks
                WHERE id_assigned_user = :userId AND deleted = "0"';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): array {
        $sql = 'INSERT INTO tasks (title, description, status, id_assigned_user)
                VALUES (:title, :description, :status, :id_assigned_user)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'status'           => $data['status'] ?? 'todo',
            'id_assigned_user' => $data['id_assigned_user'],
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id);
    }

    public function update(int $id, array $data): bool {
        $sql = 'UPDATE tasks
                SET title = :title, description = :description, status = :status, id_assigned_user = :id_assigned_user
                WHERE id = :id AND deleted = "0"';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'status'           => $data['status'],
            'id_assigned_user' => $data['id_assigned_user'],
            'id'               => $id,
        ]);
    }

    public function delete(int $id): bool {
        $sql = 'UPDATE tasks
                SET deleted = "1"
                WHERE id = :id AND deleted = "0"';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
