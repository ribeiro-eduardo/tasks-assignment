<?php

namespace App\User;

use PDO;

class UserRepository
{
    public function __construct(private PDO $pdo) {
    }

    public function findAll(): ?array {
        $sql = 'SELECT id, name, email, created_at
                FROM users
                WHERE deleted = "0"';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll() ?: null;
    }

    public function findById(int $id): ?array {
        $sql = 'SELECT id, name, email, created_at
                FROM users
                WHERE id = :id AND deleted = "0"';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array {
        $sql = 'SELECT 1 
                FROM users
                WHERE email = :email AND deleted = "0"';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;

    }

    public function create(array $data): array {
        $sql = 'INSERT INTO users (name, email) 
                VALUES (:name, :email)';
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id);
    }

    public function update(int $id, array $data): bool {
        $sql = 'UPDATE users
                SET name = :name, email = :email
                WHERE id = :id AND deleted = "0"';

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'name'  => $data['name'],
            'email' => $data['email'],
            'id'    => $id
        ]);
    }

    public function delete(int $id): bool {
        $sql = 'UPDATE users
                SET deleted = "1"
                WHERE id = :id AND deleted = "0"';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
